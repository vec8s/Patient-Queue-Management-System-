<?php
/**
 * Server-Side Authentication & Role-Based Access Guard (RBAC)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

namespace PQS\Auth;

use PQS\Database\Database;
use PQS\Utils\Response;

class AuthGuard
{
    private static ?array $authenticatedUser = null;

    /**
     * Enforce authentication for API requests
     */
    public static function enforceApi(array $allowedRoles = []): array
    {
        $token = JWTService::extractToken();
        if (!$token) {
            Response::unauthorized('يرجى تسجيل الدخول للوصول إلى هذه الخدمة.');
        }

        $payload = JWTService::verifyToken($token);
        if (!$payload || empty($payload->user_id)) {
            Response::unauthorized('رمز الجلسة منتهي أو غير صالح.');
        }

        $user = self::loadUser((int) $payload->user_id);
        if (!$user || !$user['is_active']) {
            Response::unauthorized('حساب المستخدم غير نشط أو غير موجود.');
        }

        if (!empty($allowedRoles) && !in_array($user['role'], $allowedRoles, true)) {
            Response::forbidden('ليس لديك الصلاحية الكافية لإجراء هذه العملية.');
        }

        self::$authenticatedUser = $user;
        return $user;
    }

    /**
     * Enforce authentication for Web Page Views (Server-Side Redirect)
     */
    public static function enforcePage(array $allowedRoles = []): array
    {
        // Try getting token from Cookie or Session
        $token = JWTService::extractToken();
        $user = null;

        if ($token) {
            $payload = JWTService::verifyToken($token);
            if ($payload && !empty($payload->user_id)) {
                $user = self::loadUser((int) $payload->user_id);
            }
        }

        // Fallback to PHP session if token not in headers/cookie
        if (!$user && session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['staff_id'])) {
            $user = self::loadUser((int) $_SESSION['staff_id']);
        }

        if (!$user || !$user['is_active']) {
            header('Location: login.php?msg=unauthorized');
            exit;
        }

        if (!empty($allowedRoles) && !in_array($user['role'], $allowedRoles, true)) {
            http_response_code(403);
            echo "<!DOCTYPE html><html dir='rtl' lang='ar'><body style='font-family: Cairo, sans-serif; text-align: center; padding: 60px;'>";
            echo "<h2 style='color: #dc3545;'>عذراً، لا تملك الصلاحية للوصول إلى هذه الصفحة!</h2>";
            echo "<p><a href='dashboard.php' style='padding: 8px 16px; background: #0d6efd; color: white; text-decoration: none; border-radius: 4px;'>العودة للوحة المتابعة</a></p>";
            echo "</body></html>";
            exit;
        }

        self::$authenticatedUser = $user;
        return $user;
    }

    /**
     * Get currently authenticated user if any
     */
    public static function user(): ?array
    {
        return self::$authenticatedUser;
    }

    private static function loadUser(int $userId): ?array
    {
        $db = Database::getInstance();
        return $db->fetchOne("SELECT id, first_name, last_name, email, role, is_active FROM staff WHERE id = ? LIMIT 1", [$userId]);
    }
}

