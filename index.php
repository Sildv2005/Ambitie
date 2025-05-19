<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./css/style.css">
    <title>Index</title>
</head>
<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo "You are not logged in. <br>";
    echo "You can <a href='login.php'>login</a> here.";
} else {
    echo "Welcome to the Home Page! <br>";
    echo "You can view your <a href='profile.php'>profile</a> Here";
}
echo "<br>You can view the <a href='leaderboard.php'>leaderboard</a> here.";
?>