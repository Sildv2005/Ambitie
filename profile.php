<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./css/style.css">
    <title>User Profile</title>
</head>
<?php
require 'connect.php';
require './vendor/autoload.php';
use RobThree\Auth\TwoFactorAuth;

session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo "You must be logged in to view this page.";
    exit;
}

// Fetch user details and MFA status
$userId = $_SESSION['user_id'] ?? 0;
$stmt = $conn->prepare("SELECT username, email, mfa_secret FROM user_info WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

// Handle MFA removal request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_mfa']) && !empty($_POST['mfa_code'])) {
    $tfa = new TwoFactorAuth('PPP4');
    if ($tfa->verifyCode($user['mfa_secret'], $_POST['mfa_code'], 2)) {  // 2 = window for code verification
        // Code is correct, remove MFA
        $stmt = $conn->prepare("UPDATE user_info SET mfa_secret = NULL WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user['mfa_secret'] = NULL;  // Update current session data
        echo "<p>MFA has been successfully removed.</p>";
    } else {
        echo "<p>Incorrect MFA code.</p>";
    }
}
?>
<body>
    <h1>User Profile</h1>
    <p>Welcome, <?= htmlspecialchars($user['username']) ?></p>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>

    <?php if (empty($user['mfa_secret'])): ?>
        <!-- No MFA setup, provide setup button -->
        <p><a href="setup_mfa.php">Setup Multi-factor Authentication</a></p>
    <?php else: ?>
        <!-- MFA is set up, provide removal form -->
        <form method="post">
            <label for="mfa_code">Enter your MFA code to remove MFA:</label>
            <input type="text" id="mfa_code" name="mfa_code" required>
            <button type="submit" name="remove_mfa">Remove MFA</button>
        </form>
    <?php endif; ?>
    
    <p><a href='modify_information.php'>Modify Information</a></p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>
