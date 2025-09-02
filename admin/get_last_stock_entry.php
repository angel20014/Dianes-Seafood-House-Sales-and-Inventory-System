<?php
include 'db.php'; // Ensure you include your database connection

if (isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);

    // Query to get the last stock entry
    $query = "
        SELECT stock_id, product_id, stock_in 
        FROM stock 
        WHERE product_id = $productId 
        ORDER BY stock_date DESC 
        LIMIT 1
    ";

    $result = $conn->query($query);
    $row = $result->fetch_assoc();

    if ($row) {
        // Prepare response
        echo json_encode($row);
    } else {
        echo json_encode(null);
    }
} else {
    echo json_encode(['error' => 'Invalid product ID']);
}

$conn->close();
?>
