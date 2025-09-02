<?php
include('db.php'); // Ensure you include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $categories = $_POST['categories'];
    $product_codes = $_POST['product_codes'];
    $product_names = $_POST['product_names'];

    // Iterate over categories and products
    for ($i = 0; $i < count($categories); $i++) {
        $category = $categories[$i];
        $product_code = $product_codes[$i];
        $product_name = $product_names[$i];

        // Prepare an SQL query to insert the data
        $query = "INSERT INTO category (product_code, product_name) VALUES ( ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $category, $product_code, $product_name);

        // Execute the query
        if ($stmt->execute()) {
            echo "Record for $product_name in category $category saved successfully.<br>";
        } else {
            echo "Error saving record for $product_name: " . $stmt->error . "<br>";
        }
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
