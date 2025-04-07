<?php
require 'connect.php';  // Include your database connection script

// Initialize a message variable
$message = '';

// Check if the token and email are provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare a statement to retrieve the token from the database
    $stmt = $conn->prepare("SELECT email, expires FROM password_recovery WHERE token = ? AND expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if the token is valid
    if ($user) {
        // The token is valid and not expired
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_POST['confirm_password'])) {
            // Check if passwords match
            if ($_POST['password'] === $_POST['confirm_password']) {
                // Hash the new password
                $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                // Update the user's password
                $update = $conn->prepare("UPDATE user_info SET password = ? WHERE email = ?");
                $update->bind_param("ss", $new_password, $user['email']);
                $update->execute();

                // Delete the token from the database to prevent reuse
                $delete = $conn->prepare("DELETE FROM password_recovery WHERE token = ?");
                $delete->bind_param("s", $token);
                $delete->execute();

                $message = "Your password has been reset successfully. <a href='login.php'>Login here</a>.";
            } else {
                $message = "Passwords do not match. Please try again.";
            }
        }
    } else {
        $message = "This link is invalid or has expired.";
    }
} else {
    $message = "No reset token provided.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body>
    <h1>Reset Your Password</h1>
    <p><?= $message ?></p>
    <?php if (isset($user) && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
    <form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="post">
        New Password: <input type="password" name="password" required><br>
        Confirm Password: <input type="password" name="confirm_password" required><br>
        <input type="submit" value="Reset Password">
    </form>
    <?php endif; ?>
</body>
</html>
