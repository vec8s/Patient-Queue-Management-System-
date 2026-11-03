<?php
/**
 * New Patient Registration (Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

$pageTitle = "تسجيل ملف مريض جديد";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="card-title fw-bold mb-1"><i class="bi bi-person-plus text-primary ms-2"></i>تسجيل ملف مريض جديد</h4>
                    <p class="text-muted small mb-0">يرجى إدخال البيانات الشخصية بدقة لإنشاء ملف المريض ثم الحصول على تذكرة الطابور.</p>
                </div>
                <div class="card-body p-4">
                    <form id="registrationForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label fw-semibold">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="مثال: أحمد" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label fw-semibold">اسم العائلة / اللقب <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="مثال: العلي" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label fw-semibold">تاريخ الميلاد <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dob" name="dob" max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label fw-semibold">الجنس <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="" selected disabled>اختر الجنس...</option>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                    <option value="other">أخرى</option>
                                    <option value="prefer-not-to-say">أفضل عدم التحديد</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">رقم الهاتف <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="مثال: 0501234567" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label fw-semibold">المدينة / الحي السكني</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="مثال: حي الروضة" required>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="consent" name="consent" required checked>
                            <label class="form-check-label small" for="consent">
                                أوافق على تسجيل بياناتي الطبية والشخصية واستخدامها لغرض استدعائي في الطابور وتقديم الرعاية الصحية.
                            </label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="visit.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right ms-1"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="registerBtn">
                                <i class="bi bi-check-circle ms-1"></i> حفظ الملف واستكمال الحضور
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('registerBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm ms-2"></span>جارِ الحفظ...';

    const formData = new FormData(this);

    fetch('../api/v1/patients.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle ms-1"></i> حفظ الملف واستكمال الحضور';

        const pId = (data.data && data.data.patient_id) ? data.data.patient_id : data.patient;
        if (pId) {
            showAlert('success', 'تم تسجيل الملف الطبي بنجاح! جارِ توجيهك لتسجيل الحضور...');
            setTimeout(() => {
                window.location.href = `checkin.php?patient_id=${pId}`;
            }, 1000);
        } else {
            showAlert('danger', data.message || data.error || 'تعذر تسجيل المريض.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle ms-1"></i> حفظ الملف واستكمال الحضور';
        showAlert('danger', 'خطأ في الاتصال بالخادم: ' + err.message);
    });
});
</script>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
