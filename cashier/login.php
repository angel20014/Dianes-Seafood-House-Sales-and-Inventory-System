<?php
session_start(); // Start the session
include('db.php'); // Include the database connection

$message = ''; // Initialize the message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cashierUsername = trim($_POST['username']);
    $cashierPassword = trim($_POST['password']);

    // Validate input
    if (empty($cashierUsername) || empty($cashierPassword)) {
        $message = "Please enter both username and password.";
    } else {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM cashiers WHERE username = ?");
        $stmt->bind_param("s", $cashierUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Debugging line to check the retrieved hashed password
            // echo "Hashed Password: " . $row['password']; // Uncomment for debugging

            // Verify the password
            if (password_verify($cashierPassword, $row['password'])) {
                // Set up a session for the logged-in cashier
                $_SESSION['cashier_id'] = $row['cashier_id']; // Assuming 'id' is the primary key
                $_SESSION['cashier_username'] = $row['username'];

                // Redirect to cashier_dashboard.php after successful login
                header("Location: cashier_dashboard.php");
                exit;
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "Cashier not found.";
        }
    }
}
?>


<!-- HTML for the login form remains unchanged -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Login</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link your CSS file -->
    <style>
        body {
            font-family: Roboto, Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            background-image: url('loginbg.jpg');
            background-size: cover;
            filter: blur(3px); /* Adjust the blur intensity as needed */
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 400px;
            text-align: left;
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 93%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #4cae4c;
        }

        .centered {
        text-align: center; /* Center the text */
        margin-bottom: 20px; /* Optional: add some space below the heading */
        font-size: 30px;
    }
    </style>
</head>
<body>
    <div class="login-container">
    <h2 class="centered">Cashier</h2>
        <?php if (!empty($message)): ?>
            <p class="error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>