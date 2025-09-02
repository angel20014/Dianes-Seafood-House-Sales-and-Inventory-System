<?php
include('db.php'); // Include your database connection file

// Fetch expired products from the expired_products table
$query = "SELECT * FROM expired_products";
$result = $conn->query($query);

// Fetch expired products
$sqlFetchExpired = "SELECT id, product_id, product_name, category, expiration_date, current_stock FROM expired_products";
$result = $conn->query($sqlFetchExpired);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['product_name'] . "</td>";
        echo "<td>" . $row['category'] . "</td>";
        echo "<td>" . $row['expiration_date'] . "</td>";
        echo "<td>" . $row['current_stock'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No expired products found.</td></tr>";
}

$conn->close();
?>