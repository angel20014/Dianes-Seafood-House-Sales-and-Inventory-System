<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Details</title>
    <style>
        /* CSS for header */
        .header {
    background-color: #696969;
    color: white;
    text-align: left;
    padding: 20px 0;
    font-size: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between; /* Ensure space between title and logout button */
    margin-left: 259px; /* Ensure it doesn't overlap with sidebar */
    padding-left: 20px; /* Add padding to the left for better alignment */
}

.header .title {
    flex: 1; /* Allow title to take up available space */
}

.header .logout-btn {
    margin: 0; /* Remove any margin around logout button */
    background-color: #ff0000;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.header .logout-btn:hover {
    background-color: #cc0000;
}

        /* CSS for sidebar menu */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #2E8B57;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: left; /* Align items vertically */
        }
        .sidebar a {
            padding: 10px;
            text-decoration: none;
            font-size: 24px; /* Adjust font size */
            color: black;
            display: block; /* Change display to block */
            background-color: whitesmoke; /* Button background color */
            border: none;
            border-radius: 5px; /* Add border radius */
            margin-top: 26px;
            margin-right: 22px;
            margin-left: 22px;
            text-align: center; /* Center text */
            transition: background-color 0.7s; /* Add transition effect */
            font-weight: bold;
            text-align: center;
        }

        .sidebar a:hover {
            background-color: greenyellow; /* Change background color on hover */
        }
        .sidebar i {
            margin-left: 10px;
            color: #fff; /* Set default icon color */
            animation: glow 1.5s infinite alternate; /* Add glowing animation */
        }
        .sidebar .icon {
            width: 20px;
        }

        /* CSS for logo */
        .sidebar img {
            height: 80px;
            margin-bottom: 50px; /* Added margin */
            margin-left: 10px; /* Added margin */
            margin-right: 10px; /* Added margin */
            filter: blur(.5px); /* Apply blur effect */
            border-radius: 50%; /* Make it circular */
        }

        .content {
            margin-left: 459px; /* Align content to the right of the sidebar */
            padding: 20px;
            margin-top: 80px; /* Space for the fixed header */
        }

        .box {
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* CSS for logout button */
        .logout-btn {
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

        /* CSS for user icon and ADMIN text */
        .sidebar .user-icon {
            font-size: 100px; /* Adjust the size of the user icon */
            color: black; /* Set icon color */
            margin-bottom: 20px; /* Space below the icon */
        }

        .sidebar .admin-text {
            font-size: 24px; /* Adjust font size of ADMIN text */
            font-weight: bold;
            color: black; /* Set text color */
        }

        .icon {
            margin-right: 5px;
            font-size: 35px;
        }

        .icon,
        .box i {
            font-size: 24px; /* Adjust the size of the icons */
        }

        .user-circle {
            width: 150px;
            height: 150px;
            border-radius: 80%;
            background-color: #ccc; /* Change the background color as needed */
            display: flex;
            margin-left: 50px;
            justify-content: center;
            align-items: center;
            font-size: 48px; /* Adjust the size of the user icon */
        }

        .admin-text {
            margin-left: 80px;
        }

        .horizontal-line {
            border-top: 5px solid black; /* Change the color and thickness as needed */
            margin: 10px 0; /* Adjust margin as needed */
            margin-bottom: 50px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
            margin-left: 300px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
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
        }

        th {
            background-color: #f2f2f2;
        }

        /* Button styles */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<?php include('db.php'); ?>
<div class="header">
    <div class="title">SALES AND INVENTORY SYSTEM</div>
    <button class="logout-btn" onclick="confirmLogout()">Logout</button>
</div>

<div class="sidebar">
    <div class="user-circle">
        <span class="user-icon">&#128100;</span>
    </div>
    <span class="admin-text">ADMIN</span>
    <div class="horizontal-line"></div>

    <a href="dashboard.php" onclick="navigateTo('dashboard')">
        <span>Dashboard</span>
        <i class="fas fa-tachometer-alt"></i>
    </a>
    <a href="sales.php" onclick="navigateTo('sales')">
        <span>Sales</span>
        <i class="fas fa-chart-line"></i>
    </a>
    <a href="inventory.php" onclick="navigateTo('inventory')">
        <span>Inventory</span>
        <i class="fas fa-clipboard-list"></i>
    </a>
    <a href="expired.php" onclick="navigateTo('iexpired')">
        <span>Notifications</span>
    </a>
    <a href="reports.php" onclick="navigateTo('reports')">
        <span>Reports</span>
        <i class="fas fa-clipboard-list"></i>
    </a>
</div>


<div class="container">
    <a href="inventory.php" class="btn">Product List</a>
    <h1>Stock Details</h1>
    <table>
        <thead>
            <tr>
                <th>Stock ID</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Unit</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Current Stock</th>
                <th>Status</th> <!-- New column for status -->
                <th>Expiration Date</th>
            </tr>
        </thead>
        <tbody id="stockDetailsTableBody">
            <!-- Stock details data will be populated dynamically here -->
        </tbody>
    </table>
</div>

<script>
    // Function to fetch and display stock details
function fetchAndDisplayStockDetails() {
    fetch("getproducts.php")
        .then(response => response.json())
        .then(data => {
            // Clear existing stock details table
            document.getElementById("stockDetailsTableBody").innerHTML = "";

            // Populate stock details table
            data.forEach(product => {
                updateStockDetails(product);
            });
        })
        .catch(error => {
            console.error(error);
            alert("Failed to fetch products. Please try again.");
        });
}

// Function to update stock details table
function updateStockDetails(product) {
    // Create a new row
    var row = document.createElement("tr");

    // Determine availability status
    var status = product.stock > 0 ? 'Available' : 'Not Available';

    // Populate the row with stock details
    row.innerHTML = `
        <td>${product.stock_id}</td>
        <td>${product.product_id}</td>
        <td>${product.product_name}</td>
        <td>${product.unit}</td>
        <td>${formatNumber(product.stock_in)}</td>
        <td>${formatNumber(product.stock_out)}</td>
        <td>${formatNumber(calculateCurrentStock(product.stock_in, product.stock_out))}</td>
        <td>${status}</td>
        <td>${product.expiration_date}</td>
    `;

    // Append row to stock details table body
    document.getElementById("stockDetailsTableBody").appendChild(row);
}

// Function to format numbers (e.g., for thousands separators)
function formatNumber(number) {
    return new Intl.NumberFormat().format(number);
}

// Function to calculate current stock
function calculateCurrentStock(stockIn, stockOut) {
    return stockIn - stockOut;
}

// Initial function call
window.onload = function() {
    fetchAndDisplayStockDetails(); // Fetch and display stock details
};

</script>
</body>
</html>
