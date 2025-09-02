<?php
include 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id']);
    $stockIn = intval($_POST['stock_in']);

    // Update the last stock entry
    $updateQuery = "
        UPDATE stock 
        SET stock_in = $stockIn 
        WHERE product_id = $productId 
        ORDER BY stock_date DESC 
        LIMIT 1
    ";

    if ($conn->query($updateQuery) === TRUE) {
        echo "Stock updated successfully.";
    } else {
        echo "Error updating stock: " . $conn->error;
    }
}

$conn->close();
?>
