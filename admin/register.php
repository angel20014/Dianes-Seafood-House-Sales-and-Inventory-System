<?php
include('db.php');

session_start(); // Start the session

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../index.php"); // Redirect to the main login page
    exit; // Ensure no further code is executed
}


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection file
    include "db.php";

    // Define variables and initialize with empty values
    $username = $password = $contactnumber = $secret_question = $secret_answer = "";

    // Processing form data when form is submitted
    $username = $_POST["username"];
    $password = $_POST["password"];
    $secret_question = $_POST["secret_question"];
    $secret_answer = $_POST["secret_answer"];

    // Set default status as 'active'
    $status = 'active';

    // Check if the username already exists
    $check_sql = "SELECT id FROM user WHERE username = ?";
    if ($check_stmt = $conn->prepare($check_sql)) {
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Username already exists, redirect back with an error message
            $_SESSION["registration_error"] = "Username already taken. Please choose a different one.";
            header("Location: register.php"); // Redirect to your registration form
            exit();
        }
        $check_stmt->close();
    }

    // Prepare an insert statement
    $sql = "INSERT INTO user (username, password, secret_question, secret_answer, status) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("sssss", $param_username, $param_password, $param_secret_question, $param_secret_answer, $param_status);

        // Set parameters
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password before saving in the database
        $param_secret_question = $secret_question;
        $param_secret_answer = $secret_answer; // You may also hash this if security is a concern
        $param_status = $status; // Set default status

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Set session variable to indicate successful registration
            $_SESSION["registration_success"] = true;

            // Redirect to login page after successful registration
            header("Location: login.php");
            exit(); // Ensure no further code is executed after redirect
        } else {
            echo "Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $conn->close();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
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

        h2 {
            text-align: center;
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

        .container {
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

        @media (max-width: 600px) {
            .container {
                max-width: 80%; /* Adjust the width for smaller screens */
            }
        }

        @media (max-width: 400px) {
            .container {
                margin-top: 20px; /* Reduce top margin for smaller screens */
                padding: 10px; /* Adjust padding for smaller screens */
                border-width: 2px; /* Adjust border width for smaller screens */
            }
        }

        input[type="text"],
        input[type="password"],
        input[type="submit"],
        select {
            width: 100%; /* Set width to 100% for all input elements */
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 20px;
            position: relative; /* Position relative for the eye icon */
        }

        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 20px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        label {
            font-weight: bold; /* Make labels bold */
            font-size: 20px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
        }

        .login-link a:hover {
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

        .alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-danger {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1;
}

    </style>
    <!-- FontAwesome for eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>


    <div class="container" id="register-container">
        
        <h2>Sign Up Form</h2>

        
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="username">Username</label>
            <input type="text" name="username" placeholder="Username" required><br>

            <label for="password">Password</label>
            <div style="position: relative;">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <i class="fas fa-eye eye-icon" onclick="togglePassword()"></i>
            </div><br>

    
            <label for="secret_question">Secret Question</label>
            <select name="secret_question" required>
                <option value="">Select a secret question</option>
                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                <option value="What was your first pet's name?">What was your first pet's name?</option>
                <option value="What is your favorite color?">What is your favorite color?</option>
                <!-- Add more secret questions as needed -->
            </select><br>

            <label for="secret_answer">Secret Answer</label>
            <input type="text" name="secret_answer" placeholder="Secret Answer" required><br>

            <!-- Register button -->
            <input type="submit" value="Register">
        </form>

          <!-- Display registration error message -->
          <?php if (isset($_SESSION["registration_error"])): ?>
            <div class="alert alert-danger">
                <?php
                echo $_SESSION["registration_error"];
                unset($_SESSION["registration_error"]); // Clear the message after displaying it
                ?>
            </div>
        <?php endif; ?>
        

        <!-- Success message -->
        <?php if(isset($_SESSION["registration_success"]) && $_SESSION["registration_success"] === true): ?>
            <p>Registration successful!</p>
        <?php endif; ?>

        <!-- Login link -->
        <div class="login-link">
            <p class="register-link">Already have an account? <a href="login.php">Please Login</a></p>
        </div>
    </div>

    <script>
        function closeContainer() {
            const container = document.getElementById('register-container');
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
