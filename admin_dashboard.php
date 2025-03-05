<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch all pending document requests
$query = "SELECT dr.id, u.name, dr.document_type, dr.status, dr.request_date FROM document_requests dr JOIN users u ON dr.user_id = u.id ORDER BY dr.request_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    
    if ($action == "approve") {
        $update_query = "UPDATE document_requests SET status = 'Approved' WHERE id = ?";
    } elseif ($action == "deny") {
        $update_query = "UPDATE document_requests SET status = 'Denied' WHERE id = ?";
    }
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Online Documentation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="register.php">Register User/Admin</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Pending Document Requests</h2>
        <table border="1">
            <tr>
                <th>Request ID</th>
                <th>User Name</th>
                <th>Document Type</th>
                <th>Status</th>
                <th>Request Date</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['document_type']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['request_date']; ?></td>
                    <td>
                        <?php if ($row['status'] == 'Pending') { ?>
                            <form method="POST" action="admin_dashboard.php">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="action" value="approve">Approve</button>
                                <button type="submit" name="action" value="deny">Deny</button>
                            </form>
                        <?php } else { echo "No action needed"; } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </main>
</body>
</html>
