<?php
/**
 * Safe Database Singleton & Prepared Query Executor
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

namespace PQS\Database;

use mysqli;
use mysqli_stmt;
use RuntimeException;
use PQS\Utils\Config;

class Database
{
    private static ?Database $instance = null;
    private mysqli $connection;

    private function __construct()
    {
        $host = (string) Config::get('DB_HOST', 'localhost');
        $port = (int) Config::get('DB_PORT', 3306);
        $user = (string) Config::get('DB_USER', 'root');
        $pass = (string) Config::get('DB_PASS', '');
        $name = (string) Config::get('DB_NAME', 'db_pqs');

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new mysqli($host, $user, $pass, '', $port);
            $this->connection->query("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->connection->select_db($name);
            $this->connection->set_charset("utf8mb4");
        } catch (\Throwable $e) {
            error_log("Database connection failure: " . $e->getMessage());
            throw new RuntimeException("فشل الاتصال بقاعدة البيانات. يرجى التحقق من تشغيل MySQL في XAMPP.");
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    /**
     * Execute a prepared SQL query safely
     */
    public function execute(string $sql, array $params = []): mysqli_stmt
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException("SQL prepare failed: " . $this->connection->error);
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Execute query and fetch all results as an associative array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->get_result();
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Execute query and fetch single row
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->get_result();
        $row = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
        $stmt->close();
        return $row;
    }

    /**
     * Execute INSERT query and return auto-increment ID
     */
    public function insert(string $sql, array $params = []): int
    {
        $stmt = $this->execute($sql, $params);
        $id = (int) $this->connection->insert_id;
        $stmt->close();
        return $id;
    }

    /**
     * Transaction Management
     */
    public function beginTransaction(): bool
    {
        return $this->connection->begin_transaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollback();
    }
}

