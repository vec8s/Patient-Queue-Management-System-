<?php
/**
 * Legacy Configuration Bridge
 * Loads settings from PQS\Utils\Config
 */
require_once __DIR__ . '/../vendor/autoload.php';

use PQS\Utils\Config;

Config::load(__DIR__ . '/..');

if (!defined('APP_NAME')) {
    define('APP_NAME', (string) Config::get('APP_NAME', 'Patient Queue Management System'));
    define('APP_SHORT_NAME', (string) Config::get('APP_SHORT_NAME', 'PQS'));
    define('APP_VERSION', (string) Config::get('APP_VERSION', '3.0.0'));

    define('DB_HOST', (string) Config::get('DB_HOST', 'localhost'));
    define('DB_USER', (string) Config::get('DB_USER', 'root'));
    define('DB_PASS', (string) Config::get('DB_PASS', ''));
    define('DB_NAME', (string) Config::get('DB_NAME', 'db_pqs'));

    define('JWT_SECRET', (string) Config::get('JWT_SECRET', 'pqs_app_default_secure_secret_key'));
    define('JWT_ALGORITHM', (string) Config::get('JWT_ALGORITHM', 'HS256'));
    define('JWT_EXPIRY', (int) Config::get('JWT_EXPIRY', 86400));
}
