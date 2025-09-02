<?php
include('db.php');

// Check if the form data is posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productCode = $_POST['productCode'];
    $productName = $_POST['productName'];
    $category = $_POST['category'];
    $unit = $_POST['unit'];
    $price = $_POST['price'];
    $expirationDate = $_POST['expirationDate'];
    $dateAdded = date('Y-m-d'); // Format: YYYY-MM-DD

    // Validate inputs
    if (empty($productCode) || empty($productName) || empty($category) || empty($unit) || empty($price) || empty($expirationDate)) {
        echo 'All fields are required.';
        exit;
    }

    // Validate date format for expiration date
    $dateFormat = 'Y-m-d'; 
    $d = DateTime::createFromFormat($dateFormat, $expirationDate);
    if (!$d || $d->format($dateFormat) !== $expirationDate) {
        echo 'Invalid expiration date format. Please use YYYY-MM-DD.';
        exit;
    }

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

    // Prepare and execute SQL statement to insert product
    $stmt = $conn->prepare("INSERT INTO products (product_code, image, product_name, category, unit, price, expiration_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssds", $productCode, $imagePath, $productName, $category, $unit, $price, $expirationDate);

    if ($stmt->execute()) {
        echo 'Product added successfully!';

        // Insert into the category table
        $stmtCategory = $conn->prepare("INSERT INTO category (category, product_code, product_name) VALUES (?, ?, ?)");
        $stmtCategory->bind_param("sss", $category, $productCode, $productName);

        if ($stmtCategory->execute()) {
            echo 'Category added successfully!';
        } else {
            echo 'Error adding category: ' . $stmtCategory->error;
        }

        $stmtCategory->close();
    } else {
        echo 'Error adding product: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
