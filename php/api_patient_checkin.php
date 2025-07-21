<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once '../vendor/autoload.php'; // Include the JWT library (e.g., Firebase JWT library)
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// Secret key for JWT (change this to a long and secure random string)
$secretKey = 'pamzey@7877881825419880518';
$algorithm = "HS256";

// Only allow POST requests
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['appointmentId']))) {
    //http_response_code(405);
    //echo json_encode(['error' => 'Method Not Allowed']);
    //exit;


// Get POST data (expects JSON)
//$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['appointmentId'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing fields: $field"]);
        exit;
    }
}
$data = getPatientAppointment($_POST['appointmentId'])[0];
$sqlarb = mysqli_query($db, "SELECT * FROM queue");
$lastid = mysqli_num_rows($sqlarb);
$queue_number = (strpos($_POST['appointmentId'], "APT") !== false) ? 'APPT-'.($lastid + 1) : 'WALK-'.($lastid + 1);

//$queue_number = strtoupper((substr($data['appointment'], 0, 3))).($lastid + 1);
// Prepare and execute insert
$query = "INSERT INTO queue (checkin_id,queue_number) VALUES (?, ?)";
$params = [
    $data['id'],
    $queue_number
];

$insertId = executeQuery($query, $params, true);

if ($insertId) {
    // Fetch the saved record
    echo json_encode(['success' => true, 'quenumber' => $queue_number]);
    exit;
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to register patient']);
    exit;
}

}
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['type']) == 'walk-in')) {

// Validate required fields
$required = ['patientId','appointmentType','additionalinfo','termsAgreement'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}

$idnumber = getCheckinCount() + 1;
$appReferenceNumber = 'WALK' . date("Ymd", strtotime(date('Y-m-d'))) . $idnumber;

$query = "INSERT INTO checkins (patient_id, app_type_id,reason, consent, reference_number,type_id) VALUES (?, ?, ?, ?,?,?)";
$params = [ 
    intval($_POST['patientId']),
    intval($_POST['appointmentType']),
    $_POST['additionalinfo'],
    $_POST['termsAgreement'] == "on" ? 1 : 0,
    $appReferenceNumber,
    2
];

$insertId = executeQuery($query, $params, true);
 
if($insertId){

     // $data = getPatientAppointment($_POST['appointmentId'])[0];
    $sqlarb = mysqli_query($db, "SELECT * FROM queue");
           $lastid = mysqli_num_rows($sqlarb);
     $queue_number = 'WALK-'.($lastid + 1);

//$queue_number = strtoupper((substr($data['appointment'], 0, 3))).($lastid + 1);
// Prepare and execute insert
$query = "INSERT INTO queue (checkin_id,queue_number) VALUES (?, ?)";
$params = [
    $insertId,
    $queue_number
];

$insertId = executeQuery($query, $params, true);

if ($insertId) {
    // Fetch the saved record
    echo json_encode(['success' => true, 'quenumber' => $queue_number]);
    exit;
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to queue patient']);
    exit;
}
    
}
 else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to register patient']);
    exit;
}

}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
       $appointmentId = $_GET['appointmentID'];
    if ($appointmentId) {
        // Fetch the saved record
        $data = getPatientAppointment($appointmentId);
        echo json_encode(['success' => true, 'appointment' => $data]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to register patient']);
        exit;
    }

}
elseif (($_SERVER['REQUEST_METHOD'] === 'POST') && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required = ['doctorId', 'roomNumber', 'queueId'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $field"]);
            exit;
        }
    }

    $query = "UPDATE queue SET called_by = ?, room_id = ?, status = ?, updated_at =?, called_at=? WHERE id = ?";
    $params = [
        intval($input['doctorId']),
        intval($input['roomNumber']),
        'in_progress',
        date("Y-m-d H:i:s"),
        date("Y-m-d H:i:s"),
        intval($input['queueId'])
    ];

    $updateResult = executeQuery($query, $params);

    if ($updateResult) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update queue',"data" => $updateResult]);
        exit;
    }
}
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

function getPatientAppointment($appid){
    global $db;
    $patientrecod = [];
    $res = mysqli_query($db, "SELECT a.*, s.first_name AS fname,s.last_name AS lname,
                              p.first_name,p.last_name,t.name AS appoint_name
                              FROM checkins a 
                              INNER JOIN patients p ON p.id = a.patient_id
                              LEFT JOIN staff s ON s.id = a.doctor_id 
                              LEFT JOIN appointment_types t ON t.id = a.app_type_id 
                              WHERE a.reference_number = '$appid' ");
    while ($row = mysqli_fetch_assoc($res)) {

            $patientrecod [] = [ 'fullname' => $row['first_name']." ".$row['last_name'],
                                 "appointment_date" => $row['appointment_date'],
                                 "doctor" => $row['fname']." ".$row['lname'],
                                 "appointment" => $row['appoint_name'],
                                 "time" => $row['scheduled_time'],
                                 "reason" => $row['reason'],
                                 "appnumber" => $row['reference_number'],
                                 "id" => $row['checkin_id']
                                ];
        
    }

   return $patientrecod;
}

function getCheckinCount(){
    global $db;
    $sqlarb = mysqli_query($db, "SELECT MAX(checkin_id) AS last_id FROM checkins");
    $row = mysqli_fetch_assoc($sqlarb);
    $lastid = $row['last_id'];
    return $lastid;
}

function verifyJWT($token,$algorithm) {
    global $secretKey;
    try {
          $decoded = JWT::decode($token, new Key($secretKey,$algorithm));
          return $decoded->user_id;
    } catch (Exception $e) {
        return null; // Token is invalid or expired
    }
}


function ValidateToken(){

    global $algorithm;
	$headers = getallheaders();
	$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

	if (!$authHeader) {

		http_response_code(401);
		echo json_encode(['message' => 'Invalid Authorization header']);
		exit;                
    }

	    $token = str_replace('Peer','', $authHeader);
        $user_id = verifyJWT($token,$algorithm);
        if ($user_id == null) {

			http_response_code(401); // Unauthorized
            echo json_encode(['message' => 'Unauthorized']);
            exit;
        }  

    return $user_id;

}
?>