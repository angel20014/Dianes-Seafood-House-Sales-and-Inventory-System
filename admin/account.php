<?php
session_start();
include('db.php'); // Include your database connection

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $username = $_SESSION['username'];

    // Retrieve the current password hash from the database
    $stmt = $conn->prepare("SELECT password FROM user WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify the current password
    if (password_verify($current_password, $hashed_password)) {
        if ($new_password === $confirm_password) {
            // Hash the new password and update in the database
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("UPDATE user SET password = ? WHERE username = ?");
            $stmt->bind_param('ss', $new_hashed_password, $username);

            if ($stmt->execute()) {
                $message = "Password updated successfully.";
            } else {
                $message = "Error updating password.";
            }

            $stmt->close();
        } else {
            $message = "New passwords do not match.";
        }
    } else {
        $message = "Current password is incorrect.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings</title>
    <style>
        /* Add your CSS styling similar to previous pages */
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Account Settings</h2>
        <form method="post" action="">
            <div>
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Enter your current password" required>
            </div>
            <div>
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Enter your new password" required>
            </div>
            <div>
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm your new password" required>
            </div>
            <div>
                <input type="submit" value="Change Password">
            </div>
        </form>
        <p class="error"><?php echo htmlspecialchars($message); ?></p>
    </div>
</body>
</html>
