<?php
// Include your database connection file
include('db.php');

// Check if all required fields are filled
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'], $_POST['editProductName'], $_POST['editCategory'], $_POST['editStock'], $_POST['editPrice'], $_POST['editStatus'], $_POST['editDate'], $_POST['editExpirationDate'])) {
    // Sanitize input data
    $product_id = $_POST['product_id'];
    $product_name = htmlspecialchars($_POST['editProductName']);
    $category = htmlspecialchars($_POST['editCategory']);
    $stock = intval($_POST['editStock']);
    $price = floatval($_POST['editPrice']);
    $status = htmlspecialchars($_POST['editStatus']);
    $date = $_POST['editDate']; // Assuming date format is YYYY-MM-DD
    $expiration_date = $_POST['editExpirationDate']; // Assuming expiration format is YYYY-MM-DD

    // Update product details in the products table
    $query = "UPDATE products SET product_name=?, category=?, stock=?, price=?, status=?, date=?, expiration_date=? WHERE product_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssidssii", $product_name, $category, $stock, $price, $status, $date, $expiration_date, $product_id);
    $stmt->execute();

    // Check if update was successful
    if ($stmt->affected_rows > 0) {
        // Update inventory table
        // Assuming you have an inventory table with columns: product_id, stock
        $inventory_query = "UPDATE inventory SET stock=? WHERE product_id=?";
        $inventory_stmt = $conn->prepare($inventory_query);
        $inventory_stmt->bind_param("ii", $stock, $product_id);
        $inventory_stmt->execute();

        // Check if update was successful
        if ($inventory_stmt->affected_rows > 0) {
            // Send JSON response indicating success
            echo json_encode(["success" => true]);
            exit;
        } else {
            // Send JSON response indicating inventory update failed
            echo json_encode(["success" => false, "message" => "Error updating inventory. Please try again."]);
            exit;
        }
    } else {
        // Send JSON response indicating product update failed
        echo json_encode(["success" => false, "message" => "Error updating product. Please try again."]);
        exit;
    }
} else {
    // Send JSON response indicating missing or invalid fields
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit;
}
?>
