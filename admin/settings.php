<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // Get current username from session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = $conn->real_escape_string($_POST['username']);
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    // Fetch current password hash from the database
    $sql = "SELECT password FROM user WHERE id='$user_id'";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();

    $errors = [];
    $message = '';

    // Validate current password
    if (!password_verify($currentPassword, $user['password'])) {
        $errors[] = "Current password is incorrect.";
    } else {
        $updateQuery = [];

        // Update username if it's changed
        if (!empty($newUsername) && $newUsername !== $username) {
            $updateQuery[] = "username='$newUsername'";
            $_SESSION['username'] = $newUsername; // Update session with new username
        }

        // Update password if it's provided
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateQuery[] = "password='$hashedPassword'";
        }

        // Perform the update if there are changes
        if (!empty($updateQuery)) {
            $sql = "UPDATE user SET " . implode(', ', $updateQuery) . " WHERE id='$user_id'";
            if ($conn->query($sql) === TRUE) {
                $message = "Settings updated successfully.";
            } else {
                $errors[] = "Error updating settings: " . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        .header {
            background-color: black;
            color: white;
            text-align: left;
            padding: 20px 0;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .header .title {
            flex: 1;
            margin-left: 30px;
        }

        .header .logout-icon {
            color: white;
            font-size: 24px;
            margin: 0 20px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: color 0.3s;
        }

        .header .logout-icon:hover {
            color: #cc0000;
        }

        .header .logout-icon i {
            margin-right: 0;
        }

        /* CSS for content area */
        .container {
            margin-top: 80px; /* Adjust based on header height */
            padding: 20px;
            max-width: 800px;
            margin: 80px auto;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .form-group input[type="submit"]:hover {
            background-color: #45a049;
        }

        .message {
            margin-bottom: 20px;
            color: #4CAF50;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">User Settings</div>
        <a href="logout.php" class="logout-icon" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

    <div class="container">
        <h1>Update Settings</h1>

        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="error"><?php echo implode('<br>', $errors); ?></div>
        <?php endif; ?>

        <form action="settings.php" method="post">
            <div class="form-group">
                <label for="username">New Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="form-group">
                <label for="currentPassword">Current Password</label>
                <input type="password" id="currentPassword" name="currentPassword" required>
            </div>
            <div class="form-group">
                <label for="newPassword">New Password (leave empty to keep current)</label>
                <input type="password" id="newPassword" name="newPassword">
            </div>
            <div class="form-group">
                <input type="submit" value="Save Changes">
            </div>
        </form>
    </div>

    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php'; // Replace with your actual logout URL
            }
        }
    </script>
</body>
</html>
