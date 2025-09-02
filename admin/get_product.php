<?php
include('db.php');

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $query = "SELECT * FROM products WHERE product_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false]);
    }
    
    $conn->close();
} else {
    echo json_encode(['success' => false]);
}
?>
