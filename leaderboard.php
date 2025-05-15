<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once './vendor/autoload.php';

session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Database Connection
$db = new mysqli("localhost", "ambitie_game", "ambitie_game", "ambitie_game");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: leaderboard.php");
    exit();
}

// Execute default highscores query with optional filter
$filter = $_GET['filter'] ?? '';
$query_result = ['columns' => [], 'rows' => []];

$sql = "SELECT * FROM highscores";
if (!empty($filter)) {
    $filter_safe = $db->real_escape_string($filter);
    $sql .= " WHERE player_name = '$filter_safe'";
}

if ($result = $db->query($sql)) {
    $fields = $result->fetch_fields();
    foreach ($fields as $field) {
        $query_result['columns'][] = $field->name;
    }

    while ($row = $result->fetch_assoc()) {
        $query_result['rows'][] = $row;
    }
} else {
    $query_error = "Error: " . $db->error;
}

// Initialize Twig
$loader = new FilesystemLoader('./templates');
$twig = new Environment($loader, [
    'allow_includes' => true,
]);

// Render Template
echo $twig->render('leaderboard.twig', [
    'session' => $_SESSION,
    'error' => $error ?? null,
    'query_result' => $query_result ?? null,
    'query_error' => $query_error ?? null,
    'filter' => htmlspecialchars($filter ?? '', ENT_QUOTES, 'UTF-8'),
]);

?>
