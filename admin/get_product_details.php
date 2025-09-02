<?php
include('db.php'); // Include your database connection file

// Get product_id from POST request
$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];

// Prepare SQL query
$sql = "SELECT product_id, product_name, expiration_date FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$response = array('success' => false);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $response = array(
        'success' => true,
        'product' => $product
    );
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
