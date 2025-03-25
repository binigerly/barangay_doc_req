<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$document = $_POST['document'] ?? null;
$purpose = $_POST['purpose'] ?? null;

if (!$document || !$purpose) {
    die("Missing required fields.");
}

// Insert the request into the database
$query = "INSERT INTO document_requests (user_id, document_type, purpose, status, request_date) VALUES (?, ?, ?, 'Pending', NOW())";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("iss", $user_id, $document, $purpose);

if ($stmt->execute()) {
    // Redirect to the user dashboard
    header("Location: dashboard.php");
    exit();
} else {
    die("Database error: " . $stmt->error);
}
?>