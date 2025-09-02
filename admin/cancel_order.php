<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve order details from the request (e.g., product_id and quantity canceled)
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Update the total stock_out value in the stock table by reducing the canceled quantity
    $updateStockOutSql = "UPDATE stock SET stock_out = stock_out - ? WHERE product_id = ?";
    $stmt = $conn->prepare($updateStockOutSql);
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    // Retrieve the total stock_in and total stock_out for calculating the current stock
    $stockCalcSql = "
        SELECT 
            COALESCE(SUM(stock_in), 0) AS total_stock_in,
            COALESCE(SUM(stock_out), 0) AS total_stock_out
        FROM stock
        WHERE product_id = ?
    ";
    $stmt = $conn->prepare($stockCalcSql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($total_stock_in, $total_stock_out);
    $stmt->fetch();
    $stmt->close();

    // Calculate the updated current stock
    $current_stock = $total_stock_in - $total_stock_out;

    // Update the products table with the new current stock value
    $updateProductStockSql = "UPDATE products SET current_stock = ? WHERE product_id = ?";
    $stmt = $conn->prepare($updateProductStockSql);
    $stmt->bind_param("ii", $current_stock, $product_id);
    $stmt->execute();
    $stmt->close();

    // Return the updated current stock to be used for display
    echo json_encode(['current_stock' => $current_stock]);
}
?>
