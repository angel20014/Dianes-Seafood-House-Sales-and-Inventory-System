<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $product_name = $_POST["product_name"];
    $price = $_POST["price"];
    $stock = $_POST["stock"];
    $quantity = $_POST["quantity"];
    $totalPrice = $_POST["totalPrice"];
    $amountPaid = $_POST["amountPaid"];
    $changeAmount = $_POST["changeAmount"];

    // Perform any necessary validation or processing

    // For demonstration purposes, let's just output the data
    echo "Product_name: " . $product_name . "<br>";
    echo "Price: " . $price . "<br>";
    echo "Stock: " . $stock . "<br>";
    echo "Quantity: " . $quantity . "<br>";
    echo "Total Price: " . $totalPrice . "<br>";
    echo "Amount Paid: " . $amountPaid . "<br>";
    echo "Change Amount: " . $changeAmount . "<br>";

    // Here you can perform database operations to store the sales data
    // For example, you could use MySQLi or PDO to insert the data into a database
    // Remember to properly sanitize and validate user input before inserting into the database
}
?>
