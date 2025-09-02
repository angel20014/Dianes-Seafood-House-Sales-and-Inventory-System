<?php
// Database connection
include('db.php'); // Include your database connection file


// Move expired products
$currentDate = date("Y-m-d");

// Step 1: Insert expired products into expired_products table
$sqlMoveExpired = "INSERT INTO expired_products (product_id, product_name, category, expiration_date, current_stock)
                   SELECT id, product_name, category, expiration_date, NOW(), current_stock
                   FROM products
                   WHERE expiration_date < '$currentDate'";

if ($conn->query($sqlMoveExpired) === TRUE) {
    // Step 2: Remove expired products from the products table
    $sqlDeleteExpired = "DELETE FROM products WHERE expiration_date < '$currentDate'";
    
    if ($conn->query($sqlDeleteExpired) === TRUE) {
        echo "Expired products moved and deleted successfully.";
    } else {
        echo "Error deleting expired products: " . $conn->error;
    }
} else {
    echo "Error moving expired products: " . $conn->error;
}

// Optional: Debugging - Check how many expired products were moved
$expiredCount = $conn->affected_rows; // Get the number of rows affected by the last query
if ($expiredCount > 0) {
    echo "Number of expired products moved: " . $expiredCount;
} else {
    echo "No expired products found to move.";
}

$conn->close();
?>