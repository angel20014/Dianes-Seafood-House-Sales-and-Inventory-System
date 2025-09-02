<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $productCode = isset($_POST['product_code']) ? htmlspecialchars($_POST['product_code']) : '';
    $productGroup = isset($_POST['product_group']) ? htmlspecialchars($_POST['product_group']) : '';

    // Check if all required fields are filled
    if ($productId > 0 && !empty($productCode) && !empty($productGroup)) {
        // Prepare and execute the SQL query to insert the new category
        $stmt = $conn->prepare("INSERT INTO category (product_id, product_code, product_group) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $productId, $productCode, $productGroup);

        if ($stmt->execute()) {
            // Redirect to the inventory page or show success message
            header('Location: inventory.php');
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Please fill all fields.";
    }
}

?>