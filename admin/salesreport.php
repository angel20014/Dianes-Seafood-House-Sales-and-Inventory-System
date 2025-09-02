<?php
// salesreport.php
include('db.php');

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $query = $input['query'];

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Fetch results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return results as JSON
    echo json_encode($results);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
