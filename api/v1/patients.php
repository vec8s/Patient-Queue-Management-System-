<?php
/**
 * Patient Management & Search API
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PQS\Services\PatientService;
use PQS\Utils\Config;
use PQS\Utils\Response;

Config::load(__DIR__ . '/../..');
$patientService = new PatientService();
$method = $_SERVER['REQUEST_METHOD'];

// Search Patients
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $dob    = trim($_GET['dob'] ?? '');
    $limit  = !empty($_GET['limit']) ? (int) $_GET['limit'] : 25;

    $patients = $patientService->searchPatients($search, $dob, $limit);
    Response::success(['patients' => $patients], 'تم جلب سجلات المرضى بنجاح');
}

// Register Patient
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? $_POST;

    try {
        $patientId = $patientService->registerPatient($input);
        Response::success([
            'patient_id' => $patientId,
            'patient'    => $patientId // backwards compatibility
        ], 'تم تسجيل ملف المريض بنجاح', 201);
    } catch (\Throwable $e) {
        Response::fail($e->getMessage(), null, 400);
    }
}

Response::fail('طريقة الطلب غير مدعومة.', null, 405);

