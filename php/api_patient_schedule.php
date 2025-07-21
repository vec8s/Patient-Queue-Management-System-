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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //http_response_code(405);
    //echo json_encode(['error' => 'Method Not Allowed']);
    //exit;


// Get POST data (expects JSON)
//$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['patient_id', 'preferredDoctor', 'appointmentType', 'appointmentDate', 'appointmentTime','reason','termsAgreement'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}
$idnumber = getCheckinCount() + 1;
$appReferenceNumber = 'APT' . date("Ymd", strtotime($_POST['appointmentDate'])) . $idnumber;
// Prepare and execute insert
$query = "INSERT INTO checkins (patient_id, doctor_id, app_type_id, appointment_date, scheduled_time, reason, consent,reference_number,type_id) VALUES (?, ?, ?, ?, ?, ?, ?,?,?)";
$params = [
    $_POST['patient_id'],
    $_POST['preferredDoctor'],
    $_POST['appointmentType'],
    $_POST['appointmentDate'],
    $_POST['appointmentTime'],
    $_POST['reason'],
    $_POST['termsAgreement'] == "on" ? 1 : 0,
    $appReferenceNumber,
    1
];

$insertId = executeQuery($query, $params, true);

if ($insertId) {
    // Fetch the saved record
     $result = getPatientAppointment($appReferenceNumber);
    //Appointment ID is made up of string APT,appointmentDate and Appointment ID
    //$appointmentID = "APT".str_replace('-', '',$patient['appointment_date'])."".$patient['id'];
    echo json_encode(['success' => true, 'appointmentID' => $appReferenceNumber,'patient' => $result]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to register patient']);
}

}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    if (isset($_GET['appointmentID'])) {
        // Fetch the saved record
        $data = getPatientAppointment($_GET['appointmentID']);
        echo json_encode(['success' => true, 'appointment' => $data]);

    } elseif (isset($_GET['queuepatients'])) {
        // Fetch the saved record
        $data = getQuepatients(date('Y-m-d'));
        echo json_encode(['success' => true, 'appointment' => $data]);
       
    } else{ http_response_code(500);
            echo json_encode(['error' => 'Failed to get patients']);
        }

}else{
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}



function getPatientAppointment($reference_number){
    global $db;
    $patientrecod = [];
    $res = mysqli_query($db, "SELECT a.*, s.first_name AS fname,s.last_name AS lname,
                              p.first_name,p.last_name,t.name AS appoint_name
                              FROM checkins a 
                              INNER JOIN patients p ON p.id = a.patient_id
                              LEFT JOIN staff s ON s.id = a.doctor_id 
                              LEFT JOIN appointment_types t ON t.id = a.app_type_id 
                              WHERE a.reference_number = '$reference_number' ");
    while ($row = mysqli_fetch_assoc($res)) {

            $patientrecod [] = [ 'fullname' => $row['first_name']." ".$row['last_name'],
                                 "appointment_date" => $row['appointment_date'],
                                 "doctor" => $row['fname']." ".$row['lname'],
                                 "appointment" => $row['appoint_name'],
                                 "time" => $row['scheduled_time'],
                                 "reason" => $row['reason'],
                                 "appnumber" => $row['reference_number']
                                ];
        
    }

   return $patientrecod;
}

function getQuepatients(){
    global $db;
    $patientrecod = [ "waiting"=>[],
                     "inProgress"=>[],
                      "completed"=>[]
                    ];

$res = mysqli_query($db, "SELECT a.*, s.first_name AS fname,s.last_name AS lname,
                    p.first_name,p.last_name,t.name AS appoint_name,
                    q.priority AS priorityname,q.status AS status_name,
                    q.queue_number,q.created_at,r.name AS room_name,q.called_at,
                    d.first_name AS doctorfname, d.last_name AS doctorlastname
                    FROM checkins a 
                    INNER JOIN patients p ON p.id = a.patient_id
                    LEFT JOIN staff s ON s.id = a.doctor_id 
                    LEFT JOIN appointment_types t ON t.id = a.app_type_id 
                    LEFT JOIN queue q ON q.checkin_id = a.checkin_id
                    LEFT JOIN staff d ON d.id = q.called_by 
                    LEFT JOIN rooms r ON r.id = q.room_id ");
while ($row = mysqli_fetch_assoc($res)) {

            
            if ("waiting" == $row['status_name']) {
		          
		         $patientrecod["waiting"][] = [ "id" => $row['queue_number'],
                                        'name' => $row['first_name']." ".$row['last_name'],
                                        "time" => date("H:i A", strtotime($row['created_at']) + 12 * 60),
                                        "priority" => $row['priorityname'],
                                        "reason" => $row['appoint_name'],
                                     ];
            }
            if ("in_progress" == $row['status_name']) {
		          
                $patientrecod["inProgress"][] = [ "id" => $row['queue_number'],
                                                'name' => $row['first_name']." ".$row['last_name'],
                                                 "time" => date("H:i A", strtotime($row['called_at'])),
                                                 "doctor" => 'Dr'.$row['doctorfname']." ".$row['doctorlastname'],
                                                 "room" => $row['room_name'],
                                               ];
             }
             if ("completed" == $row['status_name']) {
		          
                $patientrecod["completed"][] = [ "id" => $row['queue_number'],
                                                'name' => $row['first_name']." ".$row['last_name'],
                                                "time" => date("H:i A", strtotime($row['updated_at'])),
                                               ];
             }


        
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

// Function to verify a JWT token
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
