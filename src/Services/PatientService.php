<?php
/**
 * Patient Record & Search Management Service
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

namespace PQS\Services;

use PQS\Database\Database;
use InvalidArgumentException;
use RuntimeException;

class PatientService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Register a new patient safely
     */
    public function registerPatient(array $data): int
    {
        $firstName = trim($data['firstName'] ?? '');
        $lastName  = trim($data['lastName'] ?? '');
        $dob       = trim($data['dob'] ?? '');
        $gender    = trim($data['gender'] ?? 'male');
        $phone     = trim($data['phone'] ?? '');
        $address   = trim($data['address'] ?? '');
        $consent   = !empty($data['consent']) ? 1 : 0;

        if (empty($firstName) || empty($lastName) || empty($dob) || empty($phone)) {
            throw new InvalidArgumentException("جميع الحقول المطلوبة يجب تعبئتها بدقة.");
        }

        $sql = "INSERT INTO patients (first_name, last_name, date_of_birth, gender, phone, address, consent, is_registered) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";

        return $this->db->insert($sql, [$firstName, $lastName, $dob, $gender, $phone, $address, $consent]);
    }

    /**
     * Search patients by name, phone, or DOB
     */
    public function searchPatients(string $search = '', string $dob = '', int $limit = 25): array
    {
        $sql = "SELECT id, first_name, last_name, date_of_birth, phone, address FROM patients WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (CONCAT(first_name, ' ', last_name) LIKE ? OR phone LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($dob)) {
            $sql .= " AND date_of_birth = ?";
            $params[] = $dob;
        }

        $sql .= " ORDER BY id DESC LIMIT " . intval($limit);

        $rows = $this->db->fetchAll($sql, $params);

        return array_map(function ($row) {
            return [
                'id'         => (int) $row['id'],
                'first_name' => htmlspecialchars($row['first_name']),
                'last_name'  => htmlspecialchars($row['last_name']),
                'dob'        => $row['date_of_birth'],
                'phone'      => htmlspecialchars($row['phone']),
                'address'    => htmlspecialchars($row['address'] ?? '')
            ];
        }, $rows);
    }

    /**
     * Find patient by ID
     */
    public function getPatientById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM patients WHERE id = ? LIMIT 1", [$id]);
    }
}

