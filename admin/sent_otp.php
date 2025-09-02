<?php
session_start();
include('db.php'); 

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactnumber = $_POST['contactnumber'];

    // Check if the contact number exists in the database
    $sql = "SELECT * FROM registers WHERE ContactNumber = '$contactnumber'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // If the contact number exists, proceed to send OTP
        $_SESSION['contactnumber'] = $contact_number; 
        header("Location: verify_otp.php");
        exit();
    } else {
        $message = "Contact number not found.";
    }
}
$conn->close();
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
        <p class="message"><?php echo $message; ?></p>
        <form id="otpForm" action="verify_otp.php" method="post">
            <div>
                <input type="text" name="otp[]" id="otp1" maxlength="1" required>
                <input type="text" name="otp[]" id="otp2" maxlength="1" required>
                <input type="text" name="otp[]" id="otp3" maxlength="1" required>
                <input type="text" name="otp[]" id="otp4" maxlength="1" required>
                <input type="text" name="otp[]" id="otp5" maxlength="1" required>
                <input type="text" name="otp[]" id="otp6" maxlength="1" required>
            </div>
            <div>
                <input type="submit" value="Verify OTP">
            </div>
        </form>
    </div>

    <script>
        // Automatically fill OTP fields
        window.onload = function() {
            var otp = "<?php echo $otp; ?>"; // Get the OTP from PHP
            var otpInputs = document.querySelectorAll('input[name="otp[]"]');
            otpInputs.forEach(function(input, index) {
                input.value = otp.charAt(index); // Fill each input with a character from OTP
            });
        };
    </script>
</body>
</html>
