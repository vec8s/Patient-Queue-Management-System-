<?php
/**
 * Legacy Auth Helper Bridge
 * Uses PQS\Auth\JWTService and PQS\Auth\AuthGuard
 */
require_once __DIR__ . '/../vendor/autoload.php';

use PQS\Auth\JWTService;
use PQS\Auth\AuthGuard;
use PQS\Utils\Response;

function generateJWT($userId, $extraClaims = []) {
    return JWTService::generateToken((int) $userId, $extraClaims);
}

function verifyJWT($token) {
    $payload = JWTService::verifyToken($token);
    return $payload->user_id ?? null;
}

function validateToken() {
    $user = AuthGuard::enforceApi();
    return $user['id'];
}

function sendJsonResponse($data, $statusCode = 200) {
    Response::json('success', $data, '', $statusCode);
}
