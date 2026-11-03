<?php
/**
 * Shared Navbar Template (Headspace Style - Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

use PQS\Auth\AuthGuard;

$currentPage = basename($_SERVER['PHP_SELF']);
$inPages = str_contains($_SERVER['PHP_SELF'] ?? '', '/pages/');
$basePath = $inPages ? '../' : '';
$pagesPath = $inPages ? '' : 'pages/';

$isStaffArea = in_array($currentPage, ['dashboard.php', 'admin.php', 'login.php']);
$user = AuthGuard::user();
?>
<!-- Headspace Sunbeam Yellow Top Strip -->
<div class="headspace-announcement d-none d-sm-block">
    <i class="bi bi-sparkles ms-1"></i> مرحباً بكم في نظام إدارة طوابير وحجوزات المرضى الذكي — تجربة صحية رقمية مريحة وهادئة
</div>

<!-- Primary Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand navbar-brand-title d-flex align-items-center" href="<?php echo $basePath; ?>index.php">
            <span class="brand-dot ms-2"></span>
            <span class="fw-bold">نظام طوابير المرضى</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="تبديل القائمة">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 pe-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'index.php') ? 'active fw-bold' : ''; ?>" href="<?php echo $basePath; ?>index.php">
                        الرئيسية
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array($currentPage, ['visit.php', 'checkin.php', 'registration.php', 'patient-search.php']) ? 'active fw-bold' : ''; ?>" href="<?php echo $basePath . $pagesPath; ?>visit.php">
                        تسجيل الحضور
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'appointments.php') ? 'active fw-bold' : ''; ?>" href="<?php echo $basePath . $pagesPath; ?>appointments.php">
                        حجز موعد
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'queue.php') ? 'active fw-bold' : ''; ?>" href="<?php echo $basePath . $pagesPath; ?>queue.php">
                        شاشة صالة الانتظار
                    </a>
                </li>
                <?php if ($isStaffArea || $user): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'dashboard.php') ? 'active fw-bold' : ''; ?>" href="<?php echo $basePath . $pagesPath; ?>dashboard.php">
                        لوحة الأطباء
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'admin.php') ? 'active fw-bold' : ''; ?>" href="<?php echo $basePath . $pagesPath; ?>admin.php">
                        الإدارة
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Staff Portal Entry or Profile Info -->
            <div class="d-flex align-items-center gap-2">
                <div id="staffAuthNavArea">
                    <a href="<?php echo $basePath . $pagesPath; ?>login.php" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="loginNavLink">
                        <i class="bi bi-person-lock ms-1"></i> بوابة الكادر الطبي
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
