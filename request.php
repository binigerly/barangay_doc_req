<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = $_POST['document_type'];
    $query = "INSERT INTO document_requests (user_id, document_type, status, request_date) VALUES (?, ?, 'Pending', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $document_type);
    $stmt->execute();
    header("Location: request.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Request a Document</h2>
    <?php if (isset($_GET['success'])) echo "<p style='color: green;'>Request submitted successfully!</p>"; ?>
    <form method="POST" action="">
        <label>Select Document:</label>
        <select name="document_type" required>
            <option value="Barangay Clearance">Barangay Clearance</option>
            <option value="Indigency Certificate">Indigency Certificate</option>
            <option value="Certificate of Residency">Certificate of Residency</option>
        </select>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
