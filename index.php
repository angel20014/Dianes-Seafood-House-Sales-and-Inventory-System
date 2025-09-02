<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
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
            background-image: url('admin/loginbg.jpg');
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
            text-align: center;
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 70px; /* Adjust margin for better spacing */
        }

        .button {
            padding: 25px 35px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            text-decoration: none; /* Remove underline from links */
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #0056b3;
        }
        .logo {
            width: 200px; /* Set the desired width */
            height: 200px; /* Set the same height for a square image */
            border-radius: 50%; /* Makes the image circular */
            object-fit: cover; /* Ensures the image covers the container without distortion */
            margin-bottom: 30px; /* Space below the logo */
            margin-top: 1px;
        }
        .logo-name {
    margin: 5px 0; /* Set margin above and below to reduce space */
    font-size: 17px; /* Adjust font size */
    color: #333; /* Set text color */
}

h2 {
    margin: 5px 0; /* Reduce margin to bring h2 closer to h3 */
}

    </style>
</head>
<body>
    <div class="login-container">
    <img src="admin/logo.png" alt="Logo" class="logo">
    <h3 class="logo-name">Diane's Seafood House</h3>
    <h2>Sales and Inventory System</h2>
        <div class="button-container">
        <a href="#" class="button" onclick="confirmAdminLogin(event)">Admin Login</a> <!-- Use onclick to call the function -->
            <a href="cashier/login.php" class="button">Cashier Login</a>
        </div>
    </div>

    <script>

        function confirmAdminLogin(event) {
    // Prevent the default action of the link
    event.preventDefault();
    
    // Ask for confirmation
    var isAdmin = confirm("Are you an admin?");
    
    // If confirmed, prompt for PIN
    if (isAdmin) {
        var pin = prompt("Please enter the admin PIN:");
        
        // Check if the PIN matches
        var correctPin = "1234"; // Example PIN
        if (pin === correctPin) {
            // Send an AJAX request to set the session variable (using a PHP script)
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "set_admin_session.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        window.location.href = 'admin/login.php'; // Redirect to admin login page
                    } else {
                        alert("Error setting session. Access denied.");
                    }
                }
            };
            xhr.send("isAdmin=true"); // Send admin session variable
        } else {
            alert("Incorrect PIN. Access denied.");
        }
    }
}
    </script>

</body>
</html>
