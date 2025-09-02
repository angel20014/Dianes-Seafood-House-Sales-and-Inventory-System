<?php
include('db.php');

$product_id = $_POST['product_id'] ?? '';

if ($product_id) {
    $sql = "SELECT stock FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $product_id);
    $stmt->execute();
    $stmt->bind_result($stock);
    if ($stmt->fetch()) {
        echo json_encode(['success' => true, 'stock' => $stock]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
    $stmt->close();
}

$conn->close();
?>
