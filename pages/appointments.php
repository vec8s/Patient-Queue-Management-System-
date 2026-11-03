<?php
/**
 * Patient Appointment Booking Page (Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use PQS\Database\Database;

$db = Database::getInstance();
$pageTitle = "حجز موعد مسبق";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";

// Fetch Doctors
$doctors = $db->fetchAll("SELECT id, first_name, last_name FROM staff WHERE role = 'doctor' AND is_active = 1");

// Fetch Appointment Types
$appointmentTypes = $db->fetchAll("SELECT id, name, duration FROM appointment_types WHERE is_active = 1");
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="card-title fw-bold mb-1"><i class="bi bi-calendar2-check text-primary ms-2"></i>حجز موعد استشارة مسبق</h4>
                    <p class="text-muted small mb-0">احجز موعدك مسبقاً لتثبيت دورك في الكشف لدى الطبيب المختص.</p>
                </div>

                <div class="card-body p-4">
                    <form id="bookAppointmentForm">
                        <div class="mb-3">
                            <label for="patient_id" class="form-label fw-semibold">رقم ملف المريض <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                <input type="number" class="form-control" id="patient_id" name="patient_id" placeholder="أدخل رقم الملف الطبي المسجل (مثال: 1)" required>
                                <a href="patient-search.php" class="btn btn-outline-secondary">البحث عن رقم الملف</a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="preferredDoctor" class="form-label fw-semibold">الطبيب المختص <span class="text-danger">*</span></label>
                                <select class="form-select" id="preferredDoctor" name="preferredDoctor" required>
                                    <option value="" disabled selected>اختر الطبيب المعالج...</option>
                                    <?php foreach ($doctors as $d): ?>
                                        <option value="<?php echo $d['id']; ?>">د. <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointmentType" class="form-label fw-semibold">نوع العيادة / الاستشارة <span class="text-danger">*</span></label>
                                <select class="form-select" id="appointmentType" name="appointmentType" required>
                                    <option value="" disabled selected>اختر نوع الزيارة...</option>
                                    <?php foreach ($appointmentTypes as $t): ?>
                                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?> (<?php echo $t['duration']; ?> دقيقة)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="appointmentDate" class="form-label fw-semibold">تاريخ الزيارة <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="appointmentDate" name="appointmentDate" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointmentTime" class="form-label fw-semibold">وقت الحضور المتوقع <span class="text-danger">*</span></label>
                                <select class="form-select" id="appointmentTime" name="appointmentTime" required>
                                    <option value="" disabled selected>اختر الفترة الزمنية...</option>
                                    <option value="09:00">09:00 صباحاً</option>
                                    <option value="10:00">10:00 صباحاً</option>
                                    <option value="11:00">11:00 صباحاً</option>
                                    <option value="12:00">12:00 ظهراً</option>
                                    <option value="14:00">02:00 مساءً</option>
                                    <option value="15:00">03:00 مساءً</option>
                                    <option value="16:00">04:00 مساءً</option>
                                    <option value="17:00">05:00 مساءً</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label fw-semibold">سبب الزيارة أو الأعراض الحالية <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="يرجى كتابة وصف مختصر للشكوى الطبية..." required></textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="termsAgreement" name="termsAgreement" required checked>
                            <label class="form-check-label small" for="termsAgreement">
                                أوافق على الحضور قبل موعدي بـ 15 دقيقة وإبراز كود الحجز عند مكتب الاستقبال أو شاشة الحضور الذاتية.
                            </label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="visit.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right ms-1"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="bookBtn">
                                <i class="bi bi-calendar-plus ms-1"></i> تأكيد حجز الموعد
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Booking Success -->
<div class="modal fade" id="appointmentSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill ms-2"></i>تم تأكيد حجز الموعد!</h5>
                <button type="button" class="btn-close btn-close-white ms-0 me-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="text-muted mb-2">يرجى حفظ كود الموعد التالي لتسجيل حضورك به عند الوصول للمركز:</p>
                <div class="display-6 fw-bold text-success my-3 p-3 bg-light rounded-3 border border-success-subtle tracking-wide" id="bookedAppointmentCode">
                    ---
                </div>
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle ms-1"></i> يمكنك إدخال هذا الكود مباشرة في صفحة تسجيل الحضور لإصدار تذكرتك فوراً.
                </div>
            </div>
            <div class="modal-footer border-top d-flex justify-content-between">
                <a href="checkin.php" class="btn btn-success" id="goToCheckinBtn">
                    <i class="bi bi-ticket-detailed ms-1"></i> التوجه لتسجيل الحضور
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('bookAppointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('bookBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm ms-2"></span>جارِ الحجز...';

    const formData = new FormData(this);

    fetch('../api/v1/appointments.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-calendar-plus ms-1"></i> تأكيد حجز الموعد';

        const code = (data.data && data.data.appointmentID) ? data.data.appointmentID : data.appointmentID;
        if (code) {
            document.getElementById('bookedAppointmentCode').innerText = code;
            const modal = new bootstrap.Modal(document.getElementById('appointmentSuccessModal'));
            modal.show();
        } else {
            showAlert('danger', data.message || data.error || 'تعذر حجز الموعد. يرجى التأكد من البيانات والمحاولة مجدداً.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-calendar-plus ms-1"></i> تأكيد حجز الموعد';
        showAlert('danger', 'خطأ في الاتصال بالخادم: ' + err.message);
    });
});
</script>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
