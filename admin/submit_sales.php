<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sales_data = json_decode(file_get_contents('php://input'), true);

    $success = true;

    foreach ($sales_data as $sale) {
        $product_id = $sale['product_id'];
        $quantity = $sale['quantity'];
        $total_price = $sale['total_price'];

        $query = "INSERT INTO sales (product_id, quantity, total_price) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iid', $product_id, $quantity, $total_price);

        if (!$stmt->execute()) {
            $success = false;
            break;
        }
    }

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Sales recorded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to record sale']);
    }
}
?>
