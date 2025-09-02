<?php
include('db.php');

session_start(); // Start the session

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../index.php"); // Redirect to the main login page
    exit; // Ensure no further code is executed
}


$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SQL query to get user details
    $sql = "SELECT * FROM user WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $row['password'])) {
            // Set up session variables for the logged-in user
            $_SESSION['user_id'] = $row['id']; // Use the correct column name for user ID
            $_SESSION['username'] = $row['username'];
        
            // Redirect to the dashboard after successful login
            header("Location: dashboard.php");
            exit; // Make sure to exit after the header redirect
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap">

    <meta charset="UTF-8">
    <title>Login</title>
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

        .title {
    position: absolute;
    top: 60px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
}

.title-main {
    font-size: 52px;
    color: blue;
    font-weight: bold;
    font-family: Roboto, Arial, sans-serif; /* Ensure fallback fonts */
    -webkit-text-stroke: 1px black; /* For an outline effect */
    text-shadow: 2px 2px 6px rgba(255, 255, 255, 0.5); /* White shadow */
}

.title-sub {
    font-size: 32px;
    color: blue;
    font-weight: normal;
    font-family: Roboto, Arial, sans-serif; /* Ensure fallback fonts */
    margin-top: 10px; /* Space between the main and sub titles */
}


        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 400px;
            text-align: center;
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        p {
            font-size: 16px;
            text-align: center;
            margin-bottom: 20px;
        }

        .login-container h2 {
            font-size: 24px; /* Adjust the font size as needed */
            margin-top: 0; /* Remove default margin */
            margin-bottom: 20px; /* Add space below the title */
            color: #333; /* Text color */
        }

        .login-container label {
            font-size: 18px; /* Adjust the font size as needed */
            display: block; /* Make labels block-level elements */
            margin-bottom: 5px; /* Add space below each label */
            text-align: left; /* Align labels to the left */
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px; /* Adjust the font size as needed */
            position: relative;
        }

        .login-container .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .login-container input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 18px; /* Adjust the font size as needed */
            margin-top: 10px; /* Add spacing between the submit button and the forgot password link */
        }

        .login-container input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .message {
            color: red;
            margin-bottom: 10px;
        }

        .forgot-password {
            margin-top: 20px; /* Adjust the margin-top as needed */
            text-align: left;
        }

        .forgot-password a {
            color: #007bff;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .button-container {
            position: absolute;
            top: 40px;
            right: 20px;
        }

        .button-container a {
            background-color: #007bff;
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            margin: 0 5px;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-size: 22px;
        }

        .button-container a:hover {
            background-color: #0056b3;
        }

        .close-button {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 30px;
            height: 30px;
            background-color: #ff5f57; /* Red color similar to close button */
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-button:before {
            content: '✕'; /* Unicode character for a multiplication sign */
            color: white;
            font-size: 16px;
            line-height: 1;
        }
        img {
            height: 100px;
            margin-bottom: 50px; /* Added margin */
            margin-top: 20px;
            margin-left: 20px; /* Added margin */
            margin-right: 10px; /* Added margin */
            filter: blur(.5px); /* Apply blur effect */
            border-radius: 50%; /* Make it circular */
        }
    </style>
    <!-- FontAwesome for eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="login-container" id="login-container">
    
    <h2>ADMIN </h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="login.php" method="post">
        <div>
            <label for="username">Username</label>
            <input type="text" id="username" name="username">
        </div>
        <div>
            <label for="password">Password</label>
            <div style="position: relative;">
                <input type="password" id="password" name="password">
                <i class="fas fa-eye eye-icon" onclick="togglePassword()"></i>
            </div>
        </div>

        <p class="register-link">Don't have an account? Please<a href="register.php"> Sign Up</a>.</p>

        <div>
            <input type="submit" value="Login">
        </div>

        <div class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </form>
</div>
<script>
    function closeContainer() {
        const container = document.getElementById('login-container');
        container.style.display = 'none';
    }

    function togglePassword() {
        const passwordField = document.getElementById('password');
        const eyeIcon = document.querySelector('.eye-icon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>
</body>
</html>
