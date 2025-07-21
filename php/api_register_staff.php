<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once '../vendor/autoload.php'; // Include the JWT library (e.g., Firebase JWT library)
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// Secret key for JWT (change this to a long and secure random string)
$secretKey = 'pamzey@7877881825419880518';
$algorithm = "HS256";
$input = json_decode(file_get_contents('php://input'), true);

// Only allow POST requests
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($input['role']))) {
    
    // Get JSON input
// Validate required fields
$required = ['firstName', 'lastName', 'email', 'password', 'role'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}

// Check if email already exists
$stmt = $db->prepare("SELECT COUNT(*) FROM staff WHERE email = ?");
$stmt->bind_param("s", $input['email']);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already registered']);
    exit;
}

// Hash the password
$hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

// Insert staff
$query = "INSERT INTO staff (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
$params = [
    $input['firstName'],
    $input['lastName'],
    $input['email'],
    $hashedPassword,
    $input['role']
];

$insertId = executeQuery($query, $params, true);

if ($insertId) {
    echo json_encode(['success' => true, 'staff_id' => $insertId]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed']);
}

}
elseif(($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['email'])&&$_POST['password'])
{
		
	         $user_email =  mysqli_real_escape_string($db,$_POST['email']);
        $enteredPassword =  mysqli_real_escape_string($db,$_POST['password']);

     // Query the database to get the hashed password for the user    
        $stmt = $db->prepare("SELECT * FROM staff WHERE email = ?");
        $stmt->bind_param('s', $user_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $patient = $result->fetch_assoc();
        $stmt->close();

       if ($patient) {

            // Use password_verify to compare the entered password with the stored hash
           if (password_verify($enteredPassword, $patient['password'])) {
                // Password is correct, generate a JWT token
              $jwt = generateJWT($patient['id'],$algorithm);
              http_response_code(201);
              echo json_encode(['token' => $jwt,'code' => 201,"data" => $patient]);
              exit;
            } else {
                     // Password is incorrect
                     //http_response_code(401);
                     echo json_encode(['message' => 'Password is incorrect','code' => 401]);
                     exit;
            }
        } else {
          // User not found
          //http_response_code(404);
          echo json_encode(['message' => 'User not found','code' => 404]);
          exit;
        }

       

}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

      $headers = getallheaders();
      $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if ($authHeader) {
        $token = str_replace('Peer','', $authHeader);
        $user_id = verifyJWT($token,$algorithm);
        if ($user_id !== null) {
            // User is authenticated, perform actions here
            echo json_encode(['message' => 'Authenticated','user_id' => $user_id]);
            exit;
        }          
    }

    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}
else{
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Function to generate a JWT token
function generateJWT($user_id,$algorithm) {
    global $secretKey;
    $token = [
        'user_id' => $user_id,
        'exp' => time() + 3600, // Token expiration time (e.g., 1 hour)
    ];
    return JWT::encode($token, $secretKey, $algorithm);
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


?>