<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require 'db.php'; // Adjust the path as needed

// Get POST data
$productId = isset($_POST['productId']) ? $_POST['productId'] : null;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

$response = ['success' => false, 'message' => ''];

// Validate input
if ($productId && $quantity > 0) {
    // Prepare and execute the query to update stock
    $stmt = $db->prepare("UPDATE products SET current_stock = current_stock + ? WHERE product_id = ?");
    
    if ($stmt) {
        $stmt->bind_param("is", $quantity, $productId);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Stock updated successfully.';
        } else {
            $response['message'] = 'Execution error: ' . $stmt->error;
        }        
        $stmt->close();
    } else {
        $response['message'] = 'Prepared statement error: ' . $stmt->error;
    }
} else {
    $response['message'] = 'Invalid product ID or quantity.';
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$db->close();
?>
