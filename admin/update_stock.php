<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $stock_in = intval($_POST['stock_in']);

    // Update stock in the database
    $updateSql = "UPDATE stock SET stock_in = $stock_in WHERE product_id = $product_id";
    if ($conn->query($updateSql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}

$conn->close();
?>
