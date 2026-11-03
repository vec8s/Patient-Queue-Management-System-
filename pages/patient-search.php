<?php
/**
 * Patient Search & Lookup (Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

$pageTitle = "البحث عن مريض سابق";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="card-title fw-bold mb-1"><i class="bi bi-search text-primary ms-2"></i>البحث عن سجل المريض</h4>
                    <p class="text-muted small mb-0">ابحث عن المريض بالاسم أو رقم الهاتف المسجل لاستكمال تسجيل الحضور فوراً.</p>
                </div>
                <div class="card-body p-4">
                    <form id="searchForm">
                        <div class="row mb-3">
                            <div class="col-md-7 mb-3 mb-md-0">
                                <label for="searchTerm" class="form-label fw-semibold">اسم المريض أو رقم الهاتف</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="searchTerm" placeholder="أدخل اسم المريض أو رقم هاتفه..." required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="searchDob" class="form-label fw-semibold">تاريخ الميلاد (اختياري)</label>
                                <input type="date" class="form-control" id="searchDob">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="visit.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right ms-1"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="searchBtn">
                                <i class="bi bi-search ms-1"></i> بحث في السجلات
                            </button>
                        </div>
                    </form>

                    <!-- نتائج البحث -->
                    <div id="searchResultsSection" class="mt-4 pt-3 border-top" style="display: none;">
                        <h5 class="fw-bold mb-3">نتائج البحث المطابقة</h5>
                        <div id="patientListContainer">
                            <!-- Populated dynamically via safe API search -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const searchVal = document.getElementById('searchTerm').value.trim();
    const dobVal = document.getElementById('searchDob').value;
    const btn = document.getElementById('searchBtn');

    if (!searchVal) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm ms-2"></span>جارِ البحث...';

    const params = new URLSearchParams();
    params.append('search', searchVal);
    if (dobVal) params.append('dob', dobVal);

    fetch(`../api/v1/patients.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search ms-1"></i> بحث في السجلات';

            const section = document.getElementById('searchResultsSection');
            const container = document.getElementById('patientListContainer');
            section.style.display = 'block';
            container.innerHTML = '';

            const patients = (data.data && data.data.patients) ? data.data.patients : (data.patients || []);
            if (patients.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-warning d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-exclamation-circle ms-2"></i>
                            لم يتم العثور على مريض مسجل بهذه البيانات.
                        </div>
                        <a href="registration.php" class="btn btn-warning btn-sm fw-bold">تسجيل ملف جديد</a>
                    </div>
                `;
            } else {
                patients.forEach(p => {
                    const card = document.createElement('div');
                    card.className = 'card border mb-3 shadow-sm rounded-3';
                    card.innerHTML = `
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h6 class="fw-bold mb-1 text-primary fs-5">${p.first_name} ${p.last_name}</h6>
                                <div class="text-muted small">
                                    <span class="ms-3"><i class="bi bi-calendar ms-1"></i>الميلاد: ${p.dob}</span>
                                    <span class="ms-3"><i class="bi bi-telephone ms-1"></i>${p.phone}</span>
                                    <span><i class="bi bi-geo-alt ms-1"></i>${p.address || 'غير محدد'}</span>
                                </div>
                            </div>
                            <div>
                                <a href="checkin.php?patient_id=${p.id}" class="btn btn-success px-4">
                                    <i class="bi bi-check-circle ms-1"></i> تسجيل حضور الآن
                                </a>
                            </div>
                        </div>
                    `;
                    container.appendChild(card);
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search ms-1"></i> بحث في السجلات';
            showAlert('danger', 'تعذر الاتصال بخدمة البحث: ' + err.message);
        });
});
</script>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
