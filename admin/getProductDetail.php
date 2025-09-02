<?php
include 'db.php';

if (isset($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);

    // Fetch the product details for the given product_id
    $sql = "
    SELECT p.product_name, p.current_stock,
           COALESCE(SUM(s.stock_in), 0) AS total_stock_in,
           COALESCE(SUM(s.stock_out), 0) AS total_stock_out
    FROM products p
    LEFT JOIN stock s ON p.product_id = s.product_id
    WHERE p.product_id = ?
    GROUP BY p.product_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => [
                'product_name' => $product['product_name'],
                'stock_in' => $product['total_stock_in'],
                'stock_out' => $product['total_stock_out'],
                'current_stock' => $product['current_stock']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Product ID is missing.']);
}
?>
