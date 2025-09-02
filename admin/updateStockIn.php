<?php
include 'db.php';

if (isset($_POST['product_id']) && isset($_POST['stock_in'])) {
    $productId = intval($_POST['product_id']);
    $stockIn = intval($_POST['stock_in']);

    // Update the stock_in value in the stock table
    $updateSql = "UPDATE stock SET stock_in = ? WHERE product_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ii", $stockIn, $productId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Stock In updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
    }

    $stmt->close();
}
$conn->close();
?>
