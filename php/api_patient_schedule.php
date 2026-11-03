<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../api/v1/appointments.php';
} else {
    require_once __DIR__ . '/../api/v1/queue.php';
}
