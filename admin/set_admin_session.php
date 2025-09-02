<?php
session_start(); // Start the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputPin = $_POST['pin'];
    $correctPin = "5678"; // The correct PIN should match the one in your JavaScript code

    // Validate the entered PIN
    if ($inputPin === $correctPin) {
        $_SESSION['admin_logged_in'] = true; // Set session variable
        echo "success"; // Respond with success
    } else {
        echo "failure"; // Respond with failure if PIN doesn't match
    }
} else {
    echo "invalid_request"; // Handle invalid request types
}
?>
