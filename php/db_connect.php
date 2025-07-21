<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Global database connection variables
$DB_HOST = "p:localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "db_pqms";

function checkAndReconnectDatabase(&$db) {
    // Check if the connection is still alive
    if (!$db->ping()) {
        // Close the existing connection
        $db->close();
        
        // Attempt to reconnect
        $db = new mysqli($GLOBALS['DB_HOST'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS'], $GLOBALS['DB_NAME']);
        
        if ($db->connect_errno > 0) {
            // Log the error or handle it as needed
            error_log('Unable to reconnect to database [' . $db->connect_error . ']');
            return false;
        }
    }
    return true;
}

$db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if($db->connect_errno > 0){
      die('Unable to connect to database [' . $db->connect_error . ']');  
} 
  
$db->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME`");

mysqli_select_db($db, $DB_NAME);

include_once(__DIR__ . "/migration.php");

if (!checkAndReconnectDatabase($db)) {
    die('Unable to reconnect to database');
}

// Function to safely execute queries
function executeQuery($query, $params = [], $returnInsertId = false) {
    global $db;
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
         if ($returnInsertId) {
            $insertId = $db->insert_id;
            $stmt->close();
            return $insertId;
        }
        return $stmt;
    } catch(PDOException $e) {
       // error_log("Query execution failed: " . $e->getMessage());
        return $e->getMessage();
    }
}

?>