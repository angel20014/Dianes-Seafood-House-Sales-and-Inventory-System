<?php
header('Content-Type: application/json');
include 'db.php'; // Include your database connection file

// Retrieve POST data
$saleDate = $_POST['saleDate'] ?? '';
$product_name = $_POST['product_name'] ?? '';
$quantity = $_POST['quantity'] ?? 0;
$price = $_POST['price'] ?? 0.0;
$total = $_POST['total'] ?? 0.0;
$orderType = $_POST['orderType'] ?? '';
$customerType = $_POST['customerType'] ?? '';

// Validate the saleDate and ensure it's not empty
if (empty($saleDate)) {
    echo json_encode(['success' => false, 'message' => 'Sale date is empty']);
    exit();
}

// Convert saleDate to correct format if necessary
$formattedSaleDate = date('Y-m-d', strtotime($saleDate));

// Prepare and execute SQL query
$sql = "INSERT INTO sales (saleDate, product_name, quantity, price, total, orderType, customerType) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Check if the statement was prepared correctly
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Error preparing the SQL statement: ' . $conn->error]);
    exit();
}

// Bind the parameters
$stmt->bind_param('ssiddss', $formattedSaleDate, $product_name, $quantity, $price, $total, $orderType, $customerType);

// Execute the statement and check if it was successful
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Sale recorded successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error recording sale: ' . $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
