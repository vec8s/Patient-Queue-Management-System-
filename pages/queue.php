<?php 
/**
 * Live Queue Display Screen (Waiting Room TV View - Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use PQS\Utils\Config;

Config::load(__DIR__ . '/..');

$pageTitle = "شاشة صالة الانتظار المباشرة";
$bodyClass = "queue-display bg-light";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";
?>

<div class="container-fluid py-4 px-md-5">
    <!-- Screen Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center">
            <span class="spinner-grow spinner-grow-sm text-danger ms-2" role="status"></span>
            <h4 class="fw-bold mb-0 text-dark">شاشة صالة الانتظار المباشرة</h4>
        </div>
        <div class="text-muted fs-5">
            <i class="bi bi-clock ms-1 text-primary"></i>
            <span id="liveClock"><?php echo date("h:i A"); ?></span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Now Serving Panel -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="fw-bold mb-0 tracking-wide"><i class="bi bi-broadcast ms-2"></i>يتم استدعاؤه الآن</h3>
                </div>
                <div class="card-body py-5 d-flex flex-column justify-content-center" id="nowServingContainer">
                    <div class="py-5 text-muted">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <h4 class="fw-normal">جارِ الاتصال بشاشة الطابور المباشرة...</h4>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 text-muted small">
                    <i class="bi bi-info-circle ms-1"></i> يرجى التوجه فوراً إلى غرفة الكشف المحددة عند ظهور رقم تذكرتك وسماع التنبيه الصوتي.
                </div>
            </div>
        </div>

        <!-- Upcoming Queue Panel -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="fw-bold mb-0 text-center"><i class="bi bi-list-ol ms-2"></i>القادمون في الطابور</h5>
                </div>
                <div class="card-body p-3 bg-light overflow-auto" style="max-height: 520px;">
                    <ul class="list-unstyled mb-0" id="upcomingQueueList">
                        <li class="p-4 text-center text-muted">
                            <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
                            <div>جارِ تحميل قائمة الانتظار...</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Live clock updater
setInterval(() => {
    const now = new Date();
    const clockEl = document.getElementById('liveClock');
    if (clockEl) {
        clockEl.innerText = now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
}, 1000);
</script>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
