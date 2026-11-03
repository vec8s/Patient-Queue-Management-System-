<?php
/**
 * Staff Authentication Portal (Login Only - Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

$pageTitle = "تسجيل دخول الكادر الطبي";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";
?>

<div class="container my-5 py-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <!-- Card Header -->
                <div class="card-header bg-white text-center py-4 border-bottom">
                    <div class="mb-2">
                        <span class="brand-dot" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: var(--color-brand-orange);">
                            <i class="bi bi-person-lock text-white fs-5"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold mb-1" style="color: var(--color-charcoal-ink);">تسجيل دخول الكادر الطبي</h4>
                    <p class="mb-0 text-muted small">يرجى تسجيل الدخول بحسابك المصرح لك به من قبل الإدارة</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
                        <div class="alert alert-warning py-2 small mb-4">
                            <i class="bi bi-exclamation-triangle ms-1"></i> يرجى تسجيل الدخول أولاً للوصول إلى لوحة المتابعة.
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label fw-semibold">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="loginEmail" placeholder="example@gmail.com" required dir="ltr">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="loginPassword" class="form-label fw-semibold">كلمة المرور</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="loginPassword" placeholder="••••••••" required dir="ltr">
                            </div>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold" id="loginBtn">
                                <i class="bi bi-box-arrow-in-right ms-2"></i> دخول النظام
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light border-0 text-center py-3 text-muted small">
                    <i class="bi bi-shield-lock ms-1"></i> يتم إنشاء وتعيين صلاحيات الحسابات مركزياً من قِبل إدارة النظام.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// معالجة تسجيل الدخول
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm ms-2"></span>جارِ تسجيل الدخول...';

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;

    const formData = new URLSearchParams();
    formData.append('action', 'login');
    formData.append('email', email);
    formData.append('password', password);

    fetch('../api/v1/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right ms-2"></i> دخول النظام';

        const token = res.data ? res.data.token : res.token;
        const staff = res.data ? res.data.staff : res.data;

        if (token) {
            localStorage.setItem("pqs", token);
            if (staff) {
                localStorage.setItem("staff", JSON.stringify(staff));
            }
            showAlert('success', 'تم تسجيل الدخول بنجاح! جارِ توجيهك إلى لوحة التحكم...');
            setTimeout(() => { window.location.href = "dashboard.php"; }, 800);
        } else {
            showAlert('danger', res.message || res.error || 'البريد الإلكتروني أو كلمة المرور غير صحيحة.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right ms-2"></i> دخول النظام';
        showAlert('danger', 'خطأ في الاتصال بالخادم: ' + err.message);
    });
});
</script>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
