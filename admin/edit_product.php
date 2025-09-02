<?php
include('db.php');

// Get POST data
$productId = $_POST['productId'];
$productCode = $_POST['productCode'];
$productName = $_POST['productName'];
$category = $_POST['category'];
$unit = $_POST['unit'];
$price = $_POST['price'];
$expirationDate = $_POST['expirationDate'];

// Initialize the image path variable
$imagePath = '';

// Handle the image upload if a new image is provided
if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "uploads/"; // Ensure this directory exists and is writable
    $fileName = basename($_FILES['productImage']['name']);
    $targetFilePath = $targetDir . uniqid() . '_' . $fileName; // Prevent overwrites

    // Move the uploaded file
    if (move_uploaded_file($_FILES['productImage']['tmp_name'], $targetFilePath)) {
        $imagePath = $fileName; // Save only the filename for database insertion
    } else {
        echo 'Error uploading image.';
        exit;
    }
}

// Prepare the update query
if (!empty($imagePath)) {
    // If an image was uploaded, include it in the update
    $query = "UPDATE products SET product_code = ?, product_name = ?, category = ?, unit = ?, price = ?, expiration_date = ?, image = ? WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssssssi', $productCode, $productName, $category, $unit, $price, $expirationDate, $imagePath, $productId);
} else {
    // If no new image was uploaded, exclude the image from the update
    $query = "UPDATE products SET product_code = ?, product_name = ?, category = ?, unit = ?, price = ?, expiration_date = ? WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssi', $productCode, $productName, $category, $unit, $price, $expirationDate, $productId);
}

// Execute the query and check for errors
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo 'Product updated successfully';
    } else {
        echo 'No changes made to the product.';
    }
} else {
    echo 'Failed to update product: ' . $stmt->error;
}

// Clean up
$stmt->close();
$conn->close();
?>
