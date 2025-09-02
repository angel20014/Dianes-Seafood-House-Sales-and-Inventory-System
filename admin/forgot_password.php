            <?php
            session_start();
            include('db.php'); // Include your database connection

            $message = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = $_POST['username']; // Change to a unique identifier like username or email

                // Check if the username exists in the database
                $sql = "SELECT * FROM user WHERE username = '$username'"; // Change the column to match your database structure
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // If the username exists, fetch the secret question
                    $user = $result->fetch_assoc();
                    $_SESSION['username'] = $username; // Store username in session
                    $_SESSION['secret_question'] = $user['secret_question']; // Store secret question in session

                    header("Location: answer_secret_question.php"); // Redirect to answer secret question page
                    exit();
                } else {
                    $message = "Username not found."; // Adjust message accordingly
                }
            }
            $conn->close();
            ?>


            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Forgot Password</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                        background-image: url('loginbg.jpg'); /* Adjust this path */
                        background-size: cover;
                        background-position: center;
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
                        top: 40px; /* Adjust the distance from the top as needed */
                        left: 50%;
                        transform: translateX(-50%);
                        text-align: center;
                        z-index: 1; /* Ensure it's above the blurred background */
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

                    /* Adjust styling for the wrapper */
        .wrapper {
            background-color: rgba(255, 255, 255, 0.9); /* Slightly transparent white */
            padding: 40px;
            border-radius: 10px; /* Slightly rounded corners */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.6); /* Enhanced shadow for better contrast */
            width: 400px;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1; /* Ensure it appears above the background */
        }

                    .back-btn {
                        position: absolute;
                        top: 20px;
                        left: 20px;
                        font-size: 24px;
                        color: #007bff;
                        cursor: pointer;
                        text-decoration: none;
                    }

                    .back-btn:hover {
                        color: #0056b3;
                    }

                    h2 {
                        margin-top: 0;
                        text-align: center;
                        color: #333;
                    }

                    p {
                        color: #888;
                        text-align: left;
                    }

                    form {
                        margin-top: 20px;
                    }

                    label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        width: 100%; /* Make label take full width */
        text-align: left; /* Align text to the left */
    }

                /* Style for input fields and submit button */
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        /* Style for the text input field */
        input[type="text"] {
            border: 1px solid #ccc; /* Light border for the input field */
            margin-bottom: 20px;
        }

        /* Style for the submit button */
        input[type="submit"] {
            border: none; /* Remove default border for submit button */
            background-color: #007bff; /* Primary color */
            color: #fff; /* Text color */
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3; /* Darker color on hover */
        }

        /* Adjust error message      */
        .error {
            color: red;
            font-size: 14px; /* Slightly smaller font size */
            margin-top: 10px;
        }
                </style>
            </head>
            <body>

            <div class="title">
                    <div class="title-main">Diane's Seafood House</div>
                    <div class="title-sub">Sales and Inventory System</div>
                </div>

                <div class="wrapper">
                    <a href="login.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
                    <h2>Forgot Password</h2>
                    <p>Please enter your username to reset your password.</p>
                    <form action="" method="post">
                        <div>
                            <label>Username</label> <!-- Changed from Contact Number -->
                            <input type="text" name="username" placeholder="Enter your username" required>
                        </div>
                        <div>
                            <input type="submit" value="Next">
                        </div>
                    </form>
                    <p class="error"><?php echo $message; ?></p>
                </div>
            </body>
            </html>
