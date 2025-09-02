<?php
header('Content-Type: application/json');


// Query to get all product IDs
$sql = "SELECT product_id FROM products";
$result = $mysqli->query($sql);

$productData = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productData[] = $row;
    }
}

echo json_encode($productData);

$mysqli->close();
?>
