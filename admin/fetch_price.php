<?php
include 'db.php';

if (isset($_GET['product_name'])) {
    $productName = $_GET['product_name'];

    $stmt = $conn->prepare("SELECT price FROM products WHERE product_name = ?");
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();
    $stmt->close();

    if ($price) {
        echo json_encode(['success' => true, 'price' => $price]);
    } else {
        echo json_encode(['success' => false, 'price' => 0]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No product name provided']);
}
?>
