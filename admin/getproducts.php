<?php
header('Content-Type: application/json');

// Include your database connection file
include('db.php');

// SQL query to fetch products
$sql = "SELECT product_id, product_name, expiration_date FROM products";
$result = $conn->query($sql);

$products = array();

if ($result->num_rows > 0) {
    // Fetch data from each row and add to $products array
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Close connection
$conn->close();

// Output the data in JSON format
echo json_encode($products);
?>
