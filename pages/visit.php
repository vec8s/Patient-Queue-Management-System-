<?php 
/**
 * Patient Welcome & Check-In Gateway (Arabic)
 * Patient Queue Management System
 */
require_once __DIR__ . "/../vendor/autoload.php";
$pageTitle = "بوابة استقبال المرضى";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";
?>

<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border rounded-4 overflow-hidden" style="border-color: var(--color-linen) !important;">
                <div class="card-header bg-white text-center py-4 border-bottom" style="border-color: var(--color-linen) !important;">
                    <div class="mb-2">
                        <span class="brand-dot" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; background-color: var(--color-brand-orange);">
                            <i class="bi bi-hospital text-white fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1" style="color: var(--color-charcoal-ink);">أهلاً بكم في المركز الصحي</h3>
                    <p class="mb-0 text-muted small">بوابة تسجيل حضور المرضى وطابور الانتظار</p>
                </div>
                <div class="card-body text-center p-4 p-md-5">
                    <h4 class="fw-bold mb-3" style="color: var(--color-charcoal-ink);">هل قمت بزيارتنا من قبل؟</h4>
                    <p class="text-muted mb-4">يرجى اختيار الخيار المناسب لك للحصول على تذكرة الانتظار الخاصة بك:</p>
                    
                    <div class="d-grid gap-3">
                        <a href="patient-search.php" class="btn btn-primary btn-lg py-3">
                            <i class="bi bi-person-check-fill ms-2"></i>
                            نعم، قمت بالزيارة سابقاً (بحث بالاسم أو الهاتف)
                        </a>
                        <a href="registration.php" class="btn btn-outline-primary btn-lg py-3">
                            <i class="bi bi-person-plus-fill ms-2"></i>
                            لا، هذه زيارتي الأولى (تسجيل ملف جديد)
                        </a>
                        <a href="checkin.php" class="btn btn-light border btn-lg py-2 mt-2">
                            <i class="bi bi-ticket-perforated ms-2"></i>
                            لدي كود موعد مسبق (تسجيل حضور فوري)
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 text-center py-3 text-muted small">
                    إذا واجهت أي صعوبة، يسعد موظف الاستقبال بمساعدتك مباشرة.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>