<?php
include('db.php');

// Query to get expired products history
$sql = "SELECT product_id, product_name, TIMESTAMPDIFF(DAY, expiration_date, NOW()) AS days_ago 
        FROM products 
        WHERE expiration_date < CURDATE() 
        ORDER BY expiration_date DESC";

$result = $conn->query($sql);

$response = ['success' => true, 'expiredProductsHistory' => []];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $response['expiredProductsHistory'][] = $row;
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Query failed: ' . $conn->error;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
