<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "salesrecord_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if cashier ID and name are received from POST request
if (isset($_POST['cashierId']) && isset($_POST['name'])) {
    $cashierId = $conn->real_escape_string($_POST['cashierId']);
    $name = $conn->real_escape_string($_POST['name']);

    // Update the cashier name in the database
    $sql = "UPDATE cashiers SET name = '$name' WHERE cashier_id = '$cashierId'";

    if ($conn->query($sql) === TRUE) {
        // Return success message
        echo "success";
    } else {
        // Return error message
        echo "error: " . $conn->error;
    }
} else {
    // Return error if data is missing
    echo "error: Missing data";
}

// Close the database connection
$conn->close();
?>
