<?php
/**
 * JWT Authentication & Token Lifecycle Service
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

namespace PQS\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PQS\Utils\Config;
use Throwable;

class JWTService
{
    /**
     * Generate a signed JWT token
     */
    public static function generateToken(int $userId, array $claims = []): string
    {
        $secret = (string) Config::get('JWT_SECRET', 'pqs_app_default_secure_secret_key');
        $algorithm = (string) Config::get('JWT_ALGORITHM', 'HS256');
        $expiry = (int) Config::get('JWT_EXPIRY', 86400);

        $now = time();
        $payload = array_merge([
            'iss'     => Config::get('APP_NAME', 'PQS'),
            'iat'     => $now,
            'nbf'     => $now,
            'exp'     => $now + $expiry,
            'user_id' => $userId
        ], $claims);

        return JWT::encode($payload, $secret, $algorithm);
    }

    /**
     * Verify and decode a JWT token, returns payload object or null
     */
    public static function verifyToken(string $token): ?object
    {
        $secret = (string) Config::get('JWT_SECRET', 'pqs_app_default_secure_secret_key');
        $algorithm = (string) Config::get('JWT_ALGORITHM', 'HS256');

        try {
            return JWT::decode($token, new Key($secret, $algorithm));
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Extract token from HTTP Authorization header or Cookie
     */
    public static function extractToken(): ?string
    {
        // 1. Check HTTP Authorization header
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if ($authHeader && preg_match('/^(Bearer|Peer)\s+(.*)$/i', trim($authHeader), $matches)) {
            return trim($matches[2]);
        }

        // 2. Check Cookie fallback
        if (!empty($_COOKIE['pqs_token'])) {
            return trim($_COOKIE['pqs_token']);
        }

        return null;
    }
}

