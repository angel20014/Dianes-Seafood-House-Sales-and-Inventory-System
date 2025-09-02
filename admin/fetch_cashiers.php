<?php 
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "salesrecord_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the limit from the query string
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5; // Default to 5

// Fetch cashiers from the database with a limit, including date_added
$stmt = $conn->prepare("SELECT cashier_id, username, password, name, date_added FROM cashiers LIMIT ?");
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$output = '';
while ($row = $result->fetch_assoc()) {
    $output .= "<tr>";
    $output .= "<td>" . htmlspecialchars($row['cashier_id']) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['username']) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['password']) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['name']) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['date_added']) . "</td>"; // Display date_added
    $output .= "<td>
        <button onclick=\"openChangePasswordModal('" . htmlspecialchars($row['username']) . "')\" class='action-btn'>
            <i class='fas fa-key'></i> 
        </button>
        <button onclick=\"openEditNameModal('" . htmlspecialchars($row['cashier_id']) . "', '" . htmlspecialchars($row['name']) . "')\" class='action-btn'>
            <i class='fas fa-edit'></i> 
        </button>
    </td>";
    $output .= "</tr>";
}

$stmt->close();
$conn->close();

echo $output;
?>
