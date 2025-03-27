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

if (!$stmt) {
    file_put_contents('debug.log', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    file_put_contents('debug.log', "Query execution failed: " . $stmt->error . "\n", FILE_APPEND);
    die("Query execution failed: " . $stmt->error);
}
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
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-denied {
            color: red;
            font-weight: bold;
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
        
        <p>Your Recent Requests</p>
        <div class="table-container">
            <table>
                <tr>
                    <th>DOCUMENT</th>
                    <th>PURPOSE</th>
                    <th>DATE REQUESTED</th>
                    <th>STATUS</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                        <td><?php echo date('F j, Y', strtotime($row['request_date'])); ?></td>
                        <td class="status-<?php echo strtolower($row['status']); ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>
