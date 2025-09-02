<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    $productId = $data['id'] ?? null;

    if ($productId) {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error executing query.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'No product ID provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
