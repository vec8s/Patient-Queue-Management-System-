<?php

/**
 * Patient Queue Management System (PQS) - Development Server Router
 * Emulates web server rewriting for PHP's built-in web server.
 *
 * Used by: php artisan serve / php artisan server
 */

declare(strict_types=1);

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
);

$baseDir = __DIR__;

// 1. Root route: serve index.php
if ($uri === '/' || $uri === '') {
    require_once $baseDir . '/index.php';
    return;
}

$filePath = $baseDir . $uri;

// 2. If physical file exists (static assets like CSS, JS, images, or direct PHP files)
if (file_exists($filePath) && !is_dir($filePath)) {
    return false; // Handled directly by PHP built-in web server
}

// 3. Clean URI for extensionless routing
$cleanUri = trim($uri, '/');

// 4. Check if root contains {cleanUri}.php
if (file_exists($baseDir . '/' . $cleanUri . '.php')) {
    $_SERVER['SCRIPT_NAME'] = '/' . $cleanUri . '.php';
    $_SERVER['PHP_SELF'] = '/' . $cleanUri . '.php';
    require_once $baseDir . '/' . $cleanUri . '.php';
    return;
}

// 5. Check if pages/{cleanUri}.php exists
if (str_starts_with($cleanUri, 'pages/')) {
    $subPath = substr($cleanUri, 6);
    if (file_exists($baseDir . '/pages/' . $subPath . '.php')) {
        $_SERVER['SCRIPT_NAME'] = '/pages/' . $subPath . '.php';
        $_SERVER['PHP_SELF'] = '/pages/' . $subPath . '.php';
        require_once $baseDir . '/pages/' . $subPath . '.php';
        return;
    }
} else {
    if (file_exists($baseDir . '/pages/' . $cleanUri . '.php')) {
        $_SERVER['SCRIPT_NAME'] = '/pages/' . $cleanUri . '.php';
        $_SERVER['PHP_SELF'] = '/pages/' . $cleanUri . '.php';
        require_once $baseDir . '/pages/' . $cleanUri . '.php';
        return;
    }
}

// 6. Check if api/v1/{cleanUri}.php exists
if (str_starts_with($cleanUri, 'api/v1/')) {
    $apiFile = $baseDir . '/' . $cleanUri . '.php';
    if (file_exists($apiFile)) {
        $_SERVER['SCRIPT_NAME'] = '/' . $cleanUri . '.php';
        $_SERVER['PHP_SELF'] = '/' . $cleanUri . '.php';
        require_once $apiFile;
        return;
    }
}

// 7. Directory index fallback
if (is_dir($filePath) && file_exists(rtrim($filePath, '/') . '/index.php')) {
    require_once rtrim($filePath, '/') . '/index.php';
    return;
}

// 8. 404 Not Found Page
http_response_code(404);
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="text-center p-5 bg-white rounded-4 shadow-sm border" style="max-width: 500px;">
        <div class="display-1 fw-bold text-muted mb-3">404</div>
        <h2 class="h4 mb-3">الصفحة المطلوبة غير موجودة</h2>
        <p class="text-muted mb-4">العنوان الذي طلبته: <code><?= htmlspecialchars($uri) ?></code> غير متاح في النظام.</p>
        <a href="/" class="btn btn-primary px-4">الانتقال للرئيسية</a>
    </div>
</body>
</html>

