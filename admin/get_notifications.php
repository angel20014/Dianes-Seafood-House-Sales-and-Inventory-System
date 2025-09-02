<?php
include('db.php'); // Include your database connection file

// Example query to fetch notifications
$sql = "SELECT notification_id, message, link FROM notifications WHERE user_id = ?"; // Adjust query as needed
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId); // Replace $userId with the actual user ID
$stmt->execute();
$result = $stmt->get_result();

$notifications = array();

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode(array('notifications' => $notifications));

// Close connection
$stmt->close();
$conn->close();
?>
