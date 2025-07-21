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
if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['dob'])) {
// Get POST data (expects JSON)
//$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['firstName', 'lastName', 'dob', 'gender', 'address', 'phone', 'consent'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}

// Prepare and execute insert
$query = "INSERT INTO patients (first_name, last_name, date_of_birth, gender, address, phone, consent) VALUES (?, ?, ?, ?, ?, ?, ?)";
$params = [
    $_POST['firstName'],
    $_POST['lastName'],
    $_POST['dob'],
    $_POST['gender'],
    $_POST['address'],
    $_POST['phone'],
    $_POST['consent'] == "on" ? 1 : 0
];

$insertId = executeQuery($query, $params, true);

if ($insertId) {
    // Fetch the saved record
    echo json_encode(['success' => true, 'patient' => $insertId]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to register patient']);
}

}
elseif (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['auto'])) {

     $required = ['auto'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $field"]);
            exit;
        }
    }

    // Get current time
    $currentTime = new DateTime();

    // Fetch all queue records with status 'in_progress'
    $stmt = $db->prepare("SELECT id, called_at FROM queue WHERE status = 'in_progress'");
    $stmt->execute();
    $result = $stmt->get_result();

    $updated = 0;

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['called_at'])) {
            $calledAt = new DateTime($row['called_at']);
            $interval = $calledAt->diff($currentTime);
            $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
            if ($minutes > 20) {
                // Update status to 'completed'
                $updateQuery = "UPDATE queue SET status = ?, updated_at = ? WHERE id = ?";
                $params = ['completed',date("Y-m-d H:i:s"),intval($row['id'])];
                executeQuery($updateQuery, $params);
                $updated++;
            }
        }
    }
    $stmt->close();

    echo json_encode(['success' => true, 'updated_records' => $updated]);
    exit;
}else{
     http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
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
