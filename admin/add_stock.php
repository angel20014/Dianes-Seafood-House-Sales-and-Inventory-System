<?php
// Include database connection
include 'db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for required parameters
    if (!isset($_POST['product_id']) || !isset($_POST['stock_in'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    $product_id = $_POST['product_id'];
    $stock_in = $_POST['stock_in'];

    if (!is_numeric($stock_in) || !is_numeric($product_id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    // Fetch total stock data
    $current_stock_query = "
        SELECT COALESCE(SUM(s.stock_in), 0) AS total_stock_in, 
               COALESCE(SUM(s.stock_out), 0) AS total_stock_out,
               COALESCE((SELECT SUM(quantity) FROM sales WHERE product_id = ?), 0) AS total_sold
        FROM stock s
        WHERE s.product_id = ?";

    $current_stmt = $conn->prepare($current_stock_query);
    $current_stmt->bind_param('ii', $product_id, $product_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $current_row = $current_result->fetch_assoc();

    $total_stock_in = $current_row['total_stock_in'] ?: 0;
    $total_stock_out = $current_row['total_stock_out'] ?: 0;
    $total_sold = $current_row['total_sold'] ?: 0;

    // Calculate current stock accurately
    $current_stock = $total_stock_in - $total_sold;

    // Calculate new current stock after adding new stock
    $new_current_stock = $current_stock + $stock_in;

    // Prevent negative stock
    if ($new_current_stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Stock cannot be negative.']);
        exit;
    }

    // Insert stock record
    $sql = "INSERT INTO stock (product_id, stock_in, stock_out, current_stock, date) VALUES (?, ?, 0, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $product_id, $stock_in, $new_current_stock);

    if ($stmt->execute()) {
        // Update current stock in products table only
        $update_product_sql = "UPDATE products SET current_stock = ? WHERE product_id = ?";
        $update_product_stmt = $conn->prepare($update_product_sql);
        $update_product_stmt->bind_param('ii', $new_current_stock, $product_id);

        if (!$update_product_stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error updating product stock: ' . $update_product_stmt->error]);
            exit;
        }

        // Insert into stock_out table
        $stock_out_sql = "INSERT INTO stock_out (product_id, total_sold, date) VALUES (?, ?, NOW())";
        $stock_out_stmt = $conn->prepare($stock_out_sql);
        $stock_out_stmt->bind_param('ii', $product_id, $total_sold); // Use total_sold or any relevant value

        if (!$stock_out_stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error inserting stock_out record: ' . $stock_out_stmt->error]);
            exit;
        }

        echo json_encode(['success' => true, 'current_stock' => $new_current_stock]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add stock']);
    }

    // Close statements
    $stmt->close();
    $current_stmt->close();
    $update_product_stmt->close();
    $stock_out_stmt->close();
    $conn->close();
    exit; 
}
?>
