<?php
include 'db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $currentUsername = $_POST['currentUsername'];
    $currentPassword = $_POST['currentPassword'];

    $newUsername = $_POST['newUsername'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate inputs
    if ($newPassword !== $confirmPassword) {
        $response['message'] = 'Passwords do not match.';
        echo json_encode($response);
        exit();
    }

    // Check current username and password
    $stmt = $conn->prepare("SELECT password FROM user WHERE username = ?");
    $stmt->bind_param("s", $currentUsername);
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($currentPassword, $storedPassword)) {
        $response['message'] = 'Current password is incorrect.';
        echo json_encode($response);
        exit();
    }

    // Update username and password
    $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE user SET username = ?, password = ? WHERE username = ?");
    $stmt->bind_param("sss", $newUsername, $newPasswordHash, $currentUsername);
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Settings updated successfully.';
    } else {
        $response['message'] = 'Error updating settings.';
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
