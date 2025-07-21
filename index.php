<?php 
include_once("php/db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Patient Queue Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="landing-page">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="text-center text-md-start">
                    <h1 class="display-4 fw-bold mb-3">Clinic Patient Queue Management System</h1>
                    <p class="lead mb-4">Streamline patient flow, reduce wait times, and improve clinic efficiency with our digital queue management solution.</p>
                    <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                        <a href="visit.php" class="btn btn-primary btn-lg px-4">Patient Check-In</a>
                        <a href="login.php" class="btn btn-outline-primary btn-lg px-4">Staff Login</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-none d-md-block">
                <img src="https://via.placeholder.com/600x400" alt="Clinic illustration" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>