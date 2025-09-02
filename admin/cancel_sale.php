<?php
include('db.php');

// Retrieve data from POST request
$saleDate = $_POST['saleDate'];
$productName = $_POST['productName'];
$saleId = $_POST['saleId'];

// Cancel sale in the database (assume that 'canceled' is a status you have in your table)
$sql = "DELETE FROM sales WHERE sale_id = ? AND sale_date = ? AND product_name = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $saleId, $saleDate, $productName);

if ($stmt->execute()) {
    $response = ['success' => true, 'message' => 'Sale canceled successfully.'];
} else {
    $response = ['success' => false, 'message' => 'Failed to cancel sale.'];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
