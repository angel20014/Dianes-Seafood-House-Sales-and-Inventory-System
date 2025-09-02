<?php
 session_start();

 // Check if the user is logged in by verifying session variables
 if (!isset($_SESSION['user_id'])) {
     // If the user is not logged in, redirect to the login page
     header("Location: login.php");
     exit;
 }
 
include('db.php');

// Get current date
$current_date = date('Y-m-d');

// Move expired products to the expired_products table
$moveExpiredQuery = "
    INSERT INTO expired_products (product_id, product_name, category, expiration_date, expired_on, current_stock)
    SELECT product_id, product_name, category, expiration_date, '$current_date', current_stock
    FROM products
    WHERE expiration_date <= CURDATE() AND product_id NOT IN (SELECT product_id FROM expired_products)
";
$conn->query($moveExpiredQuery);

// Remove expired products from the main products table
$removeExpiredQuery = "DELETE FROM products WHERE expiration_date <= CURDATE()";
$conn->query($removeExpiredQuery);

// Fetch expired products
$query = "SELECT * FROM expired_products ORDER BY expired_on DESC";
$expiredProductsResult = $conn->query($query);


// Fetch products from the database
$query = "SELECT * FROM products";
$result = $conn->query($query);

// Fetch stock entries from the database
$stock_query = "SELECT * FROM stock";
$stock_result = $conn->query($stock_query);

// Fetch products and their current stock levels
$productQuery = "
    SELECT p.product_id, p.product_name, s.current_stock 
    FROM products p 
    JOIN stock s ON p.product_id = s.product_id
";
$productResult = $conn->query($productQuery);

// Check for query success
if (!$productResult) {
    die("Error fetching products: " . $conn->error);
}
// Create an associative array for stock levels
$stock_levels = [];
while ($row = $productResult->fetch_assoc()) {
    $stock_levels[$row['product_id']] = [
        'name' => $row['product_name'],
        'current_stock' => $row['current_stock']
    ];
}

    // Fetch stock levels to display
$stock_levels_query = "SELECT product_id, current_stock FROM stock";
$stock_levels_result = $conn->query($stock_levels_query);
$stock_levels_array = [];

if ($stock_levels_result) {
    while ($row = $stock_levels_result->fetch_assoc()) {
        $stock_levels_array[$row['product_id']] = $row['current_stock'];
    }
}

// Fetch categories and their products
$categoryQuery = "SELECT DISTINCT category FROM products ORDER BY category";
$categoryResult = $conn->query($categoryQuery);

// Fetch products
$productQuery = "SELECT product_id, product_code, product_name, category FROM products ORDER BY category";
$productResult = $conn->query($productQuery);
// Function to add a new product and its category

// Fetch categories from the category table
$categorySql = "SELECT * FROM category ORDER BY category";
$categoryResult = $conn->query($categorySql);

// Fetch distinct categories from products to ensure all categories are displayed
$distinctCategoryQuery = "SELECT DISTINCT category FROM products";
$distinctCategoryResult = $conn->query($distinctCategoryQuery);

// Combine results into an array for display
$categories = [];

// Populate from the category table
if ($categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[$row['category']][] = [
            'product_code' => $row['product_code'],
            'product_name' => $row['product_name'],
        ];
    }
}

// Populate from the distinct categories in products
if ($distinctCategoryResult->num_rows > 0) {
    while ($row = $distinctCategoryResult->fetch_assoc()) {
        $category = $row['category'];
        if (!isset($categories[$category])) {
            $categories[$category] = []; // Initialize empty array if no products found
        }
    }
}

// Fetch sales data to update stock using product_name
$salesQuery = "SELECT product_name, SUM(quantity) AS total_sold FROM sales GROUP BY product_name";
$salesResult = $conn->query($salesQuery);

if ($salesResult) {
    // Update stock levels based on sales
    while ($sale = $salesResult->fetch_assoc()) {
        $product_name = $sale['product_name'];
        $quantity_sold = $sale['total_sold'];

        // Get the product_id based on product_name
        $productQuery = "SELECT product_id FROM products WHERE product_name = ?";
        $stmt = $conn->prepare($productQuery);
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $productResult = $stmt->get_result();

        if ($productResult && $productRow = $productResult->fetch_assoc()) {
            $product_id = $productRow['product_id'];

            // Update the stock in the database
            $update_stock_sql = "UPDATE stock SET current_stock = current_stock - ? WHERE product_id = ?";
            $updateStmt = $conn->prepare($update_stock_sql);
            $updateStmt->bind_param("ii", $quantity_sold, $product_id);
            $updateStmt->execute();
        }
    }
}

    // Handle form submission for report generation
if (isset($_POST['generateReport'])) {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];

    // Fetch stock records based on the date range
    $reportSql = "
    SELECT s.stock_id, p.product_id, p.product_name, s.stock_in, s.stock_out, 
           (COALESCE(SUM(s.stock_in), 0) - COALESCE(SUM(s.stock_out), 0)) AS current_stock, 
           s.date
    FROM stock s
    JOIN products p ON s.product_id = p.product_id
    WHERE s.date BETWEEN '$startDate' AND '$endDate'
    GROUP BY s.stock_id, p.product_id, p.product_name, s.date
    ";

    $reportResult = $conn->query($reportSql);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        /* CSS for header */
        /* CSS for header */
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

.header .logout-icon,
.header .admin-settings-icon {
    color: white;
    font-size: 24px;
    margin: 0 20px;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: color 0.3s;
}

.header .logout-icon:hover {
    color: #cc0000;
}
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

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    max-width: 600px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.modal-content h2 {
    margin-top: 0;
}

/* Section styles */
.section {
    margin-bottom: 20px;
}

.section h3 {
    margin-top: 0;
}

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

/* CSS for Product List header */
.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.header-container h3 {
    margin: 0;
    font-size: 20px;
}

.header-container button {
    padding: 10px 15px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    background-color: grey;
    color: white;
    transition: background-color 0.3s;
}

.header-container button:hover {
    background-color: #0056b3;
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
/* CSS for search container */
.search-container {
    display: flex;
    align-items: center;
    margin: 0 20px;
}

.search-container input[type="text"] {
    padding: 8px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    flex: 1; /* Takes up remaining space */
    box-sizing: border-box;
}

.search-container .search-icon {
    font-size: 30px;
    color: #888;
    margin-left: 10px;
    cursor: pointer;
    transition: color 0.3s;
}

.search-container .search-icon:hover {
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
    margin-left: 259px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: left;
    margin-top: 0;
    max-width: calc(100% - 250px);
}

.button-container {
    display: flex;
    margin-bottom: 50px;
    gap: 10px;
}

.button-container button {
    padding: 10px 20px;
    font-size: 18px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    background-color: #007bff;
    color: white;
    transition: background-color 0.3s;
}

.button-container button:hover {
    background-color: #0056b3;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
    font-size: 16px;
}

th {
    background-color: lightgray;
}

.no-products {
    color: red;
    margin-top: 20px;
}


/* Modal Background */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.4); /* Black background with opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
    max-width: 600px; /* Maximum width */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
}

/* Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Form Elements */
form {
    display: flex;
    flex-direction: column;
}

label {
    margin-top: 10px;
    font-weight: bold;
}

input[type="text"],
input[type="number"],
input[type="date"],
select {
    margin-top: 5px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}

/* Responsive Design */
@media screen and (max-width: 600px) {
    .modal-content {
        width: 90%; /* Make modal smaller on small screens */
    }
}

#categoryTable {
    width: 95%;
    border-collapse: collapse;
    margin-left: 10px;
    
}

#categoryTable th, #categoryTable td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
    
}

.category-header th {
    background-color: lightgray;
    text-align: center;
}
/* Add this to prevent content overflow */
.category-header th, .category-header td {
    overflow: hidden; /* Prevent overlap */
}

.no-products {
    color: red;
    margin-top: 20px;
}

#stockTable {
    width: 98s%;
    border-collapse: collapse;
    margin-left: 10px;
    
}

/* The Modal (background) */
/* Modal container */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 999; /* Ensures the modal is on top of other elements */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4); /* Black background with transparency */
}

/* Modal content */
.modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
}


/* The Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

    /* Styling for the expired products section */
#expiredProducts {
    margin: 20px;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
}

/* Heading styles */
#expiredProducts h3 {
    color: #333;
    font-size: 24px;
    margin-bottom: 15px;
}

/* Table styles */
#expiredProductsTable table {
    width: 100%;
    border-collapse: collapse;
}

#expiredProductsTable th, 
#expiredProductsTable td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}

#expiredProductsTable th {
    background-color: red;
    color: white;
}

#expiredProductsTable tr:nth-child(even) {
    background-color: #f2f2f2;
}

#expiredProductsTable tr:hover {
    background-color: #e1e1e1;
}

/* No expired products message */
#expiredProductsTable p {
    font-size: 16px;
    color: #666;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 16px;
    text-align: left;
}
table, th, td {
    border: 1px solid #dddddd;
    padding: 8px;
}
th {
    background-color: #f2f2f2;
}
tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Form Styles */
/* Form Styles */
#filterForm {
    margin-bottom: 20px;                /* Space below the form */
}

/* Container for date inputs and submit button */
.date-container {
    display: flex;                      /* Flex layout for all items */
    align-items: center;                /* Center align items vertically */
    font-size: 16px;                    /* Base font size */
}

/* Label Styles */
#filterForm label {
    margin-right: 5px;                  /* Space to the right of labels */
    font-weight: bold;                  /* Make labels bold */
}

/* Input Styles */
#filterForm input[type="date"] {
    margin-right: 10px;                 /* Space to the right of date inputs */
    font-size: 18px;                    /* Font size for date inputs */
    padding: 5px;                       /* Padding inside input for better touch targets */
    border: 1px solid #ccc;             /* Light border for inputs */
    border-radius: 4px;                 /* Rounded corners for inputs */
}

/* Submit Button Styles */
#filterForm input[type="submit"] {
    font-size: 18px;                    /* Font size for the button */
    margin-left: 20px;                  /* Space to the left of the button */
    padding: 5px 10px;                  /* Padding for the button */
    background-color: #4CAF50;          /* Green background color */
    color: white;                       /* White text color */
    border: none;                       /* No border */
    border-radius: 4px;                 /* Rounded corners */
    cursor: pointer;                     /* Pointer cursor on hover */
    transition: background-color 0.3s;  /* Smooth transition for background color */
}

/* Button Hover Effect */
#filterForm input[type="submit"]:hover {
    background-color: #45a049;          /* Darker green on hover */
}



    </style>
</head>
<div>

<div class="header">
        <div class="title">Sales and Inventory System</div>

        <a href="#" class="logout-icon" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i>


        </a>
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
<div class="content">
    <div class="header-container" style="display: flex; align-items: center;">
        <h1 style="margin: 0;">
            INVENTORY
        </h1>

        <form method="post" id="filterForm" onsubmit="event.preventDefault(); printStockReport();">
    <div class="date-container">
        <label for="startDate">Start Date:</label>
        <input type="date" id="startDate" name="startDate" required>
        <label for="endDate">End Date:</label>
        <input type="date" id="endDate" name="endDate" required>
        <input type="submit" name="generateReport" value=" Report">
    </div>
</form>

    </div>

   

        <div class="button-container">
            <button onclick="showSection('productList')">Product List</button>
            <button onclick="showSection('categoryView')">By Category</button>
            <button onclick="showSection('stock')">Stock</button>
            <button onclick="checkExpiredProducts()">Check Expired Products</button> 
        </div>

        <div class="search-container">
        <input type="text" id="globalSearch" placeholder="Search ...">
        <i class="fas fa-search search-icon"></i>
    </div>

     
  <!-- Product List Section -->
<div id="productList" class="section" style="display: block;">
    <div class="header-container">
        <h3>PRODUCT LIST</h3>
        <button onclick="showAddProductModal()">+ Product</button>
    </div>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Code</th>
                <th>Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Expiration Date</th>
                <th>Tools</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Include database connection
        include 'db.php';

        // Fetch product stock information
        $productsSql = "
        SELECT p.product_id, p.product_code, p.image, p.product_name, p.category,
               p.unit, p.price, p.expiration_date, 
               COALESCE(SUM(s.stock_in), 0) AS total_stock_in,
               COALESCE(SUM(s.stock_out), 0) AS total_stock_out,
               COALESCE(sales.total_sold, 0) AS total_sold
        FROM products p
        LEFT JOIN stock s ON p.product_id = s.product_id
        LEFT JOIN (
            SELECT product_name, SUM(quantity) AS total_sold
            FROM sales
            WHERE quantity != 'canceled'  -- Exclude canceled sales
            GROUP BY product_name
        ) AS sales ON p.product_name = sales.product_name   
        WHERE p.expiration_date > CURDATE()  -- Only show products that are not expired
        GROUP BY p.product_id, p.product_code, p.product_name, p.category, p.unit, p.price, p.expiration_date
        ";
        
        // Fetch results
        $productsResult = $conn->query($productsSql);
        if ($productsResult->num_rows > 0) {
            while ($row = $productsResult->fetch_assoc()) {
                // Calculate current stock by subtracting stock-out but only accounting for non-canceled sales
                $current_stock = $row['total_stock_in'] - $row['total_stock_out'];
        
                // Update the product's current stock if there's a mismatch
                $updateSql = "
                    UPDATE products
                    SET current_stock = $current_stock
                    WHERE product_id = {$row['product_id']}
                ";

    
                echo "<tr>
                    <td>{$row['product_id']}</td>
                    <td>{$row['product_code']}</td>
                    <td><img src='uploads/{$row['image']}' alt='Image' style='width:50px;height:50px;'></td>
                    <td>{$row['product_name']}</td>
                    <td>{$row['category']}</td>
                    <td id='stock-{$row['product_id']}'>" . htmlspecialchars($current_stock) . "</td>
                    <td>{$row['unit']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['expiration_date']}</td>
                    <td>
                        <button onclick='editProduct({$row['product_id']})' class='action-btn'>
                            <i class='fas fa-edit'></i>
                        </button>
                        <button onclick='viewProduct({$row['product_id']})' class='action-btn'>
                            <i class='fas fa-eye'></i>
                        </button>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='8' class='no-products'>No products available</td></tr>";
        }
        
        ?>
        </tbody>
    </table>
</div>

       <!-- Category View Section -->
<div id="categoryView" class="section" style="display: none;">
    
    <form method="POST" action="record_category.php">
        <table id="categoryTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product Code</th>
                    <th>Product Name</th>
                </tr>
            </thead>
            <tbody>
            <?php
          // Fetch categories and exclude categories with only expired products
$categorySql = "
SELECT DISTINCT p.category
FROM products p
WHERE p.expiration_date > CURDATE()  -- Exclude expired products in categories
ORDER BY p.category
";
            $categoryResult = $conn->query($categorySql);

            if ($categoryResult->num_rows > 0) {
                while ($categoryRow = $categoryResult->fetch_assoc()) {
                    $category = $categoryRow['category'];

                    $productQuery = "SELECT product_code, product_name FROM products WHERE category = '$category'";
                    $productResult = $conn->query($productQuery);

                    if ($productResult->num_rows > 0) {
                        echo "<tr class='category-header'>
                            <th colspan='3'>" . htmlspecialchars($category) . "</th>
                        </tr>";
                        while ($productRow = $productResult->fetch_assoc()) {
                            echo "<tr>
                                <td>
                                    <input type='hidden' name='categories[]' value='$category'>
                                    <input type='hidden' name='product_codes[]' value='{$productRow['product_code']}'>
                                    <input type='hidden' name='product_names[]' value='{$productRow['product_name']}'>
                                    " . htmlspecialchars($category) . "
                                </td>
                                <td>" . htmlspecialchars($productRow['product_code']) . "</td>
                                <td>" . htmlspecialchars($productRow['product_name']) . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No products found for this category.</td></tr>";
                    }
                }
            } else {
                echo "<tr><td colspan='3'>No categories found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </form>
</div>

<!-- Stock Section -->
<div id="stock" class="section" style="display: none;">
    <table id="stockTable">
        <thead>
            <tr>
                    <th>Stock ID</th>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Date</th> <!-- New Date Column -->
                    <th>Tools</th>
            </tr>
        </thead>
        <tbody>
        <?php
        include 'db.php';

        // Fetch product stock information
        $productsSql = "
        SELECT p.product_id, p.product_code, p.product_name, p.category,
               p.unit, p.price, p.expiration_date, 
               COALESCE(SUM(s.stock_in), 0) AS total_stock_in,
               COALESCE(SUM(s.stock_out), 0) AS total_stock_out,
               COALESCE(sales.total_sold, 0) AS total_sold,
               MAX(s.date) AS stock_date  -- Fetch the most recent stock date
        FROM products p
        LEFT JOIN stock s ON p.product_id = s.product_id
        LEFT JOIN (
            SELECT product_name, SUM(quantity) AS total_sold
            FROM sales
            WHERE quantity != 'canceled'  -- Exclude canceled sales
            GROUP BY product_name
        ) AS sales ON p.product_name = sales.product_name
         WHERE p.expiration_date > CURDATE()  -- Only show products that are not expired
        GROUP BY p.product_id, p.product_code, p.product_name, p.category, p.unit, p.price, p.expiration_date
        ";

        // Fetch results
        $productsResult = $conn->query($productsSql);
        if ($productsResult->num_rows > 0) {
            while ($row = $productsResult->fetch_assoc()) {
                // Calculate current stock based on existing logic
                $current_stock = $row['total_stock_in'] - $row['total_stock_out'];

                // Update the product's current stock if there's a mismatch
                $updateSql = "
                    UPDATE products
                    SET current_stock = $current_stock
                    WHERE product_id = {$row['product_id']}
                ";
                $conn->query($updateSql);

                echo "<tr data-product-id='{$row['product_id']}'>
                        <td class='product-id'>" . htmlspecialchars($row['product_id']) . "</td>
                        <td class='product-code'>" . htmlspecialchars($row['product_code']) . "</td>
                        <td class='product-name'>" . htmlspecialchars($row['product_name']) . "</td>
                        <td class='stock-in'>" . htmlspecialchars($row['total_stock_in']) . "</td>
                        <td class='stock-out'>" . htmlspecialchars($row['total_stock_out']) . "</td>
                        <td class='current-stock'>" . htmlspecialchars($current_stock) . "</td>
                        <td class='status'>" . ($current_stock > 0 ? 'Available' : 'Unavailable') . "</td>
                        <td class='stock-date'>" . htmlspecialchars($row['stock_date']) . "</td> <!-- Display stock date -->
                        <td>
                            <button onclick='showAddStockModal(" . intval($row['product_id']) . ", " . intval($current_stock) . ")' class='action-btn'>
                                <i class='fas fa-plus'></i>
                            </button>
                            <button onclick='editStock(" . intval($row['product_id']) . ")' class='action-btn'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button onclick='viewStock(" . intval($row['product_id']) . ")' class='action-btn'>
                                <i class='fas fa-eye'></i>
                            </button>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='9' class='no-products'>No products available</td></tr>";
        }
        $productsResult->free();
        $conn->close();
        ?>
        </tbody>
    </table>
</div>



<div id="expiredProducts" class="section" style="display:none;">
    <div style="display: flex; align-items: center; margin-left: auto; cursor: pointer; margin-top: 50px;" onclick="printInventory()">
        <i class="fas fa-print" style="margin-left: 1300px; font-size: 25px;"></i>
        <span style="margin-left: 4px; font-size: 25px;">Print</span>
    </div>
    <h3>Expired Products</h3>
    <table id="expiredProductsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Expired </th>
                <th>Current Stock</th>
            </tr>
        </thead>
        <tbody>
            <!-- Expired products will be populated here -->
        </tbody>
    </table>
</div>



<div id="viewProductModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewProductModal()">&times;</span>
        <h2>Product Details</h2>
        <p><strong>Product Code:</strong> <span id="viewProductCode"></span></p>
        <p><strong>Product Name:</strong> <span id="viewProductName"></span></p>
        <p><strong>Category:</strong> <span id="viewCategory"></span></p>
        <p><strong>Unit:</strong> <span id="viewUnit"></span></p>
        <p><strong>Price:</strong> <span id="viewPrice"></span></p>
        <p><strong>Expiration Date:</strong> <span id="viewExpirationDate"></span></p>
        
       
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

   <!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddProductModal()">&times;</span>
        <h2>Add Product</h2>
        <form id="addProductForm">
            <label for="productCode">Product Code</label>
            <input type="text" id="productCode" name="productCode" required>

            <label for="productImage">Product Image</label>
            <input type="file" id="productImage" name="productImage" accept="image/*" required>

            
            <label for="productName">Product Name</label>
            <input type="text" id="productName" name="productName" required>
            
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <option value="Fish">FISH</option>
                <option value="Pork">PORK</option>
                <option value="Chicken">CHICKEN</option>
                <option value="Seafood">SEAFOODS</option>
                <option value="Softdrinks">SOFTDRINKS</option>
                <option value="Others">OTHERS</option>
            </select>
            
            <label for="unit">Unit</label>
            <select id="unit" name="unit" required>
                <option value="">Select a unit</option>
                <option value="gram">gram</option>
                <option value="gram">set</option>
                <option value="kls">kls</option>
                <option value="can">can</option>
                <option value="pack">pack</option>
                <option value="bottle">bottle</option>
                <option value="pcs">pcs</option>
            </select>
            
            <label for="price">Price</label>
            <input type="number" id="price" name="price" required step="0.01">
            
            <label for="expirationDate">Expiration Date</label>
            <input type="date" id="expirationDate" name="expirationDate" required>
            
            <button type="submit">Add Product</button>
        </form>
    </div>
</div>



                        <!-- Add Stock Modal -->
<div id="addStockModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddStockModal()">&times;</span>
        <h2>Add Stock</h2>
        <form id="addStockForm">
            <input type="hidden" id="stockProductId" name="product_id"> <!-- Ensure the name matches the server-side script -->
            <label for="stockIn">Stock In</label>
            <input type="number" id="stockIn" name="stock_in" required step="0.01">
            <button type="submit">Add Stock</button>
        </form>
    </div>
</div>


<!-- Edit Product Modal -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditProductModal()">&times;</span>
        <h2>Edit Product</h2>
        <form id="editProductForm">
            <input type="hidden" id="editProductId" name="productId">

            <label for="editProductCode">Product Code</label>
            <input type="text" id="editProductCode" name="productCode" required>

            <label>Product Image</label>
            <img id="editProductImage" src="" alt="Product Image" style="width:100px;height:100px;display:none;">
            <input type="file" id="productImage" name="productImage" accept="image/*">

            <label for="editProductName">Product Name</label>
            <input type="text" id="editProductName" name="productName" required>

            <label for="editCategory">Category</label>
            <select id="editCategory" name="category" required>
                <option value="">Select a category</option>
                <option value="Fish">FISH</option>
                <option value="Pork">PORK</option>
                <option value="Chicken">CHICKEN</option>
                <option value="Seafood">SEAFOODS</option>
                <option value="Softdrinks">SOFTDRINKS</option>
                <option value="Others">OTHERS</option>
            </select>

            <label for="editUnit">Unit</label>
            <select id="editUnit" name="unit" required>
                <option value="">Select a unit</option>
                <option value="gram">gram</option>
                <option value="gram">set</option>
                <option value="kls">kls</option>
                <option value="can">can</option>
                <option value="pack">pack</option>
                <option value="bottle">bottle</option>
                <option value="pcs">pcs</option>
            </select>

            <label for="editPrice">Price</label>
            <input type="number" id="editPrice" name="price" required step="0.01">

            <label for="editExpirationDate">Expiration Date</label>
            <input type="date" id="editExpirationDate" name="expirationDate" required>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<!-- Edit Stock Modal -->
<div id="editStockModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h4>Edit Stock</h4>
        <span id="editModalProductName"></span>
        <input type="hidden" id="editModalProductId"> <!-- Hidden field to store product_id -->
        
        <label for="modalStockIn">Stock In:</label>
        <input type="number" id="modalStockIn" name="stock_in" value="">

        <button id="saveStockInBtn" onclick="saveStockIn()">Save</button>
        <button onclick="closeModal('editStockModal')">Cancel</button>
    </div>
</div>



<!-- View Stock Modal -->
<div id="viewStockModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal('viewStockModal')">&times;</span>
        <h2>View Stock for <span id="viewModalProductName"></span></h2> <!-- Product name will be shown here -->
    
        <p><strong>Stock In:</strong> <span id="viewStockIn"></span></p> <!-- Display Stock In -->
        <p><strong>Stock Out:</strong> <span id="viewStockOut"></span></p>
        <p><strong>Current Stock:</strong> <span id="viewCurrentStock"></span></p>
        <p><strong>Status:</strong> <span id="viewProductStatus"></span></p>
    </div>
</div>


    <script>
window.onload = function() {
    checkExpiredProducts();  // Automatically check and load expired products on page load
};

function confirmDelete(productId) {
        if (confirm('Are you sure you want to delete this stock item?')) {
            // Make an AJAX request to delete the stock item
            $.ajax({
                url: 'delete_stock.php', // URL to the delete script
                type: 'POST',
                data: { product_id: productId },
                success: function(response) {
                    alert(response); // Handle response from the server
                    location.reload(); // Reload the page to reflect changes
                },
                error: function() {
                    alert('Error deleting stock item.');
                }
            });
        }
    }
    
     function printStockReport() {
    // Save the original content of the stock section
    const originalContent = document.body.innerHTML;

    // Get the stock table
    const stockTable = document.getElementById('stockTable').outerHTML;

    // Create a temporary print container
    const printContainer = document.createElement('div');
    printContainer.innerHTML = `
        <h1>Stock Report</h1>
        <table>${stockTable}</table>
    `;
    
    // Append the print container to the body
    document.body.innerHTML = printContainer.innerHTML;

    // Print the current page
    window.print();

    // Restore the original content after printing
    document.body.innerHTML = originalContent;
}


        function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => {
        section.style.display = 'none'; // Hide all sections
    });
    document.getElementById(sectionId).style.display = 'block'; // Show selected section
}

        function showAddProductModal() {
            document.getElementById('addProductModal').style.display = 'block';
        }

        function closeAddProductModal() {
            document.getElementById('addProductModal').style.display = 'none';
        }

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        // Handle Add Product form submission
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('add_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        alert(result);
        closeAddProductModal();
        // Refresh the product list here if needed
    })
    .catch(error => console.error('Error:', error));
});
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalSearch');

    // Function to show the selected section
    function showSection(sectionId) {
        document.querySelectorAll('.section').forEach(section => {
            section.style.display = (section.id === sectionId) ? 'block' : 'none';
        });
        // Clear search input when switching sections
        searchInput.value = '';
        searchInput.focus();
        filterRows(sectionId); // Apply filter to the newly visible section
    }

    // Function to filter rows based on the search input
    function filterRows(sectionId) {
        const table = document.querySelector(`#${sectionId} table`);
        const rows = table.querySelectorAll('tbody tr');
        const searchValue = searchInput.value.toLowerCase();
        
        rows.forEach(row => {
            let display = 'none';
            Array.from(row.cells).forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchValue)) {
                    display = '';
                }
            });
            row.style.display = display;
        });
    }

    // Event listener for search input
    searchInput.addEventListener('input', function() {
        // Get the currently visible section
        const visibleSection = document.querySelector('.section[style*="display: block;"]');
        if (visibleSection) {
            filterRows(visibleSection.id);
        }
    });

    // Initialize the first visible section (optional)
    showSection('productList'); // or 'categoryView' or 'stock'
});
 
 


function showAddStockModal(productId, currentStock) {
    // Your JavaScript function to show the modal
}

function showAddProductModal() {
    document.getElementById('addProductModal').style.display = 'block';
}

function closeAddProductModal() {
    document.getElementById('addProductModal').style.display = 'none';
}

function showAddStockModal(productId, currentStock) {
    document.getElementById('stockProductId').value = productId;
    document.getElementById('addStockModal').style.display = 'block';
}

function closeAddStockModal() {
    document.getElementById('addStockModal').style.display = 'none';
}

function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.style.display = 'none';
    });

    // Show the requested section
    document.getElementById(sectionId).style.display = 'block';
}

function showEditProductModal(productId) {
    // Fetch product details and populate the form
    fetch(`get_product.php?product_id=${productId}`)
    .then(response => response.json())
    .then(data => {
        document.getElementById('editProductId').value = data.product_id;
        document.getElementById('editProductCode').value = data.product_code;
        document.getElementById('editProductName').value = data.product_name;
        document.getElementById('editCategory').value = data.category;
        document.getElementById('editUnit').value = data.unit;
        document.getElementById('editPrice').value = data.price;
        document.getElementById('editExpirationDate').value = data.expiration_date;

        // Set the product image
        const imageElement = document.getElementById('editProductImage');
        if (data.image) {
            imageElement.src = data.image; // Ensure the image path is correct
            imageElement.style.display = 'block'; // Make the image visible
        } else {
            imageElement.style.display = 'none'; // Hide if no image
        }

        document.getElementById('editProductModal').style.display = 'block';
    })
    .catch(error => console.error('Error:', error));
}



function editProduct(productId) {
    // Fetch product details and populate the form
    fetch(`get_product.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                document.getElementById('editProductId').value = product.product_id;
                document.getElementById('editProductCode').value = product.product_code;
                document.getElementById('editProductName').value = product.product_name;
                document.getElementById('editCategory').value = product.category;
                document.getElementById('editUnit').value = product.unit;
                document.getElementById('editPrice').value = product.price;
                document.getElementById('editExpirationDate').value = product.expiration_date;
                
                document.getElementById('editProductModal').style.display = 'block';
            } else {
                alert('Failed to load product details.');
            }
        })
        .catch(error => console.error('Error:', error));
}

document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('edit_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        alert(result);
        closeEditProductModal();
        // Optionally, you can refresh the product list here
    })
    .catch(error => console.error('Error:', error));
});

function closeEditProductModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

   // Function to show product details
function viewProduct(productId) {
    fetch(`get_product.php?product_id=${productId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const product = data.product;
            // Populate product details
            document.getElementById('viewProductCode').textContent = product.product_code;
            document.getElementById('viewProductName').textContent = product.product_name;
            document.getElementById('viewCategory').textContent = product.category;
            document.getElementById('viewUnit').textContent = product.unit;
            document.getElementById('viewPrice').textContent = product.price;
            document.getElementById('viewExpirationDate').textContent = product.expiration_date;

            // Set the product image
            const imageElement = document.getElementById('viewProductImage');
            if (product.image) {
                imageElement.src = product.image; // Ensure the image path is correct
                imageElement.style.display = 'block'; // Make the image visible
            } else {
                imageElement.style.display = 'none'; // Hide if no image
            }

            document.getElementById('viewProductModal').style.display = 'block';
        } else {
            alert('Failed to load product details.');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Function to close the View Product Modal
function closeViewProductModal() {
    document.getElementById('viewProductModal').style.display = 'none';
}

// Load product list on page load
window.onload = function() {
    loadProductList();
}


document.addEventListener('DOMContentLoaded', function() {
    // Function to show the Add Stock modal
    function showAddStockModal(productId, currentStock) {
        document.getElementById('stockProductId').value = productId; // Set product ID in the hidden field
        document.getElementById('addStockModal').style.display = 'block'; // Show the modal
    }

    // Function to close the Add Stock modal
    function closeAddStockModal() {
        document.getElementById('addStockModal').style.display = 'none'; // Hide the modal
    }

   // Handle Add Stock form submission
document.getElementById('addStockForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent the default form submission

    const formData = new FormData(this); // Create FormData object from the form

    fetch('add_stock.php', { // URL of the PHP script to handle the stock addition
        method: 'POST', // HTTP method
        body: formData // Form data to be sent
    })
    .then(response => response.json()) // Expect JSON response
    .then(data => {
        if (data.success) {
            alert('Stock added successfully'); // Display success message
            const productId = formData.get('product_id'); // Get product ID from form data
            const addedStock = parseInt(formData.get('stock_in')); // Get added stock from form data

            // Update the stock displayed on the page
            updateStockDisplay(productId, addedStock);

            closeAddStockModal(); // Close the modal
        } else {
            alert('Failed to add stock: ' + data.message); // Display error message
        }
    })
    .catch(error => {
        console.error('Error:', error); // Log any errors
        alert('An error occurred: ' + error.message); // Display error to user
    });
});

// Function to update the displayed stock for a specific product
function updateStockDisplay(productId, addedStock) {
    const stockCell = document.getElementById(`stock-${productId}`); // Get the stock cell by product ID
    if (stockCell) {
        const currentStock = parseInt(stockCell.textContent) || 0; // Get current stock and ensure it's a number
        stockCell.textContent = currentStock + addedStock; // Update the displayed stock
    }
}


function fetchUpdatedStock(productId) {
    // Use AJAX to get updated stock and update the table
    $.ajax({
        url: 'fetch_stock.php', // PHP file to fetch current stock
        type: 'GET',
        data: { product_id: productId },
        success: function(data) {
            // Update the stock in your table
            $('#stock-' + productId).text(data.current_stock);
        }
    });
}


    // Fetch stock data and update the table
    function fetchStockData() {
        fetch('fetch_stock.php') // URL to fetch stock data
            .then(response => response.json())
            .then(data => {
                const tableBody = document.querySelector('#stockTable tbody');
                tableBody.innerHTML = ''; // Clear existing rows

                if (data.length > 0) {
                    data.forEach(stock => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${stock.product_id}</td>
                            <td>${stock.stock_in}</td>
                            <td>${stock.stock_out}</td>
                            <td>${stock.current_stock}</td>
                            <td><button onclick="showAddStockModal(${stock.product_id}, ${stock.current_stock})">Add Stock</button></td>
                        `;
                        tableBody.appendChild(row);
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5" class="no-products">No stock data available</td></tr>';
                }
            })
            .catch(error => console.error('Error fetching stock data:', error));
    }

    // Call fetchStockData when page loads
    fetchStockData();
});


function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.style.display = 'none';
    });

    // Show the selected section
    document.getElementById(sectionId).style.display = 'block';

    // If showing the 'stock' section, fetch the latest stock data
    if (sectionId === 'stock') {
        fetchStockData();
    }
}

function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.style.display = 'none';
    });

    // Show the selected section
    document.getElementById(sectionId).style.display = 'block';

    // If showing the 'stock' section, fetch the latest product IDs
    if (sectionId === 'stock') {
        fetchProductIds();
    }
}

document.getElementById('stockButton').addEventListener('click', function() {
            var stockSection = document.getElementById('stock');
            if (stockSection.style.display === 'none') {
                stockSection.style.display = 'block';
            } else {
                stockSection.style.display = 'none';
            }
        });

        document.getElementById('stockButton').addEventListener('click', function() {
            var stockSection = document.getElementById('stock');
            var stockTableBody = document.getElementById('stockTableBody');
            
            if (stockSection.style.display === 'none') {
                // Show the stock section
                stockSection.style.display = 'block';

                // Fetch stock data
                fetch('get_stock.php')
                    .then(response => response.json())
                    .then(data => {
                        stockTableBody.innerHTML = ''; // Clear previous data

                        if (data.length > 0) {
                            data.forEach(item => {
                                var row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${item.stock_id}</td>
                                    <td>${item.product_id}</td>
                                    <td>${item.stock_in}</td>
                                    <td>${item.stock_out}</td>
                                    <td>${item.current_stock}</td>
                                `;
                                stockTableBody.appendChild(row);
                            });
                        } else {
                            stockTableBody.innerHTML = '<tr><td colspan="5" class="no-products">No stock data available</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching stock data:', error);
                        stockTableBody.innerHTML = '<tr><td colspan="5" class="no-products">Error fetching stock data</td></tr>';
                    });
            } else {
                // Hide the stock section
                stockSection.style.display = 'none';
            }
        });

        function showSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.style.display = 'none';
    });
    document.getElementById(sectionId).style.display = 'block';
}

function showCategoryView() {
    // Implement logic to fetch and display products by category
}

        // Function to show product details
function viewProduct(productId) {
    fetch(`get_product.php?product_id=${productId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const product = data.product;
            // Populate product details in a modal or a dedicated section
            document.getElementById('viewProductCode').textContent = product.product_code;
            document.getElementById('viewProductName').textContent = product.product_name;
            document.getElementById('viewCategory').textContent = product.category;
            document.getElementById('viewUnit').textContent = product.unit;
            document.getElementById('viewPrice').textContent = product.price;
            document.getElementById('viewExpirationDate').textContent = product.expiration_date;

            document.getElementById('viewProductModal').style.display = 'block';
        } else {
            alert('Failed to load product details.');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Function to close the View Product Modal
function closeViewProductModal() {
    document.getElementById('viewProductModal').style.display = 'none';
}

// Load product list on page load
window.onload = function() {
    loadProductList();
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch(`delete_product.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${productId}`
        })
        .then(response => {
            return response.json(); // Parse JSON response
        })
        .then(data => {
            if (data.success) {
                // Remove the product row from the table
                const row = document.querySelector(`#stock-${productId}`).parentElement;
                row.remove();
                alert('Product deleted successfully.');
            } else {
                alert(data.message || 'Error deleting product.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting product.');
        });
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

  // Function to open the View Stock modal using data from the stockTable
function viewStock(productId) {
    // Locate the table row for the specified productId
    const tableRow = document.querySelector(`#stockTable tr[data-product-id="${productId}"]`);
    
    if (tableRow) {
        // Extract data from the table row
        const productName = tableRow.querySelector('.product-name').innerText;
        const stockIn = tableRow.querySelector('.stock-in').innerText;
        const stockOut = tableRow.querySelector('.stock-out').innerText;
        const currentStock = tableRow.querySelector('.current-stock').innerText;
        const status = tableRow.querySelector('.status').innerText;

        // Populate the modal with the fetched data
        document.getElementById('viewModalProductName').innerText = productName; // Display product name
        document.getElementById('viewStockIn').innerText = stockIn; // Display Stock In
        document.getElementById('viewStockOut').innerText = stockOut; // Display Stock Out
        document.getElementById('viewCurrentStock').innerText = currentStock; // Display Current Stock
        document.getElementById('viewProductStatus').innerText = status; // Display Status

        // Open the view stock modal
        document.getElementById('viewStockModal').style.display = 'block';
    } else {
        alert("Error: Product not found in the table.");
    }
}

// Function to open the Edit Stock modal
function editStock(productId) {
    // Fetch the current stock data using the productId
    fetch(`getProductDetails.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the data to check its structure

            // Populate the modal with the fetched data
            if (data.success) {
                const product = data.data; // Access the product data

                // Populate edit stock modal
                document.getElementById('editModalProductName').innerText = product.product_name; // Display Product Name
                document.getElementById('editModalProductId').value = product.product_id; // Store Product ID
                document.getElementById('modalStockIn').value = product.stock_in; // Editable field

                // Open the edit stock modal
                document.getElementById('editStockModal').style.display = 'block';
            } else {
                alert("Product details not found or data is missing.");
            }
        })
        .catch(error => {
            console.error('Error fetching product details:', error);
            alert("An error occurred while fetching product details.");
        });
}


function updateStockIn() {
    const productId = document.getElementById('modalProductId').value;
    const stockIn = document.getElementById('modalStockIn').value;

    fetch('updateStockIn.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `product_id=${productId}&stock_in=${stockIn}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Stock updated successfully');
            closeModal('editStockModal');
            location.reload(); // Refresh the page to update the stock table
        } else {
            alert('Failed to update stock: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating stock:', error);
        alert('Error updating stock');
    });
}

// Function to save the updated stock_in value
function saveStockIn() {
    const productId = document.getElementById('editModalProductId').value; // Get product_id from the hidden field
    const stockIn = document.getElementById('modalStockIn').value; // Get stock_in value from input field

    // Check if inputs are valid
    if (productId && stockIn) {
        // Make the AJAX request to update stock_in in the database
        fetch('updateStockIn.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&stock_in=${stockIn}`,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Display success message
                // Optionally, close the modal
                closeModal('editStockModal');
                location.reload(); // Refresh the page or update table content
            } else {
                alert("Error: " + data.message); // Display error message
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating stock.');
        });
    } else {
        alert("Please fill in all fields.");
    }
}


// Function to close modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Function to close modals
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
        
function showSection(sectionId) {
    // Hide all sections first
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.style.display = 'none'; // Hide all sections
    });

    // Show the selected section
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block'; // Show the selected section
    }
}


function checkExpiredProducts() {
    console.log("Checking expired products...");

    // First, move expired products if any
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'move_expired_products.php', true);
    xhr.onload = function() {
        if (this.status == 200) {
            console.log("Expired products moved successfully.");
            fetchExpiredProductsHistory(); // Fetch the expired products after moving
        } else {
            console.error("Failed to move expired products.");
        }
    };
    xhr.onerror = function() {
        console.error("Error occurred while moving expired products.");
    };
    xhr.send();
}

function fetchExpiredProductsHistory() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_expired_products.php', true); // Adjust this URL as needed
    xhr.onload = function() {
        if (this.status == 200) {
            // Populate the tbody of the expired products table with the fetched data
            document.getElementById('expiredProductsTable').querySelector('tbody').innerHTML = this.responseText;

            // Show the expired products section only if there are results
            if (document.getElementById('expiredProductsTable').querySelector('tbody').innerHTML.trim() !== "") {
                document.getElementById('expiredProducts').style.display = 'block'; // Show expired products section
                document.getElementById('productList').style.display = 'none'; // Hide product list table
            } else {
                console.log("No expired products found.");
                document.getElementById('expiredProducts').style.display = 'none'; // Hide section if no data
                document.getElementById('productList').style.display = 'block'; // Optionally, show product list table if no expired products
            }
        } else {
            console.error("Error fetching expired products: ", this.statusText);
        }
    };
    xhr.onerror = function() {
        console.error("Request error...");
    };
    xhr.send();
}


// Optional: Automatically check for expired products when the page loads
window.onload = function() {
    // You can call checkExpiredProducts() here if you want to check on load, or keep it commented.
};


// Optional: Automatically check for expired products when the page loads
window.onload = function() {
    checkExpiredProducts();  // Automatically check and load expired products on page load
};


function printInventory() {
    // Get the content of the expired products table
    var printContents = document.getElementById('expiredProductsTable').outerHTML;
    
    // Create a new window
    var win = window.open('', '', 'height=600,width=800');
    
    // Write the HTML for the new window
    win.document.write('<html><head><title>Expired Products</title>');
    win.document.write('<style>table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 5px; text-align: center; } h3 { text-align: center; }</style>');
    win.document.write('</head><body>');
    win.document.write('<h3>Expired Products History</h3>');
    win.document.write(printContents);
    win.document.write('</body></html>');
    
    // Close the document to render the page
    win.document.close();
    win.focus();
    
    // Print the contents of the new window
    win.print();
    
    // Close the new window after printing
    win.close();
}

function printStockReport() {
    // Get the stock table
    var stockTable = document.getElementById('stockTable');
    
    // Create a new table for the report
    var reportTable = document.createElement('table');

    // Clone the header row excluding "Status" and "Tools"
    var headerRow = stockTable.querySelector('thead tr').cloneNode(true);
    headerRow.deleteCell(6); // Assuming "Status" is the 7th cell (index 6)
    headerRow.deleteCell(7); // Assuming "Tools" is the 8th cell (index 7)
    reportTable.appendChild(headerRow);

    // Clone the body rows excluding "Status" and "Tools"
    var bodyRows = stockTable.querySelectorAll('tbody tr');
    bodyRows.forEach(function(row) {
        var newRow = row.cloneNode(true);
        newRow.deleteCell(6); // Exclude "Status"
        newRow.deleteCell(7); // Exclude "Tools"
        reportTable.appendChild(newRow);
    });

    // Create a new window
    var printWindow = window.open('', '_blank', 'width=800,height=600');
    
    // Write the report table HTML to the new window
    printWindow.document.write(`
        <html>
            <head>
                <title>Stock Report</title>
                <link rel="stylesheet" href="styles.css"> <!-- Include your CSS styles if needed -->
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 5px; text-align: center; font-size: 15px; border: 1px solid #ddd; }
                    th { background-color: #f2f2f2; }
                      h3 { text-align: center; }
                </style>
            </head>
            <body>
             <h3>Dinne's Seafood House</h3>
                <h4>Stock Report</h4>
                ${reportTable.outerHTML}
            </body>
        </html>
    `);

    // Close the document to render the content
    printWindow.document.close();
    
    // Wait for the new window to load before calling print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close(); // Close the print window after printing
    };
}

    

    </script>
</body>
</html>