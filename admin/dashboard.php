    <?php
    session_start();

    // Check if the user is logged in by verifying session variables
    if (!isset($_SESSION['user_id'])) {
        // If the user is not logged in, redirect to the login page
        header("Location: login.php");
        exit;
    }
    
    include 'db.php';

    // Query to get the total count of products
    $sql = "SELECT COUNT(*) AS totalProducts FROM products";
    $result = $conn->query($sql);

    $totalProducts = 0; // Default value in case of an error or no products found
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $totalProducts = $row["totalProducts"];
    }

        // Fetch the total number of expired products
$query = "SELECT COUNT(*) as totalExpired FROM expired_products";
$result = $conn->query($query);

// Check if the query was successful
if ($result) {
    $row = $result->fetch_assoc();
    $totalExpiredProducts = htmlspecialchars($row['totalExpired']);
} else {
    $totalExpiredProducts = "Error fetching data";  // Display an error message if the query fails
}

    // Query to get the total sales
    $sql = "SELECT SUM(total) AS totalSales FROM sales";
    $result = $conn->query($sql);

    $totalSales = 0; // Default value in case of an error or no sales found
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $totalSales = $row["totalSales"];
    }

   // Query to get today's orders with total
$sql = "SELECT product_name, SUM(quantity) AS totalSold, SUM(total) AS totalAmount
FROM sales
WHERE DATE(saleDate) = CURDATE()
GROUP BY product_name";
$result = $conn->query($sql);

// Fetch today's sold products
$todaysOrders = [];
if ($result && $result->num_rows > 0) {
    $todaysOrders = $result->fetch_all(MYSQLI_ASSOC);
}

    // Define the low sales threshold
    $lowSalesThreshold = 10;

    // Query to get total sales of each product
    $sales_sql = "SELECT p.product_name, COALESCE(SUM(s.total), 0) AS totalSales
                FROM products p
                LEFT JOIN sales s ON p.product_id = s.product_name
                GROUP BY p.product_id, p.product_name
                HAVING totalSales < ?";
    $stmt = $conn->prepare($sales_sql);
    $stmt->bind_param("i", $lowSalesThreshold);
    $stmt->execute();
    $lowSalesResult = $stmt->get_result();

    // Fetch the results
    $lowSalesProducts = $lowSalesResult->fetch_all(MYSQLI_ASSOC);

 // Fetch products with no sales based on product_name
$noSalesQuery = "
SELECT p.product_id, p.product_name
FROM products p
LEFT JOIN sales s ON p.product_name = s.product_name
WHERE s.product_name IS NULL";

$noSalesResult = $conn->query($noSalesQuery);
$noSalesProducts = $noSalesResult->fetch_all(MYSQLI_ASSOC);

    // Fetch total stock in and stock out
    $totalStockSql = "
        SELECT COALESCE(SUM(stock_in), 0) AS total_stock_in, 
            COALESCE(SUM(stock_out), 0) AS total_stock_out
        FROM stock
    ";

    // Fetch products and their stock data
    $productsSql = "
        SELECT p.product_id, p.product_name,
            COALESCE(SUM(s.stock_in), 0) AS total_stock_in, 
            COALESCE(SUM(s.stock_out), 0) AS total_stock_out
        FROM products p
        LEFT JOIN stock s ON p.product_id = s.product_id
        GROUP BY p.product_id, p.product_name
    ";

    // Fetch total stock values
    $totalStockResult = $conn->query($totalStockSql);
    $totalStockIn = 0;
    $totalStockOut = 0;

    if ($totalStockResult && $row = $totalStockResult->fetch_assoc()) {
        $totalStockIn = $row['total_stock_in'];
        $totalStockOut = $row['total_stock_out'];
    }

    // Fetch product stock data
    $productsResult = $conn->query($productsSql);

    // Query to get the total number of customers
    $sql = "SELECT COUNT(*) AS totalCustomers FROM customers";
    $result = $conn->query($sql);

    $totalCustomers = 0; // Default value in case of an error or no customers found
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $totalCustomers = $row["totalCustomers"];
    }

    // Define the low sales threshold
    $lowSalesThreshold = 10;

    // Prepare the SQL statement
    $sales_sql = "
        SELECT p.product_name, COALESCE(SUM(s.total), 0) AS totalSales
        FROM products p
        LEFT JOIN sales s ON p.product_name = s.product_name
        GROUP BY p.product_id, p.product_name
        HAVING totalSales < ?";
    $stmt = $conn->prepare($sales_sql);

    if (!$stmt) {
        die('Failed to prepare the SQL statement: ' . $conn->error);
    }

    $stmt->bind_param("i", $lowSalesThreshold);
    $stmt->execute();
    $lowSalesResult = $stmt->get_result();

    if (!$lowSalesResult) {
        die('Failed to execute the query: ' . $conn->error);
    }

    // Fetch the results
    $lowSalesProducts = $lowSalesResult->fetch_all(MYSQLI_ASSOC);

    $totalSold = 0; // Initialize total sold variable

// Fetch total sold from your sales table
$salesSql = "SELECT SUM(quantity) AS total_sold FROM sales";
$salesResult = $conn->query($salesSql);

if ($salesResult && $salesRow = $salesResult->fetch_assoc()) {
    $totalSold = $salesRow['total_sold'] ? $salesRow['total_sold'] : 0; // Set total sold value
}

// Now, your total sold will be available to use in the box
// Query to fetch weekly sales for the current month
$currentMonth = date('m');
$sql_weekly = "
    SELECT 
        WEEK(saleDate) - WEEK(DATE_SUB(saleDate, INTERVAL DAYOFMONTH(saleDate)-1 DAY)) + 1 AS week_of_month,
        COALESCE(SUM(total), 0) AS total
    FROM sales
    WHERE MONTH(saleDate) = $currentMonth 
    AND YEAR(saleDate) = YEAR(CURDATE())
    GROUP BY week_of_month
    ORDER BY week_of_month ASC
";

// Prepare labels for the weeks in the current month (adjust for your needs)
$weeklyLabels = [
    'Week 1',
    'Week 2',
    'Week 3',
    'Week 4',
    'Week 5',
    'Week 6' // Adjust if fewer weeks exist in the current month
];

$result_weekly = $conn->query($sql_weekly);

// Initialize weekly data array to hold sales totals for 6 possible weeks
$weekly_data = array_fill(0, 6, 0);

// Fetch and populate weekly sales data
if ($result_weekly && $result_weekly->num_rows > 0) {
    while ($row = $result_weekly->fetch_assoc()) {
        $weekIndex = $row['week_of_month'] - 1; // Get week of the month (starts at 1)
        
        // Make sure the weekIndex is within bounds (to prevent out-of-bounds errors)
        if ($weekIndex >= 0 && $weekIndex < count($weekly_data)) {
            $weekly_data[$weekIndex] = (int)$row['total']; // Populate weekly sales total
        }
        
        // Debugging output
        echo "Week: " . $row['week_of_month'] . " Total: " . $row['total'] . "<br>";
    }
} else {
    echo "No results found for weekly sales.";
}

// Ensure weekly_data is in the expected format (0 if no sales)
$weekly_data = array_slice($weekly_data, 0, 6); // Keep only 6 weeks' worth of data


// Query to fetch monthly sales
$sql_monthly = "
    SELECT MONTH(saleDate) AS month, 
           COALESCE(SUM(total), 0) AS total
    FROM sales
    WHERE YEAR(saleDate) = YEAR(CURDATE())  -- Get sales for the current year
    GROUP BY month 
    ORDER BY month ASC
";

$result_monthly = $conn->query($sql_monthly);
$labels = [
    'January', 'February', 'March', 'April', 
    'May', 'June', 'July', 'August', 
    'September', 'October', 'November', 'December'
];
$data = array_fill(0, 12, 0); // Initialize data array with zeros

if ($result_monthly && $result_monthly->num_rows > 0) {
    while ($row = $result_monthly->fetch_assoc()) {
        $data[$row['month'] - 1] = (int)$row['total']; // Store total sales for the month
    }
}

// Query to fetch annual sales
// Prepare labels for the upcoming years
$annualLabels = [
    '2024',
    '2025',
    '2026',
    '2027',
    '2028'
];

// Initialize data array with zeros
$annualData = array_fill(0, count($annualLabels), 0); 

// Query to fetch annual sales data for the specified years
$sql_annual = "
    SELECT YEAR(saleDate) AS year, 
           COALESCE(SUM(total), 0) AS total
    FROM sales
    WHERE YEAR(saleDate) IN (2024, 2025, 2026, 2027, 2028)
    GROUP BY year 
    ORDER BY year ASC
";

$result_annual = $conn->query($sql_annual);

// Populate annual data based on the fetched results
if ($result_annual && $result_annual->num_rows > 0) {
    while ($row = $result_annual->fetch_assoc()) {
        $index = array_search($row['year'], $annualLabels);
        if ($index !== false) {
            $annualData[$index] = (int)$row['total']; // Store total sales for the year
        }
    }
}

       
// Query to fetch expired and low stock products
$query = "
SELECT p.product_id, p.product_name, p.expiration_date, p.current_stock
FROM products AS p
WHERE p.expiration_date < NOW() OR p.current_stock < 5"; // Adjust conditions as needed

$result = mysqli_query($conn, $query);
$expiredProducts = [];
$lowStockProducts = [];

// Fetch expired and low stock products
while ($row = mysqli_fetch_assoc($result)) {
    // Check if the product is expired
    if ($row['expiration_date'] < date('Y-m-d')) {
        $expiredProducts[] = $row;
    }
    // Check if the product is low in stock
    if ($row['current_stock'] < 5) { // Adjust threshold as necessary
        $lowStockProducts[] = $row;
    }
}

// Query for out of stock products
$outOfStockQuery = "
SELECT p.product_id, p.product_name
FROM products AS p
WHERE p.current_stock = 0";

$outOfStockResult = mysqli_query($conn, $outOfStockQuery);
$outOfStockProducts = [];

// Fetch out of stock products
while ($outOfStockRow = mysqli_fetch_assoc($outOfStockResult)) {
    $outOfStockProducts[] = $outOfStockRow;
}
    // Close the statement
    $stmt->close();

    // Close the connection
    $conn->close();
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Add Bootstrap CSS and JS in your head or at the end of your body tag -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

    <style>
        /* CSS for header */
       /* Styles for the notification modal */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1000; /* Sit on top */
    left: 0;
    top: 0; /* Position at the top of the viewport */
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fefefe; /* White background */
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888; /* Gray border */
    width: 80%; /* Could be more or less, depending on screen size */
    max-width: 600px; /* Maximum width for larger screens */
    border-radius: 8px; /* Rounded corners */
    position: relative; /* Position for the close button */
}

/* Close Button */
.close-btn {
    color: #aaa; /* Light gray */
    float: right; /* Align to the right */
    font-size: 28px; /* Larger font size */
    font-weight: bold; /* Bold text */
    cursor: pointer; /* Pointer cursor on hover */
}

.close-btn:hover,
.close-btn:focus {
    color: black; /* Change color on hover */
    text-decoration: none; /* Remove underline */
    cursor: pointer; /* Pointer cursor on hover */
}

/* Notification message styling */
#notificationMessage {
    margin: 15px 0; /* Margin above and below */
    font-size: 19px; /* Font size */
    color: #333; /* Dark text color */
}

        .header {
            background-color: #2E8B57;
            color: white;
            text-align: left;
            padding: 20px 0;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .header .title {
            flex: 1;
            margin-left: 30px;
        }

        .header .logout-icon {
            color: white;
            font-size: 24px;
            
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: color 0.3s;
        }

        .header .logout-icon:hover {
            color: #cc0000;
        }

        .header .logout-icon i {
            margin-right: 20px;

        }

        .header .admin-settings-icon {
    color: white;
    font-size: 24px;
    margin-left: 1000px;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: color 0.3s;
}

.header .admin-settings-icon:hover {
    color: #4CAF50; /* Change to a color of your choice for the hover effect */
}

.header .admin-settings-icon i {
    margin-right: 20px;
}
 

        /* Modal styles */
        /* Modal CSS */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 10px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    position: relative;
    border-radius: 8px;
}

/* Close button */
.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Section styles */
.section {
    margin-bottom: 20px;
}

.section h3 {
    margin-top: 0;
}

/* Divider between sections */
.section-divider {
    border-top: 4px solid #ddd;
    margin: 20px 0;
}

/* Form styling */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input {
    width: 95%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.btn-submit,
.btn-cancel {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    margin-right: 10px;
}

.btn-submit {
    background-color: #4CAF50;
    color: white;
}

.btn-submit:hover {
    background-color: #45a049;
}

.btn-cancel {
    background-color: #f44336;
    color: white;
}

.btn-cancel:hover {
    background-color: #e53935;
}

    /* Password visibility toggle styles */
.password-container {
    position: relative;
    display: flex;
    align-items: center;
}

.password-container input {
    width: 100%;
    padding: 10px;
    padding-right: 40px; /* Adjust space for the icon */
}

.password-container i {
    position: absolute;
    right: 10px;
    cursor: pointer;
    font-size: 18px;
    color: #aaa;
}

.password-container i:hover {
    color: #333;
}


        /* CSS for sidebar menu */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 70px;
        }

        
.sidebar {
            width: 250px;
            background-color: black;
            color: white;
            height: 100%;
            position: fixed;
            top: 65px;
            left: 0;
            overflow-x: hidden;
            padding-top: 20px;
            text-align: center;
        }
       /* CSS for sidebar menu */
       .sidebar a {
            padding: 15px 30px;
            text-decoration: none;
            font-size: 24px;
            color: white;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            border-radius: 10px;
            transition: background-color 0.3s;
        }

        .sidebar a i {
            margin-right: 35px;
            text-align: justify;
        }

        .sidebar a:hover {
            background-color: green;
        }

.user-info-sidebar {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background-color: black;
    margin-bottom: 20px;
    border-radius: 8px;
    color: white;
    text-align: center;
}

.user-icon {
    font-size: 90px;
    margin-bottom: 10px;
}

.user-text {
    font-size: 25px;
    font-weight: bold;
}

        .content {
            margin-left: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        /* Additional styles */
.box {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #ff0000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #cc0000;
        }

        .sales-chart {
            height: 400px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .sales-chart .box {
            width: 100%;
            height: 100%;
            padding: 20px;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .sales-chart {
                width: 100%;
            }
        }

        .sales-container {
        display: flex;
        flex-wrap: wrap; /* Allow wrapping to the next line if necessary */
        justify-content: space-between; /* Adjust space between boxes */
        margin-bottom: 20px; /* Add some spacing below the container */
    }
    
    .sales-container .box {
        flex: 1; /* Each box will take equal space */
        min-width: 200px; /* Set a minimum width to prevent boxes from being too narrow */
        margin: 10px; /* Add some margin around the boxes */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Optional: Add shadow for better visibility */
        padding: 15px; /* Add padding for better layout */
        border-radius: 5px; /* Optional: Round the corners */
        background-color: #f9f9f9; /* Optional: Background color */
    }

        .vertical-line {
            width: 12px;
            background-color: black;
            height: calc(100vh - 70px);
            position: fixed;
            top: 70px;
            left: 250px;
            bottom: 0;
        }


       /* CSS for new user info section inside the sidebar */
.user-info-sidebar {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background-color: black; /* Match the sidebar color or choose a different color */
    margin-bottom: 20px; /* Space between user info and the sidebar links */
    border-radius: 8px; /* Rounded corners for visual appeal */
    color: white; /* Text color */
    text-align: center; /* Center align text */
}

.user-icon {
    font-size: 90px; /* Size of the user icon */
    margin-bottom: 10px; /* Space between icon and text */
}

.user-text {
    font-size: 25px; /* Size of the text */
    font-weight: bold; /* Bold text */
}


    /* CSS for colored and sized icons */
.icon-sales {
    color: #4CAF50; /* Green */
    font-size: 50px; /* Adjust size as needed */
}

.icon-customers {
    color: #2196F3; /* Blue */
    font-size: 50px; /* Adjust size as needed */
}

.icon-products {
    color: #FFC107; /* Yellow */
    font-size: 50px; /* Adjust size as needed */
}

.icon-stock-in {
    color: #FF9800; /* Orange */
    font-size: 56px; /* Adjust size as needed */
}

.icon-stock-out {
    color: #F44336; /* Red */
    font-size: 50px; /* Adjust size as needed */
}

/* Optional: Specific styles for text inside the boxes */
.sales-box p,
.inventory-box p,
.low-product-box p,
.out-of-box p
.expired-products-box {
    font-size: 2.5em; /* Ensures the text inside the boxes is appropriately sized */
    color: #333; /* Color of the text */
}


.low-sales-box, .low-inventory-box {
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #ffffff;
    margin-top: 40px;
    width: 50%;
    max-width: 1300px; /* Optional: Set a max-width for better readability */
    text-align: center; /* Center text inside the box */
}


.low-sales-box h2, .low-inventory-box h2 {
    font-size: 2.5em;
    margin-bottom: 10px;
}

.low-sales-box ul, .low-inventory-box ul {
    list-style: none;
    padding: 0;
}

.low-sales-box li, .low-inventory-box li {
    margin-bottom: 5px;
}

#inventoryStock {
            font-size: 2.5em;
            font-weight: bold;
            text-align: center;
            color: #333;
        }

#totalCustomers {
            font-size: 2.5em;
            font-weight: bold;
            text-align: center;
            color: #333;
        }

        #totalSales {
            font-size: 1.5em;
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
            color: #333;
        }

        #lowStocks {
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 50px;
            text-align: center;
            color: #333;
        }

        #outOfStocks {
            font-size: 2.5em;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
            color: #333;
        }

        #lowSalesProducts {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            color: #333;
        }

        #totalExpiredProducts
        {
            font-size: 2.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 30px;
            color: #333;
        }

        .box-containers {
    display: flex; /* Use flexbox for horizontal alignment */
    justify-content: space-between; /* Space the boxes evenly */
    margin: 10px 0; /* Adjust margin as needed */
}

.today-orders-box, .low-sales-box {
    flex: 0 0 98%; /* Set a fixed width for each box */
    padding: 60px;
    margin: 0 50px; /* Optional horizontal spacing */
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    
}

/* Optional: Different background for clarity */
.today-orders-box {
    background-color: #f9f9f9; /* Background color */
}

.low-sales-box {
    background-color: #f0f0f0; /* Background color */
}

.date-time {
            margin-top: 0   ;
    font-size: 18px; /* Adjust size as needed */
    text-align: right;
}

.graph-container {
        width: 100%; /* Full width */
        max-width: 220px; /* Maximum width slightly larger than the canvas */
        margin: 20px auto; /* Center the container */
        text-align: center; /* Center text if any */
       
    }

    #salesGraph {
        width: 20%; /* Make canvas responsive */
        height: auto; /* Maintain aspect ratio */
        margin-right: 20px;
    }

.box {
    flex: 1; /* Each box takes equal width */
    padding: 30px;
    margin: 0 10px; /* Optional horizontal spacing between boxes */
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    min-width: 150px; /* Set a minimum width to avoid too small boxes */
}

/* Additional styles for responsiveness */
@media (max-width: 768px) {
    .sales-container {
        flex-direction: column; /* Stack boxes vertically on smaller screens */
    }
    
    .box {
        margin-bottom: 20px; /* Space between stacked boxes */
    }
}

#notificationContainer {
            position: relative;
            z-index: 1000;
            margin: 10px 0; /* Adjust as needed */
        }
        .notification {
            background-color: #f8d7da; /* Red background for expired/low stock */
            color: #721c24; /* Dark red text */
            padding: 10px;
            border: 1px solid #f5c6cb;
            margin-bottom: 1px; /* Space between notifications */
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 600px;
            margin-right: 600px;
        }
        .close-btn {
            background: none;
            border: none;
            color: #721c24;
            cursor: pointer;
        }



    </style>
</head>
    <div class="header">
        
        <div class="title">Sales and Inventory System</div>

        <a href="#" class="logout-icon" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i>


        </a>
    </div>
    
    <div id="notificationContainer" style="position: relative; z-index: 1000;">
    <!-- Notifications will be dynamically inserted here -->
</div>


    
    <div class="sidebar">
    <div class="user-info-sidebar">
        <i class="fas fa-user user-icon"></i>
        <span class="user-text">ADMINISTRATOR</span>
    </div>
    
    <a href="dashboard.php" onclick="navigateTo('dashboard')">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    
    <a href="sales_transaction.php" onclick="navigateTo('sales_transaction')">
        <i class="fas fa-exchange-alt"></i>
        <span>Sales</span>
    </a>
    
    <a href="inventory.php" onclick="navigateTo('inventory')">
        <i class="fas fa-clipboard-list"></i>
        <span>Products</span>
    </a>
    
    
    <!-- User Menu -->
    <div class="user-menu">
    <a href="user_management.php" onclick="navigateTo('user_management')">
    <i class="fas fa-users"></i>
    <span>Cashiers</span>
</a>

<a href="#" class="admin-settings-icon" onclick="confirmSettings()">
    <i class="fas fa-cogs"></i>
            <span>Settings</span>
        </a>
    
    </div>
</div>

   <!-- Modal Structure -->
<div id="settingsModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Change Username and Password</h2>
        <form id="settingsForm" action="settings_process.php" method="post">
            <!-- Current Information Section -->
            <div class="section">
                <h3>Current Information</h3>
                <div class="form-group">
                    <label for="currentUsername">Current Username</label>
                    <input type="text" id="currentUsername" name="currentUsername" required>
                </div>
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <div class="password-container">
                        <input type="password" id="currentPassword" name="currentPassword" required>
                        <i class="fas fa-eye" id="toggleCurrentPassword" onclick="togglePasswordVisibility('currentPassword', 'toggleCurrentPassword')"></i>
                    </div>
                </div>
            </div>
            
            <!-- Divider -->
            <div class="section-divider"></div>
            
            <!-- New Information Section -->
            <div class="section">
                <h3>New Information</h3>
                <div class="form-group">
                    <label for="newUsername">New Username</label>
                    <input type="text" id="newUsername" name="newUsername" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <div class="password-container">
                        <input type="password" id="newPassword" name="newPassword" required>
                        <i class="fas fa-eye" id="toggleNewPassword" onclick="togglePasswordVisibility('newPassword', 'toggleNewPassword')"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <div class="password-container">
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <i class="fas fa-eye" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirmPassword', 'toggleConfirmPassword')"></i>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">Save Changes</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
        </form>
        <div id="responseMessage" style="display: none;"></div> <!-- For displaying messages -->
    </div>
</div>


<div class="content">

    <h1>Dashboard</h1>
    <div class="sales-container">
        <div class="box sales-box">
            <h3><i class="fas fa-dollar-sign icon-sales"></i> TOTAL SALES</h>
            <p id="totalSales">₱ <?php echo htmlspecialchars($totalSales); ?></p>
        </div>
        <div class="box sales-box">
            <h3><i class="fas fa-users icon-customers"></i> TOTAL CUSTOMERS</h3>
            <p id="totalCustomers"><?php echo htmlspecialchars($totalCustomers); ?></p>
        </div>
        <div class="box inventory-box">
            <h2><i class="fas fa-boxes icon-products"></i> PRODUCTS</h2>
            <p id="inventoryStock"><?php echo $totalProducts; ?></p>
        </div>
        <div class="box low-product-box">
            <h3><i class="fas fa-level-down-alt icon-stock-in"></i> STOCK IN</h>
            <p id="lowStocks"><?php echo htmlspecialchars($totalStockIn); ?></p>
        </div>
        <div class="box out-of-box">
            <h3><i class="fas fa-times-circle icon-stock-out"></i> STOCK OUT</h3>
            <p id="outOfStocks"><?php echo htmlspecialchars($totalSold); ?></p> <!-- Display total sold -->
        </div>
    <div class="box expired-products-box">
        <h3><i class="fas fa-exclamation-triangle icon-expired"></i>EXPIRED PRODUCTS</h3>
        <p id="totalExpiredProducts"><?php echo htmlspecialchars($totalExpiredProducts); ?></p>
    </div>

    <div class="box-containers ">
    <div class="box today-orders-box">
        <h2><i class="fas fa-calendar-day icon-orders"></i> Today's Orders</h2>
        <ul id="todayOrders">
    <?php if (!empty($todaysOrders)): ?>
        <?php foreach ($todaysOrders as $order): ?>
            <li><?php echo htmlspecialchars($order['product_name']); ?> - Quantity: <?php echo htmlspecialchars($order['totalSold']); ?>, Total: <?php echo htmlspecialchars($order['totalAmount']); ?></li>
        <?php endforeach; ?>
    <?php else: ?>
        <li>No orders today.</li>
    <?php endif; ?>
</ul>
    </div>
    
    <div class="box low-sales-box">
        <h2><i class="fas fa-chart-line icon-sales"></i> Low Sales Products</h2>
        <ul id="lowSalesProducts">
            <?php if (!empty($lowSalesProducts)): ?>
                <?php foreach ($lowSalesProducts as $product): ?>
                    <li><?php echo htmlspecialchars($product['product_name']); ?> - Sales: <?php echo htmlspecialchars($product['totalSales']); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No low sales products found.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>


                <!-- Add Canvas for Sales Graph -->
                <canvas id="weeklySalesGraph" width="300" height="100"></canvas>
                <canvas id="salesGraph" width="300" height="100"></canvas>
                <canvas id="annualSalesGraph" width="300" height="100"></canvas>

                
                

    <script>
       const expiredProducts = <?php echo json_encode($expiredProducts); ?>;
const lowStockProducts = <?php echo json_encode($lowStockProducts); ?>;
const lowSalesProducts = <?php echo json_encode($lowSalesProducts); ?>;
const noSalesProducts = <?php echo json_encode($noSalesProducts); ?>;
const outOfStockProducts = <?php echo json_encode($outOfStockProducts); ?>;

function showNotifications() {
    let notifications = [];

    expiredProducts.forEach(product => {
        notifications.push({
            message: `Expired Product: ${product.product_name}, Expired on: ${product.expiration_date}, Current Stock: ${product.current_stock || 0}`,
            link: `productDetails.html?id=${product.product_id}`
        });
    });

    lowStockProducts.forEach(product => {
        notifications.push({
            message: `Low Stock: Product ID: ${product.product_id}, ${product.product_name}, Only ${product.current_stock || 0} left.`,
            link: `productDetails.html?id=${product.product_id}`
        });
    });

      // Prepare low sales products notifications
      lowSalesProducts.forEach(product => {
            notifications.push({
                message: `Low Sales Product: ${product.product_name}, Sales this month: ${product.totalSales || 0}.`,
                link: `productDetails.html?id=${product.product_id}`
            });
        });

         // Add no sales products notifications
    noSalesProducts.forEach(product => {
        notifications.push({
            message: `No Sales Product: ${product.product_name}, No sales recorded.`,
            link: `productDetails.html?id=${product.product_id}`
        });
    });

    outOfStockProducts.forEach(product => {
        notifications.push({
            message: `Out of Stock: Product ID: ${product.product_id}, ${product.product_name}.`,
            link: `productDetails.html?id=${product.product_id}`
        });
    });

    notifications.forEach(notification => {
        displaySingleNotification(notification);
    });
}

function displaySingleNotification(notification) {
    const notificationContainer = document.getElementById('notificationContainer');

    const notificationElement = document.createElement('div');
    notificationElement.className = 'notification';
    notificationElement.innerHTML = `
        <a href="${notification.link}" style="color: black; text-decoration: none;">${notification.message}</a>
        <button class="close-btn" onclick="this.parentElement.remove();" style="margin-left: 10px;">&times;</button>
    `;
    notificationContainer.appendChild(notificationElement);
}

// Call showNotifications to display the notifications
showNotifications();

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php'; // Replace with your actual logout URL
            }
        }

        document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'settings_process.php', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            var responseMessage = document.getElementById('responseMessage');
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                responseMessage.textContent = response.message;
                responseMessage.style.color = response.success ? 'green' : 'red';
                responseMessage.style.display = 'block';
                if (response.success) {
                    setTimeout(closeModal, 2000); // Optionally close the modal after a delay
                }
            } else {
                responseMessage.textContent = 'An error occurred.';
                responseMessage.style.color = 'red';
                responseMessage.style.display = 'block';
            }
        };

        xhr.send(formData);
    });

    function closeModal() {
        document.getElementById('settingsModal').style.display = 'none';
    }

    function confirmSettings() {
        document.getElementById('settingsModal').style.display = 'block';
    }

    window.onclick = function(event) {
        if (event.target === document.getElementById('settingsModal')) {
            closeModal();
        }
    }

    function togglePasswordVisibility(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(iconId);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

// Fetch the PHP variables into JavaScript
const labels = <?php echo json_encode($labels); ?>; // Month labels
const data = <?php echo json_encode($data); ?>; // Total sales for each month
const weeklyLabels = <?php echo json_encode($weeklyLabels); ?>; // Use this for weekly labels
const weeklyData = <?php echo json_encode($weekly_data); ?>; // Total sales for each week
const annualLabels = <?php echo json_encode($annualLabels); ?>; // Labels for years
const annualData = <?php echo json_encode($annualData); ?>; // Sales data for each year
const ctx = document.getElementById('salesGraph').getContext('2d');


// Weekly Sales Chart
const weeklyCtx = document.getElementById('weeklySalesGraph').getContext('2d');
const weeklySalesChart = new Chart(weeklyCtx, {
    type: 'bar',
    data: {
        labels: weeklyLabels, // Use the correctly formatted labels
        datasets: [{
            label: 'Weekly Sales',
            data: weeklyData,
            fill: false,
            backgroundColor: 'rgba(153, 102, 255, 0.5)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 2,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales Amount'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Week'
                }
            }
        }
    }
});

const salesChart = new Chart(ctx, {
    type: 'bar', // Change to 'line'
    data: {
        labels: labels,
        datasets: [{
            label: 'Monthly Sales',
            data: data,
            fill: false, // Do not fill under the line
            backgroundColor: 'rgba(54, 162, 235, 0.5)', 
            borderColor: 'rgba(75, 192, 192, 1)', // Line color
            borderWidth: 2, // Width of the line
            tension: 0.1 // Smooth the line
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true, // Start y-axis at zero
                title: {
                    display: true,
                    text: 'Sales Amount' // Y-axis title
                },
                ticks: {
                    stepSize: 100, // Count by 100s
                    callback: function(value) { return value; } // Show the value as is
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Month' // X-axis title
                }
            }
        }
    }
});

// Annual Sales Chart
const annualCtx = document.getElementById('annualSalesGraph').getContext('2d');
const annualSalesChart = new Chart(annualCtx, {
    type: 'bar',
    data: {
        labels: annualLabels,
        datasets: [{
            label: 'Annual Sales',
            data: annualData,
            backgroundColor: 'rgba(255, 206, 86, 0.5)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales Amount'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Year'
                }
            }
        }
    }
});

function navigateTo(page) {
        // Implement your navigation logic here
        console.log("Navigating to " + page);
        // Example: You could load content dynamically or redirect
        window.location.href = page + ".php"; // Redirecting to the page
    }
</script>
</body>
</html>
