<?php
include_once("php/db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">PQMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.html">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="queue.html">Queue Display</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.html">Admin</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-white me-3">Admin User</span>
                    <button class="btn btn-outline-light btn-sm">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">System Administration</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card h-100 admin-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-primary mb-3 mx-auto">
                            <i class="bi bi-people text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5 class="card-title">Staff Management</h5>
                        <p class="card-text text-muted">Add, edit, or remove staff members and their permissions</p>
                        <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staffModal">Manage</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 admin-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-success mb-3 mx-auto">
                            <i class="bi bi-calendar-check text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5 class="card-title">Appointment Settings</h5>
                        <p class="card-text text-muted">Configure appointment types, durations, and availability</p>
                        <a href="#" class="btn btn-outline-success">Configure</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 admin-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-info mb-3 mx-auto">
                            <i class="bi bi-tv text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5 class="card-title">Display Settings</h5>
                        <p class="card-text text-muted">Customize queue display screens and notifications</p>
                        <a href="#" class="btn btn-outline-info">Customize</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 admin-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-warning mb-3 mx-auto">
                            <i class="bi bi-graph-up text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5 class="card-title">Reports & Analytics</h5>
                        <p class="card-text text-muted">View clinic performance metrics and patient flow data</p>
                        <a href="#" class="btn btn-outline-warning">View Reports</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">System Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="system-status-item">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span>Queue Management: <strong>Active</strong></span>
                                </div>
                                <div class="system-status-item">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span>Database Connection: <strong>Connected</strong></span>
                                </div>
                                <div class="system-status-item">
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                    <span>Display Screens: <strong>1/2 Connected</strong></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="system-status-item">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span>Last Backup: <strong>Today 02:00 AM</strong></span>
                                </div>
                                <div class="system-status-item">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span>System Version: <strong>2.1.4</strong></span>
                                </div>
                                <div class="system-status-item">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    <span>Uptime: <strong>7 days 4 hours</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Management Modal -->
    <div class="modal fade" id="staffModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Staff Management</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                       <?php
                                          $outcomes = $db->query("SELECT * FROM staff LIMIT 8");
                                          while($ot = $outcomes->fetch_assoc()): 
                                            $names = ($ot['role'] == 'doctor') ? "Dr. ".$ot['first_name']." ".$ot['last_name'] : $ot['first_name']." ".$ot['last_name'];

                                        ?>

                                 <tr>
                                    <td><?php echo $names; ?></td>
                                    <td><?php echo $ot['role']; ?></td>
                                    <td><?php echo $ot['email']; ?></td>
                                    <td>
                                        <?php if($ot['is_active']) { ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php } else { ?>
                                            <span class="badge bg-warning text-dark">On Leave</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </td>
                                </tr>

                                       <?php endwhile; ?>
                                
                                
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a type="button" href='login.php' class="btn btn-primary">Add New Staff</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>