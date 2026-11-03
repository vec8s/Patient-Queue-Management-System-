<?php
/**
 * Legacy Database Connection Bridge
 * Uses PQS\Database\Database
 */
require_once __DIR__ . '/config.php';

use PQS\Database\Database;

try {
    $db = Database::getInstance()->getConnection();
} catch (Throwable $e) {
    if (php_sapi_name() !== 'cli' && !headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
    echo json_encode(['error' => 'فشل الاتصال بقاعدة بيانات MySQL: ' . $e->getMessage()]);
    exit;
}

/**
 * Legacy query execution helper for backwards compatibility
 */
function executeQuery($query, $params = [], $returnInsertId = false) {
    global $db;
    try {
        $stmt = $db->prepare($query);
        if (!$stmt) {
            return false;
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

        if ($returnInsertId) {
            $insertId = $db->insert_id;
            $stmt->close();
            return $insertId;
        }

        return $stmt;
    } catch (Throwable $e) {
        error_log("Query execution error: " . $e->getMessage());
        return false;
    }
}
