<?php
/**
 * Concurrency-Safe Queue & Ticket Management Service
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

namespace PQS\Services;

use DateTime;
use PQS\Database\Database;
use RuntimeException;
use Throwable;

class QueueService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Atomically generate next sequential ticket number for the day
     */
    public function generateTicketNumber(string $prefix = 'WALK'): string
    {
        $this->db->beginTransaction();

        try {
            // Lock sequence record for update to prevent concurrent duplicate tickets
            $today = date('Y-m-d');
            $row = $this->db->fetchOne(
                "SELECT last_number FROM daily_ticket_sequences WHERE sequence_date = ? AND prefix = ? FOR UPDATE",
                [$today, $prefix]
            );

            $nextNum = ($row !== null) ? ((int) $row['last_number']) + 1 : 1;

            $this->db->execute(
                "INSERT INTO daily_ticket_sequences (sequence_date, prefix, last_number) 
                 VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE last_number = ?",
                [$today, $prefix, $nextNum, $nextNum]
            );

            $this->db->commit();
            return sprintf('%s-%03d', $prefix, $nextNum);
        } catch (Throwable $e) {
            $this->db->rollback();
            // Fallback to table count if sequences table is unavailable
            $countRow = $this->db->fetchOne("SELECT COUNT(*) AS total FROM queue WHERE DATE(created_at) = CURDATE()");
            $num = ($countRow ? (int)$countRow['total'] : 0) + 1;
            return sprintf('%s-%03d', $prefix, $num);
        }
    }

    /**
     * Process check-in for a Walk-In patient
     */
    public function checkinWalkIn(int $patientId, int $appTypeId = 1, string $reason = 'استشارة حضور مباشر', int $consent = 1): array
    {
        $this->db->beginTransaction();

        try {
            // Generate unique reference
            $refNumber = 'WLK' . date('YmdHis') . rand(10, 99);
            $checkinId = $this->db->insert(
                "INSERT INTO checkins (patient_id, type_id, app_type_id, reason, consent, reference_number, status, actual_checkin_time) 
                 VALUES (?, 2, ?, ?, ?, ?, 'checked_in', NOW())",
                [$patientId, $appTypeId, $reason, $consent, $refNumber]
            );

            $queueNumber = $this->generateTicketNumber('WALK');

            $queueId = $this->db->insert(
                "INSERT INTO queue (checkin_id, queue_number, status) VALUES (?, ?, 'waiting')",
                [$checkinId, $queueNumber]
            );

            $this->db->commit();

            return [
                'checkin_id'   => $checkinId,
                'queue_id'     => $queueId,
                'queue_number' => $queueNumber,
                'reference'    => $refNumber
            ];
        } catch (Throwable $e) {
            $this->db->rollback();
            throw new RuntimeException("فشل إتمام تسجيل الحضور: " . $e->getMessage());
        }
    }

    /**
     * Process check-in for an existing pre-scheduled appointment
     */
    public function checkinAppointment(string $referenceNumber): array
    {
        $checkin = $this->db->fetchOne(
            "SELECT checkin_id, status FROM checkins WHERE reference_number = ? LIMIT 1",
            [$referenceNumber]
        );

        if (!$checkin) {
            throw new RuntimeException("لم يتم العثور على حجز بهذا الكود المرجعي.");
        }

        $checkinId = (int) $checkin['checkin_id'];

        // Check if already in queue today
        $existing = $this->db->fetchOne(
            "SELECT queue_number FROM queue WHERE checkin_id = ? AND status IN ('waiting', 'called', 'in_progress')",
            [$checkinId]
        );

        if ($existing) {
            return [
                'queue_number' => $existing['queue_number'],
                'already_queued' => true,
                'message' => 'تم استخراج التذكرة مسبقاً.'
            ];
        }

        $this->db->beginTransaction();
        try {
            $queueNumber = $this->generateTicketNumber('APPT');

            $queueId = $this->db->insert(
                "INSERT INTO queue (checkin_id, queue_number, status) VALUES (?, ?, 'waiting')",
                [$checkinId, $queueNumber]
            );

            $this->db->execute(
                "UPDATE checkins SET status = 'checked_in', actual_checkin_time = NOW() WHERE checkin_id = ?",
                [$checkinId]
            );

            $this->db->commit();

            return [
                'checkin_id'   => $checkinId,
                'queue_id'     => $queueId,
                'queue_number' => $queueNumber,
                'already_queued' => false
            ];
        } catch (Throwable $e) {
            $this->db->rollback();
            throw new RuntimeException("فشل إدراج الموعد في الطابور: " . $e->getMessage());
        }
    }

    /**
     * Call next patient to a doctor's examination room
     */
    public function callPatient(int $queueId, int $doctorId, int $roomId): bool
    {
        $stmt = $this->db->execute(
            "UPDATE queue SET called_by = ?, room_id = ?, status = 'in_progress', called_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$doctorId, $roomId, $queueId]
        );
        $success = ($stmt->affected_rows > 0);
        $stmt->close();
        return $success;
    }

    /**
     * Mark patient consultation as completed
     */
    public function completeConsultation(int $queueId): bool
    {
        $stmt = $this->db->execute(
            "UPDATE queue SET status = 'completed', updated_at = NOW() WHERE id = ?",
            [$queueId]
        );
        $success = ($stmt->affected_rows > 0);
        $stmt->close();
        return $success;
    }

    /**
     * Get live queue dashboard for displays and doctors
     */
    public function getLiveQueue(): array
    {
        $query = "SELECT q.id AS queue_id, q.queue_number, q.priority, q.status, q.created_at, q.called_at, q.updated_at,
                         p.first_name, p.last_name,
                         t.name AS appointment_name,
                         r.name AS room_name,
                         s.first_name AS doc_fname, s.last_name AS doc_lname
                  FROM queue q
                  INNER JOIN checkins c ON c.checkin_id = q.checkin_id
                  INNER JOIN patients p ON p.id = c.patient_id
                  LEFT JOIN appointment_types t ON t.id = c.app_type_id
                  LEFT JOIN rooms r ON r.id = q.room_id
                  LEFT JOIN staff s ON s.id = q.called_by
                  WHERE DATE(q.created_at) = CURDATE()
                  ORDER BY q.id ASC";

        $rows = $this->db->fetchAll($query);

        $result = [
            'waiting'    => [],
            'inProgress' => [],
            'completed'  => []
        ];

        foreach ($rows as $row) {
            $docName = trim(($row['doc_fname'] ?? '') . ' ' . ($row['doc_lname'] ?? ''));
            $item = [
                'queue_id'     => (int) $row['queue_id'],
                'queue_number' => $row['queue_number'],
                'patient_name' => $row['first_name'] . ' ' . $row['last_name'],
                'reason'       => $row['appointment_name'] ?? 'استشارة',
                'doctor'       => !empty($docName) ? 'د. ' . $docName : 'العيادة العامة',
                'room'         => $row['room_name'] ?? 'غرفة 1',
                'time'         => date('h:i A', strtotime($row['created_at'])),
                'id'           => $row['queue_number'],
                'name'         => $row['first_name'] . ' ' . $row['last_name']
            ];

            if ($row['status'] === 'waiting') {
                $result['waiting'][] = $item;
            } elseif ($row['status'] === 'in_progress') {
                $result['inProgress'][] = $item;
            } elseif ($row['status'] === 'completed') {
                $result['completed'][] = $item;
            }
        }

        return $result;
    }

    /**
     * Auto-complete consultations running over 30 minutes
     */
    public function autoCompleteStale(): int
    {
        $rows = $this->db->fetchAll("SELECT id, called_at FROM queue WHERE status = 'in_progress' AND called_at IS NOT NULL");
        $now = new DateTime();
        $updated = 0;

        foreach ($rows as $r) {
            $called = new DateTime($r['called_at']);
            $diffMinutes = ($now->getTimestamp() - $called->getTimestamp()) / 60;
            if ($diffMinutes > 30) {
                $this->completeConsultation((int) $r['id']);
                $updated++;
            }
        }

        return $updated;
    }
}
