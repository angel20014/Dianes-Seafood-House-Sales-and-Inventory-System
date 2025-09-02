<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salesrecord_db"; // Change this to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username from the URL parameter
if (isset($_GET['username'])) {
    $cashierUsername = $conn->real_escape_string($_GET['username']);

    // Prepare the delete statement
    $sql = "DELETE FROM cashiers WHERE username='$cashierUsername'";

    if ($conn->query($sql) === TRUE) {
        // Redirect back to the user management page with a success message
        header("Location: user_management.php?message=Cashier deleted successfully");
        exit();
    } else {
        // Redirect back with an error message
        header("Location: user_management.php?message=Error deleting cashier: " . $conn->error);
        exit();
    }
} else {
    // If username is not set, redirect back with an error message
    header("Location: user_management.php?message=No username provided");
    exit();
}

// Close the connection
if ($conn) {
    $conn->close();
}
?>
