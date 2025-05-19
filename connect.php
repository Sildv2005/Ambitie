<?php

require_once './vendor/autoload.php';

$host = 'localhost';
$db = 'ambitie_game';
$user = 'ambitie_game';
$pass = 'ambitie_game';
$charset = 'utf8mb4';

// Debug output - Uncomment if needed for debugging
/*
var_dump($pass);

if (!$pass) {
    echo "Error: Databse login credentials invalid. Make sure the username and password are correct.";
    exit;
}

echo "Database password loaded successfully: " . htmlspecialchars($pass, ENT_QUOTES, 'UTF-8');
*/

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Success message - Uncomment if needed for debugging
// echo "Connected successfully!";

$conn->set_charset($charset);

function runQuery($sql, $types = null, ...$params) {
    global $conn;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("MySQL prepare error: " . $conn->error);
        return false;
    }

    if ($types && !empty($params)) {
        if (!$stmt->bind_param($types, ...$params)) {
            error_log("MySQL bind_param error: " . $stmt->error);
            return false;
        }
    }

    if (!$stmt->execute()) {
        error_log("MySQL execute error: " . $stmt->error);
        return false;
    }

    return $stmt;
}
