<?php
include('db.php'); // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure you are checking if the arrays are set
    if (isset($_POST['categories']) && isset($_POST['product_codes']) && isset($_POST['product_names'])) {
        $categories = $_POST['categories'];
        $product_codes = $_POST['product_codes'];
        $product_names = $_POST['product_names'];

        // Loop through and insert into the database
        for ($i = 0; $i < count($categories); $i++) {
            $category = $categories[$i];
            $product_code = $product_codes[$i];
            $product_name = $product_names[$i];

            // Prepare your SQL statement
            $insertCategorySql = "INSERT INTO category (category, product_code, product_name) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertCategorySql);
            $stmt->bind_param("sss", $category, $product_code, $product_name);

            if (!$stmt->execute()) {
                echo "Error inserting category: " . $stmt->error;
            }
        }
    } else {
        echo "No data received.";
    }


    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
