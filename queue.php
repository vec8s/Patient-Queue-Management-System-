<?php 
include_once("php/db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Queue Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="queue-display">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-md-8">
                <div class="current-patient-section p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-white">NOW SERVING</h2>
                    </div>
                    <div class="current-patient-card text-center py-5">
                       
                             <?php
                                        $outcomes = $db->query("SELECT q.*,p.first_name,p.last_name,
                                                               f.first_name AS doctor_fname,f.last_name AS doctor_sname,
                                                               r.name AS room_name
                                                             FROM queue q
                                                             INNER JOIN checkins s ON s.checkin_id = q.checkin_id
                                                             INNER JOIN patients p ON p.id = s.patient_id 
                                                             INNER JOIN staff f ON f.id = q.called_by 
                                                             INNER JOIN rooms r ON r.id = q.room_id
                                                             where q.status = 'in_progress' ORDER BY checkin_id DESC LIMIT 1");
                                        if ($outcomes && $outcomes->num_rows > 0):
                                            while($ot = $outcomes->fetch_assoc()): 
                                ?>

                        <div class="queue-number-lg mb-3"><?php echo $ot['queue_number']; ?></div>
                        <h3 class="patient-name"><?php echo $ot['first_name'] . ' ' . $ot['last_name']; ?></h3>
                        <div class="patient-info">
                            <div class="info-item">
                                <i class="bi bi-clock"></i>
                                <span><?php echo date("h:i A", strtotime($ot['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-person"></i>
                                <span><?php echo $ot['doctor_fname'] . ' ' . $ot['doctor_sname']; ?></span>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-door-open"></i>
                                <span><?php echo $ot['room_name']; ?></span>
                            </div>
                        </div>

                                <?php 
                                        endwhile; 
                                        else:
                                ?>

                      <h3 class="patient-name">No Patient..</h3>
                           <?php endif; ?>

                    </div>
                    <div class="text-center mt-3">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="upcoming-queue-section p-4 h-100">
                    <h4 class="fw-bold mb-4 text-center">UPCOMING PATIENTS</h4>
                    <ul class="upcoming-list">

                                <?php
                                        $outcomes = $db->query("SELECT q.*,p.first_name,p.last_name
                                                             FROM queue q
                                                             INNER JOIN checkins s ON s.checkin_id = q.checkin_id
                                                             INNER JOIN patients p ON p.id = s.patient_id 
                                                             where q.status = 'waiting' ORDER BY checkin_id DESC LIMIT 6");
                                        if ($outcomes && $outcomes->num_rows > 0):
                                            while($ot = $outcomes->fetch_assoc()): 
                                ?>
                                        <li class="upcoming-item">
                                             <div class="queue-number-sm"><?php echo $ot['queue_number']; ?></div>
                                             <div class="patient-details">
                                             <div class="patient-name"><?php echo $ot['first_name'] . ' ' . $ot['last_name']; ?></div>
                                             <div class="appointment-time"><?php echo date("h:i A", strtotime($ot['created_at'])); ?></div>
                                             </div>
                                        </li>
                                <?php 
                                            endwhile; 
                                        else: 
                                ?>
                                        <li class="upcoming-item">
                                            <div class="patient-details">
                                                <div class="patient-name">No upcoming patients</div>
                                            </div>
                                        </li>
                                <?php endif; ?>

                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>