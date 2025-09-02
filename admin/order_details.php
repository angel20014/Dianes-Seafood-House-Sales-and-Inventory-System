<?php
include('db.php');



    // Query to fetch order details
    $query = 'SELECT * FROM orders'; // Modify the query based on your actual database schema
    $stmt = $pdo->query($query);

    // Fetch all order details as an associative array
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

   
    // Return error message in JSON format
    echo json_encode(['error' => $e->getMessage()]);

?>

