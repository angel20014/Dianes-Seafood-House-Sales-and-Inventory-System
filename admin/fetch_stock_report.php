<?php
include 'db.php';

// Fetch stock data
$stockSql = "
    SELECT stock_id, product_id, stock_in, stock_out, current_stock, date
    FROM stock
";

$stockResult = $conn->query($stockSql);

if ($stockResult->num_rows > 0) {
    while ($row = $stockResult->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row['stock_id']) . "</td>
            <td>" . htmlspecialchars($row['product_id']) . "</td>
            <td>" . htmlspecialchars($row['stock_in']) . "</td>
            <td>" . htmlspecialchars($row['stock_out']) . "</td>
            <td>" . htmlspecialchars($row['current_stock']) . "</td>
            <td>" . htmlspecialchars($row['date']) . "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6' class='no-products'>No stock data available</td></tr>";
}

$stockResult->free();
$conn->close();
?>
