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

// Retrieve form data
$name = $_POST['name'];
$username = $_POST['username'];
$password = $_POST['password'];

// Check if username already exists
$checkQuery = $conn->prepare("SELECT * FROM cashiers WHERE username = ?");
$checkQuery->bind_param("s", $username);
$checkQuery->execute();
$result = $checkQuery->get_result();

if ($result->num_rows > 0) {
    // Username already exists
    echo json_encode(["status" => "error", "message" => "Error: Username already taken. Please choose a different one."]);
    exit; // Make sure to exit after sending the response
}

// Check if name already exists
$nameCheckQuery = $conn->prepare("SELECT * FROM cashiers WHERE name = ?");
$nameCheckQuery->bind_param("s", $name);
$nameCheckQuery->execute();
$nameResult = $nameCheckQuery->get_result();

if ($nameResult->num_rows > 0) {
    // Name already exists
    echo json_encode(["status" => "error", "message" => "Error: Cashier name already taken. Please choose a different name."]);
    exit; // Make sure to exit after sending the response
}

// Hash the password before inserting it
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Proceed to insert the new cashier with the hashed password
$insertQuery = $conn->prepare("INSERT INTO cashiers (name, username, password, date_added) VALUES (?, ?, ?, NOW())");
$insertQuery->bind_param("sss", $name, $username, $hashedPassword);

if ($insertQuery->execute()) {
    echo json_encode(["status" => "success", "message" => "Cashier added successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error adding cashier: " . $conn->error]);
}

// Close connections
$checkQuery->close();
$nameCheckQuery->close();
$insertQuery->close();
$conn->close();
?>
