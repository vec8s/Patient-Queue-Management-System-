<?php 
/**
 * Landing Page (Headspace Style - Arabic)
 * Patient Queue Management System
 */
require_once __DIR__ . "/vendor/autoload.php";
$pageTitle = "الرئيسية";
$bodyClass = "landing-page";
include_once __DIR__ . "/includes/header.php";
include_once __DIR__ . "/includes/navbar.php";
?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="row align-items-center py-4 my-2">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="text-center text-lg-start">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3 fw-bold">
                    <span class="brand-dot ms-1"></span> منظومة الرعاية الصحية الهادئة
                </span>
                <h1 class="display-4 fw-bold mb-3" style="letter-spacing: -1.5px;">
                    نظام إدارة طوابير وحجوزات المرضى
                </h1>
                <p class="lead mb-4 fs-5" style="color: var(--color-graphite);">
                    تنظيم انسيابي ومريح لحركة المراجعين في العيادات والمراكز الصحية، لتقليل التوتر وفترات الانتظار، مع واجهات تفاعلية مريحة للعين.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="pages/visit.php" class="btn btn-primary btn-lg px-4 shadow-sm">
                        <i class="bi bi-person-check ms-2"></i> تسجيل حضور مريض
                    </a>
                    <a href="pages/appointments.php" class="btn btn-dark btn-lg px-4">
                        <i class="bi bi-calendar-check ms-2"></i> حجز موعد مسبق
                    </a>
                    <a href="pages/queue.php" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-tv ms-2"></i> شاشة صالة الانتظار
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6 text-center">
            <div class="p-2">
                <img src="images/profile.png" alt="إدارة طوابير المرضى" class="img-fluid rounded-4 shadow-sm border" style="max-height: 400px; object-fit: cover; border-color: var(--color-linen) !important;">
            </div>
        </div>
    </div>

    <!-- Feature Cards Band -->
    <div class="row g-4 mt-4 pt-4 border-top" style="border-color: var(--color-linen) !important;">
        <div class="col-md-4">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <div class="fs-1 mb-3 text-primary"><i class="bi bi-qr-code-scan"></i></div>
                    <h5 class="fw-bold mb-2">خدمة ذاتية سلسة</h5>
                    <p class="text-muted mb-0 small">استخراج فوري لتذاكر الطابور للمواعيد المسبقة أو الحضور المباشر دون الوقوف في طوابير الاستقبال.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 p-3 card-yellow">
                <div class="card-body">
                    <div class="fs-1 mb-3" style="color: var(--color-deep-indigo);"><i class="bi bi-broadcast"></i></div>
                    <h5 class="fw-bold mb-2" style="color: var(--color-charcoal-ink);">شاشة انتظار هادئة وواضحة</h5>
                    <p class="mb-0 small" style="color: var(--color-graphite);">عرض رقم المريض المستدعى ورقم العيادة واسم الطبيب المعالج مع تنبيه صوتي وتحديث لحظي.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <div class="fs-1 mb-3 text-dark"><i class="bi bi-person-badge"></i></div>
                    <h5 class="fw-bold mb-2">مساحة عمل الطبيب</h5>
                    <p class="text-muted mb-0 small">مناداة المريض القادم بنقرة واحدة وتحديد غرفة الاستشارة ومتابعة حالة طابور الكشف اليومي.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . "/includes/footer.php"; ?>