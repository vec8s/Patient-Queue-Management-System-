<?php
/**
 * Standardized API Response Helper (JSend Envelope)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

namespace PQS\Utils;

class Response
{
    /**
     * Send JSON Response and terminate execution
     */
    public static function json(string $status, mixed $data = null, string $message = '', int $code = 200): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
        }

        echo json_encode([
            'status'    => $status, // 'success' | 'fail' | 'error'
            'success'   => ($status === 'success'), // backwards-compatible flag
            'message'   => $message,
            'data'      => $data,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function success(mixed $data = null, string $message = '', int $code = 200): void
    {
        self::json('success', $data, $message, $code);
    }

    public static function fail(string $message = 'Invalid request parameters', mixed $errors = null, int $code = 400): void
    {
        self::json('fail', $errors, $message, $code);
    }

    public static function error(string $message = 'Internal server error', int $code = 500): void
    {
        self::json('error', null, $message, $code);
    }

    public static function unauthorized(string $message = 'Unauthorized or expired session'): void
    {
        self::json('fail', null, $message, 401);
    }

    public static function forbidden(string $message = 'Access denied'): void
    {
        self::json('fail', null, $message, 403);
    }
}

