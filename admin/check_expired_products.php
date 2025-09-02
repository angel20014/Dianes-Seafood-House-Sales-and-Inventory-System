<?php
include('db.php');

// Step 1: Insert expired products into the expired_products table
$expiredSql = "
    INSERT INTO expired_products (product_id, product_name, category, expiration_date, expired_on, current_stock)
    SELECT product_id, product_name, category, expiration_date, CURDATE() as expired_on, current_stock
    FROM products
    WHERE expiration_date <= CURDATE()
";

// Execute the insertion query
if ($conn->query($expiredSql) === TRUE) {
    echo "Expired products moved successfully.";
} else {
    echo "Error moving expired products: " . $conn->error;
}

// Step 2: Remove expired products from the products table
$deleteExpiredSql = "DELETE FROM products WHERE expiration_date <= CURDATE()";

// Execute the deletion query
if ($conn->query($deleteExpiredSql) === TRUE) {
    echo "Expired products removed successfully.";
} else {
    echo "Error removing expired products: " . $conn->error;
}

$conn->close();
?>
