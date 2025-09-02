<?php
header('Content-Type: application/json');

// Fetch expired products
$sql = "
    SELECT id, name, expiration_date
    FROM products
    WHERE expiration_date < CURDATE() AND user_id = ?
";

$stmt = $mysqli->prepare($sql);

$user_id = 1; // Replace with actual user ID or session-based ID
$stmt->bind_param('i', $user_id);

$stmt->execute();
$result = $stmt->get_result();

$expired_products = [];

while ($row = $result->fetch_assoc()) {
    $expired_products[] = $row;
}

// Close connection
$stmt->close();
$mysqli->close();

// Output expired products as JSON
echo json_encode($expired_products);
?>
