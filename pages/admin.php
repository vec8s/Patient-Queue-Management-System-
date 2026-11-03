<?php
/**
 * System Administration Panel (Arabic)
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use PQS\Auth\AuthGuard;
use PQS\Database\Database;
use PQS\Utils\Config;

Config::load(__DIR__ . '/..');

// Enforce Server-Side Admin-Only Protection
$currentUser = AuthGuard::enforcePage(['admin']);

$db = Database::getInstance();
$pageTitle = "لوحة الإدارة العامة";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/navbar.php";

// Fetch Staff List
$staffList = $db->fetchAll("SELECT id, first_name, last_name, email, role, is_active, created_at FROM staff ORDER BY id DESC LIMIT 50");

// Stats
$totalPatients = (int) ($db->fetchOne("SELECT COUNT(*) AS total FROM patients")['total'] ?? 0);
$todayCheckins = (int) ($db->fetchOne("SELECT COUNT(*) AS total FROM checkins WHERE DATE(created_at) = CURDATE()")['total'] ?? 0);
$totalStaff = count($staffList);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1">لوحة الإدارة العامة</h2>
            <p class="text-muted mb-0">متابعة إحصائيات المركز، إدارة الكادر الطبي، وغرف الاستشارة.</p>
        </div>
        <div>
            <span class="badge bg-light text-secondary border px-3 py-2 fs-6">
                <i class="bi bi-shield-lock ms-1"></i> مرحباً، <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?> (مدير النظام)
            </span>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50 text-uppercase fw-bold">إجمالي المرضى المسجلين</div>
                        <h2 class="fw-bold mb-0 mt-1"><?php echo $totalPatients; ?></h2>
                    </div>
                    <div class="display-5 text-white-50"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50 text-uppercase fw-bold">حالات الحضور اليوم</div>
                        <h2 class="fw-bold mb-0 mt-1"><?php echo $todayCheckins; ?></h2>
                    </div>
                    <div class="display-5 text-white-50"><i class="bi bi-ticket-perforated-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-dark text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50 text-uppercase fw-bold">الكادر الطبي والإداري</div>
                        <h2 class="fw-bold mb-0 mt-1"><?php echo $totalStaff; ?></h2>
                    </div>
                    <div class="display-5 text-white-50"><i class="bi bi-person-badge-fill"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Management Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-person-lines-fill text-primary ms-2"></i>قائمة الكادر الطبي والإداري المسجل</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="pe-4">المعرف</th>
                            <th>الاسم</th>
                            <th>الدور الوظيفي</th>
                            <th>البريد الإلكتروني</th>
                            <th>حالة الحساب</th>
                            <th>تاريخ التسجيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffList as $st): 
                            $name = ($st['role'] === 'doctor') ? 'د. ' . $st['first_name'] . ' ' . $st['last_name'] : $st['first_name'] . ' ' . $st['last_name'];
                        ?>
                        <tr>
                            <td class="pe-4 fw-bold text-muted">#<?php echo $st['id']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($name); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($st['role']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($st['email']); ?></td>
                            <td>
                                <?php if ($st['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">نشط</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">معطل</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?php echo date("Y-m-d", strtotime($st['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Status Box -->
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-success ms-2"></i>حالة واستقرار النظام</h5>
        <div class="row g-3 text-muted small">
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill text-success ms-2 fs-5"></i>
                    <div>
                        <div class="fw-bold text-dark">قاعدة البيانات</div>
                        <div>MySQL متصلة ومستقرة (UTF-8)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill text-success ms-2 fs-5"></i>
                    <div>
                        <div class="fw-bold text-dark">محرك الطابور</div>
                        <div>التحديث التلقائي نشط (PSR-4)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill text-success ms-2 fs-5"></i>
                    <div>
                        <div class="fw-bold text-dark">إصدار النظام</div>
                        <div><?php echo Config::get('APP_NAME'); ?> الإصدار <?php echo Config::get('APP_VERSION', '3.0.0'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
