<?php
include('db.php');

// Fetch product data
$query = "SELECT product_name, product_code, current_stock FROM products";
$result = $conn->query($query);

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);

$conn->close();
?>
