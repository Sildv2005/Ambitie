<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once './vendor/autoload.php';

session_start();

// Database Connection
$db = new mysqli("localhost", "ambitie_game", "ambitie_game", "ambitie_game");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /");
    exit();
}

// Handle Time Removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_time'], $_POST['player_id']) && !empty($_SESSION['user_logged_in'])) {
    $id = $db->real_escape_string($_POST['player_id']);
    $filter = $_GET['filter'] ?? '';
    $page = $_GET['page'] ?? 1;
    $db->query("DELETE FROM highscores WHERE id = '$id'");
    header("Location: /?filter=" . urlencode($filter) . "&page=" . (int)$page);
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? '';
$filter_safe = $db->real_escape_string($filter);

// Pagination settings
$per_page = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count total rows for pagination
$count_sql = "SELECT COUNT(*) as count FROM highscores";
if (!empty($filter)) {
    $count_sql .= " WHERE player_name = '$filter_safe'";
}
$total_count_result = $db->query($count_sql);
$total_rows = ($total_count_result && $row = $total_count_result->fetch_assoc()) ? (int)$row['count'] : 0;
$total_pages = (int) ceil($total_rows / $per_page);

// Build query
$sql = "SELECT * FROM highscores";
if (!empty($filter)) {
    $sql .= " WHERE player_name = '$filter_safe'";
}
$sql .= " LIMIT $per_page OFFSET $offset";

// Run query
$query_result = ['columns' => [], 'rows' => []];
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
    'title' => 'Leaderboard',
    'query_result' => $query_result ?? null,
    'query_error' => $query_error ?? null,
    'filter' => htmlspecialchars($filter ?? '', ENT_QUOTES, 'UTF-8'),
    'pagination' => [
        'page' => $page,
        'total_pages' => $total_pages,
    ]
]);

?>
