<?php
/**
 * Unified Authentication & Staff Management API
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PQS\Auth\AuthGuard;
use PQS\Auth\JWTService;
use PQS\Database\Database;
use PQS\Utils\Config;
use PQS\Utils\Response;

Config::load(__DIR__ . '/../..');
$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

// Parse JSON body if present
$raw = file_get_contents('php://input');
$json = json_decode($raw, true) ?? [];

// 1. Verify current token
if ($method === 'GET') {
    $user = AuthGuard::enforceApi();
    Response::success(['user' => $user], 'الجلسة نشطة ومصرح بها');
}

if ($method === 'POST') {
    $action = $_GET['action'] ?? $json['action'] ?? $_POST['action'] ?? 'login';

    // Staff Login
    if ($action === 'login') {
        $email = trim(strtolower($_POST['email'] ?? $json['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? $json['password'] ?? '');

        if (empty($email) || empty($password)) {
            Response::fail('يرجى إدخال البريد الإلكتروني وكلمة المرور.');
        }

        $staff = $db->fetchOne("SELECT id, first_name, last_name, email, password, role, is_active FROM staff WHERE email = ? LIMIT 1", [$email]);

        if (!$staff || !password_verify($password, $staff['password'])) {
            Response::fail('البريد الإلكتروني أو كلمة المرور غير صحيحة.', null, 401);
        }

        if (!$staff['is_active']) {
            Response::forbidden('هذا الحساب معطل. يرجى التواصل مع إدارة المركز.');
        }

        $token = JWTService::generateToken((int) $staff['id'], [
            'role'  => $staff['role'],
            'email' => $staff['email'],
            'name'  => $staff['first_name'] . ' ' . $staff['last_name']
        ]);

        // Set HttpOnly Cookie for security
        setcookie('pqs_token', $token, [
            'expires'  => time() + 86400,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        unset($staff['password']);

        Response::success([
            'token' => $token,
            'staff' => $staff,
            'data'  => $staff // backwards compatibility
        ], 'تم تسجيل الدخول بنجاح');
    }

    // Staff Registration (RESTRICTED ONLY TO ADMIN)
    if ($action === 'register') {
        // Enforce Admin Access
        AuthGuard::enforceApi(['admin']);

        $firstName = trim($json['firstName'] ?? $_POST['firstName'] ?? '');
        $lastName  = trim($json['lastName'] ?? $_POST['lastName'] ?? '');
        $email     = trim(strtolower($json['email'] ?? $_POST['email'] ?? ''));
        $password  = (string) ($json['password'] ?? $_POST['password'] ?? '');
        $role      = trim($json['role'] ?? $_POST['role'] ?? 'doctor');

        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            Response::fail('يرجى ملء جميع الحقول المطلوبة لإنشاء الموظف.');
        }

        // Check if email already registered
        $existing = $db->fetchOne("SELECT id FROM staff WHERE email = ? LIMIT 1", [$email]);
        if ($existing) {
            Response::fail('هذا البريد الإلكتروني مسجل مسبقاً في النظام.', null, 409);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insertId = $db->insert(
            "INSERT INTO staff (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)",
            [$firstName, $lastName, $email, $hash, $role]
        );

        Response::success(['staff_id' => $insertId], 'تم إنشاء حساب الموظف بنجاح من قبل الإدارة.', 201);
    }

    // Logout
    if ($action === 'logout') {
        setcookie('pqs_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        Response::success(null, 'تم تسجيل الخروج بنجاح');
    }
}

Response::fail('طريقة الطلب غير مدعومة.', null, 405);

