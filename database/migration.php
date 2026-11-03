<?php
/**
 * Database Migration & Schema Seeder
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PQS\Database\Database;
use PQS\Utils\Config;

// Initialize Configuration
Config::load(__DIR__ . '/..');

try {
    $db = Database::getInstance()->getConnection();

    // 1. Run Schema DDL
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Schema file not found at: {$schemaFile}");
    }
    $schemaSql = file_get_contents($schemaFile);
    if ($db->multi_query($schemaSql)) {
        do {
            if ($result = $db->store_result()) {
                $result->free();
            }
        } while ($db->more_results() && $db->next_result());
    }

    // 2. Run Seed DML
    $seedFile = __DIR__ . '/seed.sql';
    if (file_exists($seedFile)) {
        $seedSql = file_get_contents($seedFile);
        if ($db->multi_query($seedSql)) {
            do {
                if ($result = $db->store_result()) {
                    $result->free();
                }
            } while ($db->more_results() && $db->next_result());
        }
    }

    $message = "تمت تهيئة وتحديث قاعدة البيانات بنجاح لنظام " . Config::get('APP_NAME', 'PQS') . "!";

    if (php_sapi_name() === 'cli') {
        echo $message . PHP_EOL;
    } else {
        echo "<!DOCTYPE html><html dir='rtl' lang='ar'><head><meta charset='utf-8'><title>تهيئة النظام</title></head>";
        echo "<body style='font-family: system-ui, sans-serif; text-align: center; padding: 50px; background: #f8f9fa;'>";
        echo "<div style='background: white; padding: 30px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>";
        echo "<h2 style='color: #198754; margin-bottom: 20px;'>" . htmlspecialchars($message) . "</h2>";
        echo "<p><a href='../index.php' style='padding: 10px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px;'>الانتقال للصفحة الرئيسية</a></p>";
        echo "</div></body></html>";
    }
} catch (Throwable $e) {
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, "Migration Error: " . $e->getMessage() . PHP_EOL);
    } else {
        http_response_code(500);
        echo "<h2 style='color: red;'>فشل التهيئة: " . htmlspecialchars($e->getMessage()) . "</h2>";
    }
    exit(1);
}

