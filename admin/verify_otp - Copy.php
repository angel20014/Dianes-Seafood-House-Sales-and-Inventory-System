<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if OTP is provided
    if (isset($_POST['otp'])) {
        $user_otp = $_POST['otp'];
        
        // Check if OTP matches the one stored in the session
        if (isset($_SESSION['otp']) && $_SESSION['otp'] == $user_otp) {
            // If OTP is verified, redirect to create password page
            header("Location: create_password.php");
            exit();
        } else {
            // If OTP is invalid, display error message
            $message = "Invalid OTP. Please try again.";
        }
    }
} else {
    // If request method is not POST, redirect back to send OTP page
    header("Location: send_otp.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 400px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        form {
            margin-top: 20px;
            text-align: center;
        }
        input[type="text"] {
            width: 40px;
            padding: 10px;
            margin: 0 5px; /* Adjusted margin here */
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
            text-align: center;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 40px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Verify OTP</h2>
        <p class="message"><?php echo isset($message) ? $message : ''; ?></p>
        <form id="otpForm" action="create_password.php" method="post">
            <div>
                <input type="text" id="otp1" name="otp[]" maxlength="1" required>
                <input type="text" id="otp2" name="otp[]" maxlength="1" required>
                <input type="text" id="otp3" name="otp[]" maxlength="1" required>
                <input type="text" id="otp4" name="otp[]" maxlength="1" required>
                <input type="text" id="otp5" name="otp[]" maxlength="1" required>
                <input type="text" id="otp6" name="otp[]" maxlength="1" required>
            </div>
            <div>
                <input type="submit" value="Verify OTP">
            </div>
        </form>
    </div>

    <script>
        window.onload = function() {
            var otp = "<?php echo isset($_SESSION['otp']) ? $_SESSION['otp'] : ''; ?>"; // Get the OTP from PHP session
            var otpInputs = document.querySelectorAll('input[name="otp[]"]');
            for (var i = 0; i < otpInputs.length; i++) {
                otpInputs[i].value = otp.charAt(i); // Fill each input with a character from OTP
            }
        };
    </script>
</body>
</html>
