<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Update if needed
$dbname = "salesrecord_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cashierId']) && isset($_POST['newCashierPassword'])) {
        $cashierId = $_POST['cashierId'];
        $newPassword = $_POST['newCashierPassword'];

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Prepare and bind
        $stmt = $conn->prepare("UPDATE cashiers SET password=? WHERE username=?");
        
        if ($stmt) {
            $stmt->bind_param("ss", $hashedPassword, $cashierId); // Bind the hashed password

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo "Password updated successfully.";
                } else {
                    echo "No changes made. Please check if the username is correct.";
                }
            } else {
                echo "Failed to execute statement: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Failed to prepare statement: " . $conn->error;
        }
    } else {
        echo "Required fields are missing.";
    }
}

$conn->close();
?>
