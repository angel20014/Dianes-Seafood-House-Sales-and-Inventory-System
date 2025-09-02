<?php
include('db.php');

// Query to fetch stock information
$query = "
    SELECT s.stock_id, s.product_id, s.stock_in, s.stock_out, (s.stock_in - s.stock_out) AS current_stock
    FROM stock s
    LEFT JOIN products p ON s.product_id = p.product_id
";

// Execute the query
$result = $conn->query($query);

if ($result) {
    // Fetch all results as an associative array
    $stockData = $result->fetch_all(MYSQLI_ASSOC);

    // Set content type to JSON and output the result
    header('Content-Type: application/json');
    echo json_encode(['stock' => $stockData]);
} else {
    // Handle query error
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
}

// Close the database connection
$conn->close();
?>
