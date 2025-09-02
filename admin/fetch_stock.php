<?php
// Include database connection
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required POST variables are set
    if (!isset($_POST['product_id']) || !isset($_POST['stock_in']) || !isset($_POST['stock_out'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    $product_id = $_POST['product_id'];
    $stock_in = $_POST['stock_in'];
    $stock_out = $_POST['stock_out']; // Quantity sold

    // Validate inputs
    if (!is_numeric($product_id) || !is_numeric($stock_in) || !is_numeric($stock_out)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    // Calculate current stock based on previous entries
    $current_stock_query = "
        SELECT SUM(stock_in) AS total_in, SUM(stock_out) AS total_out
        FROM stock
        WHERE product_id = ?";
    
    $current_stmt = $conn->prepare($current_stock_query);
    $current_stmt->bind_param('i', $product_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $current_row = $current_result->fetch_assoc();

    // Calculate total stock in and out
    $total_in = $current_row['total_in'] ?? 0; // Defaults to 0 if NULL
    $total_out = $current_row['total_out'] ?? 0; // Defaults to 0 if NULL

    // New current stock calculation
    $new_current_stock = $total_in - $total_out + $stock_in - $stock_out;

    // Prevent negative stock
    if ($new_current_stock < 0) {
        $new_current_stock = 0;
    }

    // Insert new stock entry
    $sql = "INSERT INTO stock (product_id, stock_in, stock_out, current_stock, date) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiis', $product_id, $stock_in, $stock_out, $new_current_stock);

    if ($stmt->execute()) {
        // Update products table with the new current stock
        $update_product_sql = "UPDATE products SET current_stock = ? WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_product_sql);
        $update_stmt->bind_param('ii', $new_current_stock, $product_id);
        $update_stmt->execute();
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add stock']);
    }

    // Close statements and connection
    $stmt->close();
    $current_stmt->close();
    $update_stmt->close();
    $conn->close();
}
?>
