<?php
include('db.php');

$query = $_GET['query'] ?? '';

if (!empty($query)) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_name LIKE ?");
    $searchTerm = "%$query%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode($products);
}
?>
