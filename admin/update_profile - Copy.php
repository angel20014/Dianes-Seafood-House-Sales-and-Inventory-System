<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $user_id = $_SESSION['user_id'];

    $sql = "UPDATE user SET username = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $username, $user_id);

    if ($stmt->execute()) {
        $message = "Username updated successfully.";
    } else {
        $message = "Error updating username: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <style>
        /* Add relevant styles */
    </style>
</head>
<body>
    <h1>Update Profile</h1>
    <form action="update_profile.php" method="post">
        <label for="username">New Username:</label>
        <input type="text" name="username" id="username" required>
        <input type="submit" value="Update Username">
    </form>
    <p><?php echo $message; ?></p>
</body>
</html>
