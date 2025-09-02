<?php
// stock.php or the relevant PHP file handling the stock display
include('db.php');

// Query to fetch products along with their stock details
$query = "
SELECT 
    p.product_id, 
    p.product_code, 
    p.product_name, 
    p.category,
    p.unit, 
    p.price, 
    p.expiration_date,
    COALESCE(SUM(s.stock_in), 0) AS total_stock_in, 
    COALESCE(SUM(so.quantity), 0) AS total_stock_out,
    COALESCE(SUM(s.stock_in), 0) - COALESCE(SUM(so.quantity), 0) AS current_stock
FROM 
    products p
LEFT JOIN 
    stock s ON p.product_id = s.product_id
LEFT JOIN 
    sales so ON p.product_id = so.product_id
GROUP BY 
    p.product_id, p.product_code, p.product_name, p.category, p.unit, p.price, p.expiration_date";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(['success' => true, 'products' => $products]);
} else {
    echo json_encode(['success' => false, 'message' => 'No products found.']);
}

$conn->close();
?>
