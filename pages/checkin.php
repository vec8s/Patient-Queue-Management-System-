<?php
/**
 * Patient Check-In Page (Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use PQS\Database\Database;

$db = Database::getInstance();
$pageTitle = "تسجيل حضور المريض";
$patientId = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";

// Fetch appointment types for walk-in form
$appointmentTypes = $db->fetchAll("SELECT id, name FROM appointment_types WHERE is_active = 1 ORDER BY id ASC");
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 text-center">
                    <h4 class="fw-bold mb-1"><i class="bi bi-ticket-detailed text-primary ms-2"></i>تسجيل حضور المريض</h4>
                    <p class="text-muted small mb-0">اختر نوع حضورك لاستلام تذكرة الطابور الخاصة بك فوراً.</p>
                </div>

                <div class="card-body p-4">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3" id="checkinTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($patientId > 0) ? '' : 'active'; ?> fw-bold py-2" id="appointment-tab" data-bs-toggle="tab" data-bs-target="#appointmentTab" type="button" role="tab">
                                <i class="bi bi-calendar-check ms-2"></i>لدي موعد مسبق
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($patientId > 0) ? 'active' : ''; ?> fw-bold py-2" id="walkin-tab" data-bs-toggle="tab" data-bs-target="#walkinTab" type="button" role="tab">
                                <i class="bi bi-person-walking ms-2"></i>حضور فوري (Walk-In)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2">
                        <!-- Tab 1: Appointment Check-In -->
                        <div class="tab-pane fade <?php echo ($patientId > 0) ? '' : 'show active'; ?>" id="appointmentTab" role="tabpanel">
                            <form id="appointmentForm">
                                <div class="mb-4">
                                    <label for="appointmentId" class="form-label fw-semibold">كود الموعد المرجعي</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white"><i class="bi bi-qr-code text-primary"></i></span>
                                        <input type="text" class="form-control" id="appointmentId" name="appointmentId" placeholder="مثال: APT20260912001" required>
                                    </div>
                                    <div class="form-text">أدخل كود الحجز الذي حصلت عليه عند حجز موعدك مسبقاً.</div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg" id="appointmentCheckinBtn">
                                        <i class="bi bi-check-circle ms-2"></i> تأكيد الحضور واستخراج التذكرة
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Walk-In Check-In -->
                        <div class="tab-pane fade <?php echo ($patientId > 0) ? 'show active' : ''; ?>" id="walkinTab" role="tabpanel">
                            <form id="walkinForm">
                                <input type="hidden" name="type" value="walk-in">
                                
                                <div class="mb-3">
                                    <label for="patientIdInput" class="form-label fw-semibold">رقم ملف المريض المسجل</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                        <input type="number" class="form-control" id="patientIdInput" name="patientId" value="<?php echo ($patientId > 0) ? $patientId : ''; ?>" placeholder="أدخل رقم الملف الطبي..." required>
                                        <a href="patient-search.php" class="btn btn-outline-secondary">البحث عن ملفي</a>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="appointmentTypeSelect" class="form-label fw-semibold">نوع العيادة / الاستشارة</label>
                                    <select class="form-select" id="appointmentTypeSelect" name="appointmentType" required>
                                        <?php foreach ($appointmentTypes as $at): ?>
                                            <option value="<?php echo $at['id']; ?>"><?php echo htmlspecialchars($at['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="additionalinfo" class="form-label fw-semibold">سبب الزيارة أو الشكوى الطبية</label>
                                    <textarea class="form-control" id="additionalinfo" name="additionalinfo" rows="2" placeholder="اكتب سبباً مختصراً للزيارة الحالية..."></textarea>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg" id="walkinCheckinBtn">
                                        <i class="bi bi-ticket-detailed ms-2"></i> تأكيد وإصدار تذكرة طابور
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Queue Ticket Result -->
<div class="modal fade" id="ticketModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-ticket-perforated-fill ms-2"></i>تذكرة الحضور الخاصة بك</h5>
                <button type="button" class="btn-close btn-close-white ms-0 me-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="text-muted mb-2">رقم دورك في قائمة الانتظار هو:</p>
                <div class="display-3 fw-bold text-primary my-2 p-3 bg-light rounded-4 border border-primary-subtle" id="ticketQueueNumber">
                    ---
                </div>
                <p class="small text-muted mb-0"><i class="bi bi-tv ms-1"></i> يرجى متابعة شاشة صالة الانتظار والتوجه فوراً للعيادة عند استدعاء رقمك.</p>
            </div>
            <div class="modal-footer border-top d-flex justify-content-between">
                <a href="queue.php" class="btn btn-primary">
                    <i class="bi bi-tv ms-1"></i> الانتقال لشاشة الانتظار
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<script>
// Handle Appointment Check-in
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('appointmentCheckinBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm ms-2"></span>جارِ التحقق...';

    const formData = new FormData(this);

    fetch('../api/v1/queue.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle ms-2"></i> تأكيد الحضور واستخراج التذكرة';

        const qNum = (data.data && data.data.quenumber) ? data.data.quenumber : (data.quenumber || data.queue_number);
        if (qNum) {
            document.getElementById('ticketQueueNumber').innerText = qNum;
            const modal = new bootstrap.Modal(document.getElementById('ticketModal'));
            modal.show();
        } else {
            showAlert('danger', data.message || data.error || 'تعذر تسجيل الحضور. يرجى التحقق من الكود المرجعي.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle ms-2"></i> تأكيد الحضور واستخراج التذكرة';
        showAlert('danger', 'خطأ في الاتصال بالخادم: ' + err.message);
    });
});

// Handle Walk-in Check-in
document.getElementById('walkinForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('walkinCheckinBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm ms-2"></span>جارِ إصدار التذكرة...';

    const formData = new FormData(this);

    fetch('../api/v1/queue.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-ticket-detailed ms-2"></i> تأكيد وإصدار تذكرة طابور';

        const qNum = (data.data && data.data.quenumber) ? data.data.quenumber : (data.quenumber || data.queue_number);
        if (qNum) {
            document.getElementById('ticketQueueNumber').innerText = qNum;
            const modal = new bootstrap.Modal(document.getElementById('ticketModal'));
            modal.show();
        } else {
            showAlert('danger', data.message || data.error || 'تعذر إصدار تذكرة الحضور الفوري.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-ticket-detailed ms-2"></i> تأكيد وإصدار تذكرة طابور';
        showAlert('danger', 'خطأ في الاتصال بالخادم: ' + err.message);
    });
});
</script>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
