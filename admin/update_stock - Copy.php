<?php
include('db.php');

try {
    // Start a transaction
    $conn->begin_transaction();

    // Fetch sales data
    $salesQuery = "SELECT productName, quantity FROM sales WHERE processed = 0";
    $salesResult = $conn->query($salesQuery);

    if (!$salesResult) {
        throw new Exception("Error fetching sales: " . $conn->error);
    }

    // Create an associative array for current stock levels
    $stock_levels = [];
    while ($sale = $salesResult->fetch_assoc()) {
        $productName = $sale['productName'];
        $quantity = $sale['quantity'];

        // Fetch current stock for the product
        $stockQuery = "SELECT current_stock FROM stock WHERE product_id = ?";
        $stmt = $conn->prepare($stockQuery);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stock = $result->fetch_assoc();
            $current_stock = $stock['current_stock'];

            // Deduct the quantity sold from the current stock
            $new_stock = $current_stock - $quantity_sold;
            if ($new_stock < 0) {
                $new_stock = 0; // Ensure stock doesn't go negative
            }

            // Update the stock level in the database
            $update_stock_sql = "UPDATE stock SET current_stock = ? WHERE product_id = ?";
            $stmt = $conn->prepare($update_stock_sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("ii", $new_stock, $product_id);
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception("Error updating stock: " . $stmt->error);
            }

            // Mark sale as processed
            $update_sales_sql = "UPDATE sales SET processed = 1 WHERE productName = ? AND quantity = ?";
            $stmt = $conn->prepare($update_sales_sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("ii", $product_id, $quantity_sold);
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception("Error updating sales: " . $stmt->error);
            }
        } else {
            throw new Exception("Product ID $product_id not found in stock.");
        }
    }

    // Commit the transaction
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Close the connection
$conn->close();
?>
