<?php
// updateStock.php
header('Content-Type: application/json');

// Include database connection
include 'db.php';

// Get the JSON data from the request body
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['product_name']) && isset($data['stock_in']) && isset($data['current_stock'])) {
    $product_name = $conn->real_escape_string($data['product_name']);
    $stock_in = (int)$data['stock_in'];
    $current_stock = (int)$data['current_stock'];
    
    // Calculate new current stock
    $new_current_stock = $current_stock + $stock_in;

    // Prepare the SQL query to update stock
    $sql = "UPDATE products SET current_stock = $new_current_stock WHERE product_name = '$product_name'";

    if ($conn->query($sql) === TRUE) {
        // Respond with success
        echo json_encode(['success' => true]);
    } else {
        // Respond with an error
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    // Respond with an error if required fields are missing
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Close the database connection
$conn->close();
?>
