<?php
// Include database connection
include 'db.php';

// Get product_id from query parameters
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Prepare SQL statement to fetch product details
$sql = "SELECT p.product_id, p.product_name, 
               SUM(s.stock_in) AS stock_in, 
               SUM(s.stock_out) AS stock_out, 
               (SUM(s.stock_in) - SUM(s.stock_out)) AS current_stock 
        FROM products AS p
        LEFT JOIN stock AS s ON p.product_id = s.product_id 
        WHERE p.product_id = ?
        GROUP BY p.product_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if product exists
if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $response = [
        'success' => true,
        'data' => [
            'product_id' => $product['product_id'],
            'product_name' => $product['product_name'],
            'stock_in' => $product['stock_in'] ? $product['stock_in'] : 0,
            'stock_out' => $product['stock_out'] ? $product['stock_out'] : 0,
            'current_stock' => $product['current_stock'] ? $product['current_stock'] : 0,
        ],
    ];
} else {
    $response = ['success' => false, 'message' => 'Product not found.'];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Close connection
$stmt->close();
$conn->close();
?>
