<?php

// delete_stock.php
include 'db.php';

if (isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    
    // Prepare and execute deletion
    $stmt = $conn->prepare("DELETE FROM stock WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    
    if ($stmt->execute()) {
        echo "Stock item deleted successfully.";
    } else {
        echo "Error deleting stock item: " . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>