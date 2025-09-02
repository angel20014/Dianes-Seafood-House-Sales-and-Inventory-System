<?php
// Assuming you have included the necessary database connection code here

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$productId = $data['product_id'];
$stockIn = $data['stock_in'];

// Validate input
if (empty($productId) || empty($stockIn)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Prepare SQL query to update stock
$query = "UPDATE stock SET stock_in = ? WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $stockIn, $productId); // Assuming both are integers

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Stock updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error saving stock changes: ' . $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
