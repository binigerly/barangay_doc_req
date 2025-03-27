<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}

// Fetch user name
$user_name = "";
$user_query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

// Fetch user requests
$query = "SELECT document_type, purpose, status, request_date FROM document_requests WHERE user_id = ? ORDER BY request_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Barangay Online Documentation</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            padding: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            background: white;
            border-radius: 10px;
            margin: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .request-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin-top: 20px;
        }
        .request-form h3 {
            margin-bottom: 15px;
            color: #444;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #2980b9;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            border-radius: 4px;
            font-size: 16px;
        }
        button:hover {
            background: #1f6690;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>User Panel</h2>
        <ul>
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="request.php">📄 Request a Document</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <div class="request-form">
            <h3>Request a Document</h3>
            <form action="submit_request.php" method="POST">
                <label for="document">Select Document:</label>
                <select name="document" id="document" required>
                    <option value="Barangay Clearance">Barangay Clearance</option>
                    <option value="Certificate of Residency">Certificate of Residency</option>
                    <option value="Certificate of Indigency">Certificate of Indigency</option>
                    <option value="Certificate of Good Moral Character">Certificate of Good Moral Character</option>
                    <option value="Barangay Blotter Report">Barangay Blotter Report</option>
                    <option value="Barangay Protection Order">Barangay Protection Order</option>
                    <option value="Barangay Business Permit">Barangay Business Permit</option>
                </select>
                <br>
                <label for="purpose">Purpose:</label>
                <input type="text" name="purpose" id="purpose" placeholder="Enter purpose..." required>
                <br>
                <button type="submit">Submit Request</button>
            </form>
        </div>
    </div>
</body>
</html>
