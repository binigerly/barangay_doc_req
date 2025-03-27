<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch admin name
$admin_name = "";
$admin_query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($admin_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($admin_name);
$stmt->fetch();
$stmt->close();

// Fetch all pending document requests (Now includes `purpose`)
$query = "SELECT dr.id, u.name, dr.document_type, dr.purpose, dr.status, dr.request_date 
          FROM document_requests dr 
          JOIN users u ON dr.user_id = u.id 
          ORDER BY dr.request_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    // Validate action
    if (!in_array($action, ['approve', 'deny'])) {
        die("Invalid action.");
    }

    // Prepare the update query based on the action
    if ($action == "approve") {
        $update_query = "UPDATE document_requests SET status = 'Approved' WHERE id = ?";
    } elseif ($action == "deny") {
        $update_query = "UPDATE document_requests SET status = 'Denied' WHERE id = ?";
    }

    $stmt = $conn->prepare($update_query);

    // Debugging: Log database errors
    if (!$stmt) {
        file_put_contents('debug.log', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $request_id);

    if ($stmt->execute()) {
        // Debugging: Log successful updates
        file_put_contents('debug.log', "Request ID $request_id updated to $action\n", FILE_APPEND);
        header("Location: admin_dashboard.php");
        exit();
    } else {
        file_put_contents('debug.log', "Database error: " . $stmt->error . "\n", FILE_APPEND);
        die("Database error: " . $stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Online Documentation</title>
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
        .btn {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            color: white;
            margin-right: 5px;
        }
        .btn-approve {
            background-color: green;
        }
        .btn-deny {
            background-color: red;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin_dashboard.php">🏠 Dashboard</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>
        <p>Recent Requests</p>
        <div class="table-container">
        <table>
    <tr>
        <th>NAME</th>
        <th>DOCUMENT</th>
        <th>PURPOSE</th>
        <th>DATE REQUESTED</th>
        <th>STATUS</th>
        <th>ACTION</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['document_type']); ?></td>
            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
            <td><?php echo date('F j, Y', strtotime($row['request_date'])); ?></td>
            <td class="status-<?php echo strtolower($row['status']); ?>">
                <?php echo htmlspecialchars($row['status']); ?>
            </td>
            <td>
                <?php if ($row['status'] == 'Pending') { ?>
                    <form method="POST" action="admin_dashboard.php">
                        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                        <button type="submit" name="action" value="deny" class="btn btn-deny">Deny</button>
                    </form>
                <?php } else { echo "No action needed"; } ?>
            </td>
        </tr>
    <?php } ?>
</table>        </div>
    </div>
</body>
</html>
