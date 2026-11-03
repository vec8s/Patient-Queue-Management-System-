<?php
/**
 * Appointment Scheduling & Lookup Service
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

namespace PQS\Services;

use PQS\Database\Database;
use InvalidArgumentException;
use RuntimeException;

class AppointmentService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Book a new scheduled appointment
     */
    public function bookAppointment(array $data): array
    {
        $patientId = (int) ($data['patient_id'] ?? 0);
        $doctorId  = (int) ($data['preferredDoctor'] ?? 0);
        $typeId    = (int) ($data['appointmentType'] ?? 1);
        $date      = trim($data['appointmentDate'] ?? '');
        $time      = trim($data['appointmentTime'] ?? '');
        $reason    = trim($data['reason'] ?? 'استشارة طبية');
        $consent   = !empty($data['termsAgreement']) ? 1 : 0;

        if ($patientId <= 0 || $doctorId <= 0 || empty($date) || empty($time)) {
            throw new InvalidArgumentException("يرجى ملء جميع بيانات الموعد المطلوبة.");
        }

        // Generate unique reference number: APT<YYYYMMDD><RAND>
        $refNumber = 'APT' . date('Ymd', strtotime($date)) . rand(100, 999);

        $sql = "INSERT INTO checkins (patient_id, doctor_id, app_type_id, appointment_date, scheduled_time, reason, consent, reference_number, type_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'pending')";

        $insertId = $this->db->insert($sql, [$patientId, $doctorId, $typeId, $date, $time, $reason, $consent, $refNumber]);

        return [
            'id'               => $insertId,
            'reference_number' => $refNumber,
            'appointment_date' => $date,
            'scheduled_time'   => $time
        ];
    }

    /**
     * Lookup appointment details by reference code
     */
    public function getAppointmentByReference(string $referenceNumber): ?array
    {
        $sql = "SELECT c.checkin_id, c.reference_number, c.appointment_date, c.scheduled_time, c.reason, c.status,
                       p.first_name, p.last_name, p.phone,
                       s.first_name AS doc_fname, s.last_name AS doc_lname,
                       t.name AS appointment_name
                FROM checkins c
                INNER JOIN patients p ON p.id = c.patient_id
                LEFT JOIN staff s ON s.id = c.doctor_id
                LEFT JOIN appointment_types t ON t.id = c.app_type_id
                WHERE c.reference_number = ? LIMIT 1";

        $row = $this->db->fetchOne($sql, [$referenceNumber]);
        if (!$row) {
            return null;
        }

        $doctorName = trim(($row['doc_fname'] ?? '') . ' ' . ($row['doc_lname'] ?? ''));

        return [
            'id'               => (int) $row['checkin_id'],
            'reference_number' => $row['reference_number'],
            'patient_name'     => $row['first_name'] . ' ' . $row['last_name'],
            'phone'            => $row['phone'],
            'doctor'           => !empty($doctorName) ? 'د. ' . $doctorName : 'العيادة العامة',
            'appointment_type' => $row['appointment_name'] ?? 'استشارة',
            'scheduled_time'   => $row['scheduled_time'] ?? '',
            'appointment_date' => $row['appointment_date'],
            'reason'           => $row['reason'] ?? '',
            'status'           => $row['status']
        ];
    }
}

