<?php

// namespace main;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Dotenv\Dotenv;

session_start();
require_once './vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable('/var/www');
$dotenv->load();

// Database Connection (Using .env variable)
$db_password = $_ENV['DB_PASSWORD_ROOT'] ?? '';
$db = new mysqli("localhost", "root", $db_password, "ambitie_game");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $db->real_escape_string($_POST['username']);
    $password = $_POST['password']; // Use proper password hashing in production

    $result = $db->query("SELECT * FROM mysql.user WHERE user='$username' LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $_SESSION['db_logged_in'] = true;
    } else {
        $error = "Invalid username or password.";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: filter.php");
    exit();
}

// Execute Query
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = $_POST['query'];
    $query_result = ['columns' => [], 'rows' => []];

    if ($result = $db->query($query)) {
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            $query_result['columns'][] = $field->name;
        }

        while ($row = $result->fetch_assoc()) {
            $query_result['rows'][] = $row;
        }
    } else {
        $query_error = "Error: " . $db->error;
        echo "Query error: $query_error\n";
    }
}

// Initialize Twig
$loader = new FilesystemLoader('./templates');
$twig = new Environment($loader, [
    'allow_includes' => true,
]);

// Render Template
echo $twig->render('filter.twig', [
    'session' => $_SESSION,
    'error' => $error ?? null,
    'query_result' => $query_result ?? null,
    'query_error' => $query_error ?? null,
]);

?>
