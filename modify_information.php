<?php
require 'connect.php';
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$message = '';

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone($phone) {
    return preg_match('/^(\+(\d{1,3})\s?)?(\d{10,12})$|^0(\d{9,11})$/', $phone);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['new_username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check that at least one field is non-empty
    if (empty($new_username) && empty($email) && empty($phone) && empty($password)) {
        $message = "Please fill in at least one field to update.";
    } else {
        if (!isValidEmail($email) && !empty($email)) {
            $message = "Invalid email format. Please enter a valid email address.";
        } else if (!isValidPhone($phone) && !empty($phone)) {
            $message = "Invalid phone number format. Please enter a valid phone number.";
        } else {
            // Hash password if provided
            if (!empty($password)) {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }

            $current_username = $_SESSION['username'];
            $update_successful = updateUserInfo($current_username, $new_username, $email, $phone, $password);
            if ($update_successful) {
                $_SESSION['username'] = $new_username ?: $_SESSION['username']; // Update session username if changed
                $message = 'Information updated successfully.';
            } else {
                $message = 'No changes were made.';
            }
        }
    }
}

function updateUserInfo($current_username, $new_username, $email, $phone, $password) {
    global $conn;

    // Build SQL query and parameters dynamically based on non-empty fields
    $sql = "UPDATE user_info SET ";
    $types = '';
    $params = [];

    if (!empty($new_username)) {
        $sql .= "username = ?, ";
        $types .= 's';
        $params[] = $new_username;
    }
    if (!empty($email)) {
        $sql .= "email = ?, ";
        $types .= 's';
        $params[] = $email;
    }
    if (!empty($phone)) {
        $sql .= "phone = ?, ";
        $types .= 's';
        $params[] = $phone;
    }
    if (!empty($password)) {
        $sql .= "password = ?, ";
        $types .= 's';
        $params[] = $password;
    }

    // Remove trailing comma and add WHERE clause
    $sql = rtrim($sql, ', ') . " WHERE username = ?";
    $types .= 's';
    $params[] = $current_username;

    // Run query
    $stmt = runQuery($sql, $types, ...$params);
    return $stmt && $stmt->affected_rows > 0;
}

?>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./css/style.css">
    <title>Modify information</title>
</head>

<form method="post">
    New Username: <input type="text" name="new_username" value="<?= htmlspecialchars($_SESSION['username']) ?>"><br>
    Email: <input type="text" name="email"><br>
    Phone: <input type="text" name="phone"><br>
    Password: <input type="password" name="password"><br>
    <input type="submit" value="Update Information">
</form>
<link rel="stylesheet" href="./css/style.css">
<div><?= htmlspecialchars($message) ?></div>
