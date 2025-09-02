<?php
include 'db.php';

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    
    // Fetch stock details for the specific product
    $stockSql = "
    SELECT p.product_id, p.product_name,
           COALESCE(SUM(s.stock_in), 0) AS total_stock_in,
           COALESCE(SUM(s.stock_out), 0) AS total_stock_out,
           COALESCE(SUM(sa.quantity), 0) AS total_sold,
           (COALESCE(SUM(s.stock_in), 0) - (COALESCE(SUM(s.stock_out), 0) + COALESCE(SUM(sa.quantity), 0))) AS current_stock
    FROM products p
    LEFT JOIN stock s ON p.product_id = s.product_id
    LEFT JOIN sales sa ON p.product_id = sa.product_id
    WHERE p.product_id = $product_id
    GROUP BY p.product_id
    ";

    $stockResult = $conn->query($stockSql);
    $stockDetails = $stockResult->fetch_assoc();

    // Return as JSON
    echo json_encode($stockDetails);
}

$conn->close();
?>
