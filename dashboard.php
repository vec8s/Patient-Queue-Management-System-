<?php 
include_once("php/db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<style>
     .alert-position {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            width: 350px;
        }
</style>
</head>
<body>
<div class="alert-position" id="alertContainer"></div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PQMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="queue.php">Queue Display</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">Admin</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-white me-3" id="userName">Dr. Pamzey</span>
                    <button class="btn btn-outline-light btn-sm" id="logoutBtn">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">Today's Queue</h2>
                <p class="text-muted"><?php echo date("l, F j, Y"); ?>
                </p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#callPatientModal">
                    <i class="bi bi-person-plus"></i> Call Next Patient
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Waiting Room</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="waitingList">
                            <!-- Filled by JavaScript -->
                        </ul>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Total waiting: <span id="waitingCount">0</span> patients</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">In Progress</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="inProgressList">
                            <!-- Filled by JavaScript -->
                        </ul>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Total in progress: <span id="progressCount">0</span> patients</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">Completed</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="completedList">
                            <!-- Filled by JavaScript -->
                        </ul>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Total completed: <span id="completedCount">0</span> patients</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call Patient Modal -->
    <div class="modal fade" id="callPatientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Call Next Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Doctor</label>
                        <select class="form-select" id="doctorSelect" name="doctorSelect">
                            <?php
                                 $outcomes2 = $db->query("SELECT * FROM staff where role='doctor' ");
                                 while($ot = $outcomes2->fetch_assoc()): 
                                    $names = ($ot['role'] == 'doctor') ? "Dr. ".$ot['first_name']." ".$ot['last_name'] : $ot['first_name']." ".$ot['last_name'];
                                    $ids = $ot['id']; 
                            ?>
                            <option value="<?php echo $ids; ?>"><?php echo $names; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Room</label>
                        <select class="form-select" id="roomSelect" name="roomSelect">
                            <?php
                                 $outcomes = $db->query("SELECT * FROM rooms");
                                while($ot = $outcomes->fetch_assoc()): 
                                 $name = $ot['name']; $id = $ot['id'];
                            ?>
                            <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div id="nextPatientInfo">
                         <?php
                                 $outcomes3 = $db->query("SELECT q.*, a.name AS reason,
                                                         p.first_name,p.last_name 
                                                        FROM queue q
                                                        JOIN checkins s ON s.checkin_id = q.checkin_id
                                                        JOIN patients p ON p.id = s.patient_id 
                                                        JOIN appointment_types a ON a.id = s.app_type_id
                                                        WHERE q.status = 'waiting' LIMIT 1");
                                 while($ots = $outcomes3->fetch_assoc()): 
                            ?>
                              <p>Next patient: <strong><?php echo $ots['first_name']." ".$ots['last_name']; ?> (<?php echo $ots['queue_number']; ?>)</strong></p>
                              <p>Appointment time: <?php echo date("h:i A", strtotime($ots['created_at'])); ?></p>
                              <p>Reason: <?php echo $ots['reason']; ?></p>
                              <input type="hidden" value="<?php echo $ots['id']; ?>" name="queueId" id="queueId">
                        <?php endwhile; ?>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="callPatientBtn">Call Patient</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        const staff = JSON.parse(localStorage.getItem("staff"));
        let name = staff.first_name+" "+staff.last_name;
        if(staff.role === "doctor"){ name = "Dr. "+staff.last_name}
        document.getElementById("userName").innerHTML = name;

        // Logout function
        document.getElementById('logoutBtn').addEventListener('click', function() {
            localStorage.removeItem("pqms");
            localStorage.removeItem("staff");
            window.location.href = "login.php";
        });
    </script>
</body>
</html>