<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Home - Barangay Online Documentation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Welcome to Barangay Online Documentation</h1>
    <nav>
        <ul>
            <li><a href="request.php">Request a Document</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</body>
</html>
