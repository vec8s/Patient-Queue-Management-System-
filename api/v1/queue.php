<?php
/**
 * Queue State & Ticket Processing API
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PQS\Auth\AuthGuard;
use PQS\Services\QueueService;
use PQS\Utils\Config;
use PQS\Utils\Response;

Config::load(__DIR__ . '/../..');
$queueService = new QueueService();
$method = $_SERVER['REQUEST_METHOD'];

// Fetch Live Queue Data
if ($method === 'GET') {
    $data = $queueService->getLiveQueue();
    // Return compatible payload with old frontend format
    Response::success([
        'queue'       => $data,
        'appointment' => $data // legacy frontend compatibility
    ]);
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true) ?? [];
    $input = array_merge($_POST, $json);

    // 1. Doctor calling a patient to room
    if (isset($input['queueId']) && isset($input['doctorId'])) {
        // Enforce doctor/staff authentication
        AuthGuard::enforceApi(['doctor', 'nurse', 'admin']);

        $queueId  = (int) $input['queueId'];
        $doctorId = (int) $input['doctorId'];
        $roomId   = (int) ($input['roomNumber'] ?? 1);

        $success = $queueService->callPatient($queueId, $doctorId, $roomId);
        if ($success) {
            Response::success(null, 'تم استدعاء المريض بنجاح');
        } else {
            Response::error('تعذر تحديث حالة تذكرة المريض في الطابور');
        }
    }

    // 2. Complete consultation
    if (isset($input['completeQueueId'])) {
        AuthGuard::enforceApi(['doctor', 'nurse', 'admin']);
        $queueId = (int) $input['completeQueueId'];
        $queueService->completeConsultation($queueId);
        Response::success(null, 'تم إنهاء الاستشارة بنجاح');
    }

    // 3. Auto-complete stale
    if (isset($input['auto'])) {
        $updated = $queueService->autoCompleteStale();
        Response::success(['updated_records' => $updated], 'تم تحديث الحالات المعلقة تلقائياً');
    }

    // 4. Check-in with pre-scheduled appointment ID
    $appointmentId = trim($input['appointmentId'] ?? '');
    $type = trim($input['type'] ?? '');

    if (!empty($appointmentId) && $type !== 'walk-in') {
        try {
            $result = $queueService->checkinAppointment($appointmentId);
            Response::success([
                'quenumber'      => $result['queue_number'],
                'queue_number'   => $result['queue_number'],
                'already_queued' => $result['already_queued'] ?? false
            ], 'تم تأكيد حضور الموعد بنجاح');
        } catch (\Throwable $e) {
            Response::fail($e->getMessage(), null, 400);
        }
    }

    // 5. Walk-in Check-in
    if ($type === 'walk-in' || isset($input['patientId'])) {
        $patientId = (int) ($input['patientId'] ?? 0);
        $appTypeId = (int) ($input['appointmentType'] ?? 1);
        $reason    = trim($input['additionalinfo'] ?? 'استشارة حضور مباشر');
        $consent   = !empty($input['termsAgreement']) ? 1 : 1;

        if ($patientId <= 0) {
            Response::fail('رقم ملف المريض غير صالح أو غير موجود.');
        }

        try {
            $result = $queueService->checkinWalkIn($patientId, $appTypeId, $reason, $consent);
            Response::success([
                'quenumber'    => $result['queue_number'],
                'queue_number' => $result['queue_number'],
                'reference'    => $result['reference']
            ], 'تم تسجيل الحضور الفوري واستخراج التذكرة بنجاح');
        } catch (\Throwable $e) {
            Response::fail($e->getMessage(), null, 400);
        }
    }

    Response::fail('معاملات تسجيل الحضور غير صحيحة.', null, 400);
}

Response::fail('طريقة الطلب غير مدعومة.', null, 405);

