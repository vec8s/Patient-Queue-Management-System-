<?php
/**
 * Shared Header Template (Arabic & RTL)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PQS\Utils\Config;

Config::load(__DIR__ . '/..');

$systemTitle = "نظام إدارة طوابير وحجوزات المرضى";
$appName = Config::get('APP_NAME', 'PQS');
$title = isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' . $systemTitle : $systemTitle;
$inPages = str_contains($_SERVER['PHP_SELF'] ?? '', '/pages/');
$basePath = $inPages ? '../' : '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom Styles -->
    <link href="<?php echo $basePath; ?>css/style.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Cairo', system-ui, -apple-system, sans-serif;
        }
        .alert-position {
            position: fixed;
            top: 20px;
            left: 20px;
            right: auto;
            z-index: 1200;
            width: 380px;
            max-width: 90vw;
        }
        .navbar-brand-title {
            font-weight: 700;
            letter-spacing: -0.2px;
        }
    </style>
</head>
<body class="<?php echo $bodyClass ?? 'bg-light'; ?>">
<!-- Global Alert Container -->
<div class="alert-position" id="alertContainer"></div>
