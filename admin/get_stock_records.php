<?php
include('db.php');

// Query to fetch products along with their stock IDs
$query = "
    SELECT p.product_id, p.product_name, s.stock_id
    FROM products p
    LEFT JOIN stock s ON p.product_id = s.product_id
";

// Execute the query
$result = $conn->query($query);

if ($result) {
    // Fetch all results as an associative array
    $products = $result->fetch_all(MYSQLI_ASSOC);

    // Set content type to JSON and output the result
    header('Content-Type: application/json');
    echo json_encode(['products' => $products]);
} else {
    // Handle query error
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
}

// Close the database connection
$conn->close();
?>
