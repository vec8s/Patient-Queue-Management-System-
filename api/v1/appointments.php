<?php
/**
 * Patient Appointment API
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PQS\Services\AppointmentService;
use PQS\Utils\Config;
use PQS\Utils\Response;

Config::load(__DIR__ . '/../..');
$service = new AppointmentService();
$method = $_SERVER['REQUEST_METHOD'];

// Lookup appointment
if ($method === 'GET') {
    $ref = trim($_GET['appointmentID'] ?? $_GET['appointmentId'] ?? '');
    if (empty($ref)) {
        Response::fail('كود الموعد المرجعي مطلوب.');
    }

    $appointment = $service->getAppointmentByReference($ref);
    if (!$appointment) {
        Response::fail('لم يتم العثور على حجز بهذا الكود المرجعي.', null, 404);
    }

    // Return in compatible shape
    Response::success([
        'appointment' => $appointment,
        'records'     => [$appointment] // backwards compatibility
    ]);
}

// Book appointment
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? $_POST;

    try {
        $result = $service->bookAppointment($input);
        Response::success([
            'appointment'   => $result,
            'appointmentID' => $result['reference_number']
        ], 'تم حجز الموعد بنجاح', 201);
    } catch (\Throwable $e) {
        Response::fail($e->getMessage(), null, 400);
    }
}

Response::fail('طريقة الطلب غير مدعومة.', null, 405);

