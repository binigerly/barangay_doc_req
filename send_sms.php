<?php
session_start();
include 'config.php'; // Database connection

// Function to send SMS notification
function sendSMSNotification($phone, $message) {
    $api_url = "https://sms-gateway-provider.com/api/send";
    $api_key = "YOUR_API_KEY";
    
    $data = [
        'api_key' => $api_key,
        'number' => $phone,
        'message' => $message
    ];
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// Call this function when a request is approved
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    
    if ($action == "approve") {
        $update_query = "UPDATE document_requests SET status = 'Approved' WHERE id = ?";
        
        // Fetch user details for SMS
        $user_query = "SELECT users.phone, users.name FROM document_requests INNER JOIN users ON document_requests.user_id = users.id WHERE document_requests.id = ?";
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        
        if ($user = $user_result->fetch_assoc()) {
            $phone = $user['phone'];
            $message = "Hello " . $user['name'] . ", your document request has been approved. Please visit the barangay office to claim it.";
            sendSMSNotification($phone, $message);
        }
    }
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit();
}
?>
