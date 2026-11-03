<?php 
/**
 * Doctor & Staff Workspace Dashboard (Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use PQS\Auth\AuthGuard;
use PQS\Database\Database;
use PQS\Utils\Config;

Config::load(__DIR__ . '/..');

// Enforce Server-Side Authentication Guard
$currentUser = AuthGuard::enforcePage(['admin', 'doctor', 'nurse']);

$db = Database::getInstance();
$pageTitle = "لوحة تحكم الأطباء والكادر";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";

// Fetch Active Doctors for the Call Modal
$doctors = $db->fetchAll("SELECT id, first_name, last_name, role FROM staff WHERE is_active = 1 AND role IN ('doctor', 'nurse', 'admin')");

// Fetch Active Rooms
$rooms = $db->fetchAll("SELECT id, name FROM rooms WHERE is_active = 1");

// Fetch the next waiting patient
$nextPatient = $db->fetchOne("SELECT q.id AS queue_id, q.queue_number, q.created_at,
                                     p.first_name, p.last_name,
                                     t.name AS reason
                              FROM queue q
                              INNER JOIN checkins c ON c.checkin_id = q.checkin_id
                              INNER JOIN patients p ON p.id = c.patient_id
                              LEFT JOIN appointment_types t ON t.id = c.app_type_id
                              WHERE q.status = 'waiting'
                              ORDER BY q.id ASC LIMIT 1");
?>

<div class="container my-4">
    <!-- Header Section -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-7">
            <h2 class="fw-bold mb-1">لوحة متابعة واستدعاء المرضى</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-person-check ms-1 text-primary"></i> مرحباً، <strong><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></strong> (<?php echo htmlspecialchars($currentUser['role']); ?>)
                <span class="mx-2">•</span>
                <i class="bi bi-calendar-event ms-1"></i> تاريخ اليوم: <?php echo date("Y-m-d"); ?>
            </p>
        </div>
        <div class="col-md-5 text-md-start">
            <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#callPatientModal" <?php echo !$nextPatient ? 'disabled' : ''; ?>>
                <i class="bi bi-megaphone ms-2"></i> استدعاء المريض القادم
            </button>
        </div>
    </div>

    <!-- 3 Queue Columns: Waiting, In-Progress, Completed -->
    <div class="row g-4">
        <!-- Waiting Room -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-hourglass-split ms-2"></i>في صالة الانتظار</h5>
                    <span class="badge bg-white text-primary fs-6 px-2 py-1" id="waitingCount">0</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="waitingList">
                        <li class="list-group-item text-center py-4 text-muted">جارِ تحميل المرضى المنتظرين...</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- In-Progress -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-warning text-dark py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-activity ms-2"></i>قيد الاستشارة والكشف</h5>
                    <span class="badge bg-dark text-white fs-6 px-2 py-1" id="progressCount">0</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="inProgressList">
                        <li class="list-group-item text-center py-4 text-muted">جارِ تحميل الاستشارات الحالية...</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="col-lg-4 col-md-12">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-check2-all ms-2"></i>المكتملة اليوم</h5>
                    <span class="badge bg-white text-success fs-6 px-2 py-1" id="completedCount">0</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="completedList">
                        <li class="list-group-item text-center py-4 text-muted">جارِ تحميل الزيارات المكتملة...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call Next Patient Modal -->
<div class="modal fade" id="callPatientModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-megaphone-fill text-primary ms-2"></i>استدعاء المريض القادم</h5>
                <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <?php if ($nextPatient): ?>
                    <div class="alert alert-primary mb-4 p-3 rounded-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">المريض القادم في الدور:</span>
                            <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($nextPatient['queue_number']); ?></span>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($nextPatient['first_name'] . ' ' . $nextPatient['last_name']); ?></h5>
                        <small class="text-secondary d-block"><i class="bi bi-info-circle ms-1"></i>السبب: <?php echo htmlspecialchars($nextPatient['reason'] ?? 'استشارة'); ?></small>
                        <input type="hidden" id="queueId" value="<?php echo $nextPatient['queue_id']; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="doctorSelect">الطبيب المعالج</label>
                        <select class="form-select" id="doctorSelect" name="doctorSelect" required>
                            <?php foreach ($doctors as $doc): 
                                $prefix = ($doc['role'] === 'doctor') ? 'د. ' : '';
                                $selected = ($doc['id'] == $currentUser['id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $doc['id']; ?>" <?php echo $selected; ?>><?php echo $prefix . htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="roomSelect">غرفة الكشف / الاستشارة</label>
                        <select class="form-select" id="roomSelect" name="roomSelect" required>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                        <h5>لا يوجد مرضى في قائمة الانتظار</h5>
                        <p class="small">تم استدعاء وخدمة جميع المرضى المسجلين حالياً.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                <?php if ($nextPatient): ?>
                    <button type="button" class="btn btn-primary px-4" id="callPatientBtn">
                        <i class="bi bi-bell-fill ms-1"></i> استدعاء المريض الآن
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
