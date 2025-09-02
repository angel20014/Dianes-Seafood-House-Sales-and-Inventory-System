<?php
include('db.php');

// Fetch orders data
$orders_sql = "SELECT * FROM orders ORDER BY order_date DESC";
$orders_result = $conn->query($orders_sql);

// Fetch products data
$products_sql = "SELECT * FROM products";
$products_result = $conn->query($products_sql);

// Group products by category
$products_by_category = [];
while ($product = $products_result->fetch_assoc()) {
    $category = $product['category'];
    if (!isset($products_by_category[$category])) {
        $products_by_category[$category] = [];
    }
    $products_by_category[$category][] = $product;
}

// Fetch sales data
$sales_sql = "SELECT * FROM sales ORDER BY saleDate DESC";
$sales_result = $conn->query($sales_sql);
if (!$sales_result) {
    throw new Exception("Error fetching sales: " . $conn->error);
}

// Function to update stock after a sale
function updateStockAfterSale($conn, $productId, $quantitySold) {
    // Check current stock level
    $stock_sql = "SELECT current_stock FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($stock_sql);
    if (!$stmt) {
        die('Failed to prepare stock query: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stock_result = $stmt->get_result();
    $stmt->close();

    if ($stock_result->num_rows > 0) {
        $stock_row = $stock_result->fetch_assoc();
        $currentStock = $stock_row['current_stock'];

        // Calculate new stock level
        $newStockLevel = $currentStock - $quantitySold;

        // Update stock level in database
        $update_stock_sql = "UPDATE products SET current_stock = ? WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_stock_sql);
        if (!$update_stmt) {
            die('Failed to prepare stock update query: ' . $conn->error);
        }

        $update_stmt->bind_param("ii", $newStockLevel, $productId);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

// Process each sale and update stock levels
while ($sale = $sales_result->fetch_assoc()) {
    $productId = $sale['product_name']; // Assuming this field matches product_id; adjust if necessary
    $quantitySold = $sale['quantity'];

    // Update stock level after sale
    updateStockAfterSale($conn, $productId, $quantitySold);
}

// Function to update low sales products based on total sales in the sales table
function updateLowSalesProducts($conn, $lowSalesThreshold) {
    // Query to get total sales for each product from the sales table
    $sql = "SELECT s.product_name, SUM(s.total) AS totalSales
            FROM sales s
            GROUP BY s.product_name
            HAVING totalSales < ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Failed to prepare the SQL statement: ' . $conn->error);
    }

    // Bind the low sales threshold parameter
    $stmt->bind_param("d", $lowSalesThreshold);
    $stmt->execute();
    $lowSalesResult = $stmt->get_result();

    if (!$lowSalesResult) {
        die('Failed to execute the query: ' . $conn->error);
    }

    // Prepare statement to insert or update low sales products
    $insertOrUpdateSql = "INSERT INTO low_sales_products (product_name, total, updated_at)
                          VALUES (?, ?, NOW())
                          ON DUPLICATE KEY UPDATE total = VALUES(total), updated_at = NOW()";

    $insertOrUpdateStmt = $conn->prepare($insertOrUpdateSql);

    if (!$insertOrUpdateStmt) {
        die('Failed to prepare the SQL insert/update statement: ' . $conn->error);
    }

    // Iterate over the results and insert or update the low sales products
    while ($row = $lowSalesResult->fetch_assoc()) {
        $insertOrUpdateStmt->bind_param("sd", $row['product_name'], $row['totalSales']);
        $insertOrUpdateStmt->execute();
    }

    // Close the statement
    $insertOrUpdateStmt->close();
}

// Define the low sales threshold
$lowSalesThreshold = 1000; // Set your threshold here

// Update low sales products
updateLowSalesProducts($conn, $lowSalesThreshold);


// Update stock levels after processing sales
$updateStockQuery = "
    UPDATE stock 
    SET current_stock = current_stock - ?
    WHERE product_id = ?";

    function processSale($productId, $quantitySold) {
        include('db.php'); // Include database connection
    
        // Step 1: Fetch the current stock
        $currentStockQuery = "SELECT current_stock FROM stock WHERE product_id = ?";
        $stmt = $conn->prepare($currentStockQuery);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if product exists
        if ($result->num_rows === 0) {
            echo "Product not found.";
            return;
        }
    
        $currentStock = $result->fetch_assoc()['current_stock'];
    
        // Step 2: Check if there's enough stock
        if ($currentStock >= $quantitySold) {
            // Step 3: Update stock
            $updateStockQuery = "UPDATE stock SET current_stock = current_stock - ? WHERE product_id = ?";
            $stmt = $conn->prepare($updateStockQuery);
            $stmt->bind_param("ii", $quantitySold, $productId);
            $stmt->execute();
    
            echo "Sale processed successfully! Current stock updated.";
        } else {
            // Handle insufficient stock scenario
            echo "Not enough stock available. Current stock: $currentStock, Quantity sold: $quantitySold.";
        }
    
        $stmt->close();
        $conn->close();
    }
    
// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Existing CSS */
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

        .header .logout-icon, .header .admin-settings-icon {
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

        .header .admin-settings-icon:hover {
            color: #4CAF50;
        }

        .header .admin-settings-icon i, .header .logout-icon i {
            margin-right: 0;
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
            top: 70px;
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
        /* CSS for new user info section inside the sidebar */
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

            /* Flexbox for product categories */
 /* Flexbox for product categories */
 #productCategories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Adjust space between boxes */
        }

        /* Style for category buttons */
        .category-buttons {
            display: flex;
            gap: 10px; /* Space between buttons */
            margin-bottom: 20px; /* Space below the buttons */
        }

        .category-buttons button {
            padding: 0px 20px; /* Space inside the button */
            font-size: 16px; /* Font size for button text */
            border: none; /* Remove default border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            color: white; /* Text color */
            background-color: #2E8B57; /* Background color */
            transition: background-color 0.3s, transform 0.2s; /* Smooth color change and scaling effect */
        }

        .category-buttons button:hover {
            background-color: #1c6b40; /* Darker background color on hover */
            transform: scale(1.05); /* Slightly enlarge button on hover */
        }

        .category-buttons button:active {
            background-color: #1a5736; /* Even darker color on click */
            transform: scale(0.98); /* Slightly shrink button on click */
        }

        /* Individual category box styling */
        .box {
            width: calc(33.33% - 20px); /* Three boxes per row with gap adjustment */
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
            background-color: #fff;
            border: 1px solid black; /* Black border */
        }

        /* Increase font size in category boxes */
        .box h3 {
            font-size: 24px; /* Adjust font size as needed */
            margin-bottom: 15px;
        }

        .box ul {
            font-size: 16px; /* Adjust font size as needed */
        }

        .box li {
            margin-bottom: 10px;
        }

        /* Flexbox for columns inside .content */
        .column {
            flex: 1;
            box-sizing: border-box;
            display: flex;
            flex-direction: column; /* Stack children vertically */
        }

        /* Content container setup */
        .content {
            margin-left: 250px; /* Space for sidebar */
            padding: 20px;
            padding-bottom: 100px; /* Add space for fixed footer */
            display: flex;
            gap: 20px; /* Space between columns */
            width: calc(100% - 250px); /* Full width minus sidebar width */
            box-sizing: border-box; /* Include padding and border in width calculation */
            overflow: hidden; /* Prevent scrolling within content */
        }

        /* Flexbox for columns inside .content */
        .column {
            flex: 1;
            box-sizing: border-box;
            display: flex;
            flex-direction: column; /* Stack children vertically */
        }

        /* Style for sales table */
        /* Ensure the table container has a fixed height and scrolls if needed */
.salesTableWrapper {
    max-height: 400px; /* Adjust height as needed */
    overflow-y: auto; /* Add vertical scrollbar */
    margin-bottom: 20px; /* Space below the table */
}

#salesTableContainer {
    width: 100%;
    height: 523px; /* Adjust height as needed */
    overflow-y: auto; /* Enable vertical scrolling */
    overflow-x: hidden; /* Hide horizontal scrollbar if not needed */
    box-sizing: border-box; /* Include padding and border in element's total width and height */
    border: 2px solid #ddd; /* Optional, for visual separation */
    text-align: center; 
    background-color: gainsboro;
}

#salesTableContainer h2 {
    margin: 0; /* Removes default margin for cleaner centering */
    padding: 20px 0; /* Optional: Adds vertical padding to space out the heading */
    font-size: 24px; /* Optional: Adjusts font size as needed */
    color: #333; /* Optional: Sets text color */
}

/* Custom Scrollbar for WebKit Browsers (Chrome, Safari) */
#salesTableContainer::-webkit-scrollbar {
    width: 12px; /* Width of the scrollbar */
}

#salesTableContainer::-webkit-scrollbar-track {
    background: #f1f1f1; /* Color of the track (part the scrollbar moves within) */
}

#salesTableContainer::-webkit-scrollbar-thumb {
    background: #888; /* Color of the scrollbar thumb (the draggable part) */
    border-radius: 6px; /* Rounded corners for the thumb */
}

#salesTableContainer::-webkit-scrollbar-thumb:hover {
    background: #555; /* Darker color when hovered */
}

/* Custom Scrollbar for Firefox */
#salesTableContainer {
    scrollbar-width: thin; /* Thin scrollbar */
    scrollbar-color: #888 #f1f1f1; /* Thumb color and track color */
}

/* Styling for the Sales Table */
#salesTable {
    width: 100%;
    border-collapse: collapse; /* Optional, for table styling */
    margin-top: 10px;
}

#salesTable th, #salesTable td {
    border: 1px solid #ddd; /* Optional, for table styling */
    padding: 8px; /* Optional, for spacing */
  
}

#salesTable th {
    background-color: white; /* Optional, for header background */
}
        /* Style for the order summary section */
        /* Style for the order summary section */
#orderSummary {
    position: fixed; /* Stick to the bottom of the viewport */
    bottom: 0;
    left: 1048px; /* Start where the sidebar ends */
    width: calc(55% - 250px); /* Full width minus sidebar width */
    background-color: lightgrey;
    border-top: 1px solid #ddd;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    padding: 10px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column; /* Stack items vertically */
    gap: 10px; /* Space between summary items */
    z-index: 1000; /* Ensure it's on top of other content */
}

/* Style for the summary items */
.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0;
    
}

.summary-item label {
    flex: 1;
    font-weight: bold;
    font-size: 24px;
    text-align: justify;
}

.summary-item input {
    flex: 2;
    padding: 10px;
    font-size: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
    text-align: justify;
    font-weight: bold;
    
}

.order-buttons {
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    color: #fff;
    background-color: #007bff;
    cursor: pointer;
    margin-right: 10px;
}

.order-buttons:hover {
    background-color: #0056b3;
}

.order-buttons:active {
    background-color: #1a5736;
    transform: scale(0.98);
}

        /* Styling for summary items container */
        .summary-items-container {
            display: flex; /* Use flexbox for horizontal alignment */
            gap: 20px; /* Space between items */
            align-items: center; /* Vertically center items */
        }

        /* Individual summary item styling */
        .summary-items {
            display: flex;
            align-items: center; /* Center label and select vertically */
            gap: 10px; /* Space between label and select */
        }

        .summary-items label {
            font-size: 14px; /* Adjust font size as needed */
            font-weight: bold; /* Make label text bold */
        }

        .summary-items select {
            padding: 5px;
            font-size: 14px; /* Adjust font size as needed */
        }

        .logo-container {
    display: flex; /* Use flexbox for alignment */
    align-items: center; /* Vertically center the items */
    justify-content: center; /* Align to the right */
    margin-right: 30px;
}

.logo-container img {
    height: 80px; /* Set height */
    width: auto; /* Maintain aspect ratio */
    border-radius: 50%; /* Optional: make the logo oval */
    margin-right: 10px; /* Space between logo and info */
}

.date {
    margin-left: auto; /* Pushes the date to the right */
    font-size: 14px; /* Adjust font size as needed */
    margin-right: 10px;
}

    </style>
</head>
<body>
    <div class="header">
        <div class="title">Sales and Inventory System</div>
        <a href="admin-settings.php" class="admin-settings-icon">
            <i class="fas fa-cogs"></i>
        </a>
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
    <a href="sales.php" onclick="navigateTo('sales')">
        <i class="fas fa-dollar-sign"></i>
        <span>Sales</span>
    </a>
    <a href="sales_transaction.php" onclick="navigateTo('sales_transaction')">
        <i class="  fas fa-exchange-alt"></i>
        <span>Transaction</span>
    </a>
    <a href="inventory.php" onclick="navigateTo('inventory')">
        <i class="fas fa-clipboard-list"></i>
        <span>Inventory</span>
    </a>
    <a href="inventory_report.php" onclick="navigateTo('inventory_report')">
        <i class="fas fa-file-alt"></i>
        <span>Report</span>
    </a>
    <a href="expired.php" onclick="navigateTo('expired')">
        <i class="fas fa-bell"></i>
        <span>Notifications</span>
    </a>
   
</div>

    
    <div class="content">
        
        <!-- Column 1: Product Categories -->
        <div class="column" id="productCategoriesContainer">
            <h2>Sales</h2>
            <div id="productCategories">
                <?php foreach ($products_by_category as $category => $products): ?>
                    <div class="box" data-category="<?php echo htmlspecialchars($category); ?>">
                        <h3><?php echo htmlspecialchars($category); ?></h3>
                        <ul>
                            <?php foreach ($products as $product): ?>
                                <li>
                                    <span><?php echo htmlspecialchars($product['product_name']); ?></span>
                                    <span><?php echo htmlspecialchars($product['price']); ?> Php</span>
                                    <button onclick="selectProduct('<?php echo htmlspecialchars($product['product_id']); ?>', '<?php echo htmlspecialchars($product['product_name']); ?>', <?php echo htmlspecialchars($product['price']); ?>)">Select</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

                                
        <!-- Column 2: Sales Table and Order Summary -->
        <div class="column" id="salesTableContainer">
    <div class="logo-container">
        <img src="logo.png" alt="Logo">
        <div class="restaurant-info">
            <h3>DIANNES SEAFOOD HOUSE</h3>
            <p>Tulay, Poblacion III, Carcar City, Cebu</p>
           

        </div>
    </div>

    <p id="currentDate" class="date"></p> 
    <h2>ORDERS</h2> 


            <div class="summary-items-container">
                <div class="summary-items">
                    <label for="orderType">Order Type: </label>
                    <select id="orderType">
                        <option value="Dine In">Dine In</option>
                        <option value="Take Out">Take Out</option>
                    </select>
                </div>
                <div class="summary-items">
                    <label for="customerType">Customer Type: </label>
                    <select id="customerType">
                        <option value="Regular">Regular</option>
                        <option value="Senior">Senior Citizen</option>
                    </select>
                </div>
            </div>

            <div class="salesTableWrapper">
        <table id="salesTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
</table>
<td></td> <!-- Added column for action buttons -->


    <div id="orderSummary">
    <div class="summary-item">
        <label for="totalAmount">Total Amount </label>
        <input type="text" id="totalAmount" readonly>
    </div>
    <div class="summary-item">
        <label for="amountPaid">Amount Paid </label>
        <input type="number" id="amountPaid" placeholder="">
    </div>
    <div class="summary-item">
        <label for="changeAmount">Change Amount </label>
        <input type="text" id="changeAmount" readonly>
    </div>
    <div class="summary-item">
        <button class="order-buttons" onclick="calculateChange()">Calculate Change</button>
        <button class="order-buttons" onclick="submitOrder()">ADD Order</button>
    </div>
    
</div>


<script>

     // Function to format the date as needed
     function formatDate(date) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString(undefined, options);
    }

    // Set the current date in the specified paragraph
    document.addEventListener('DOMContentLoaded', function() {
        const dateParagraph = document.getElementById('currentDate');
        const today = new Date();
        dateParagraph.textContent = formatDate(today);
    });

    
    const salesTableBody = document.querySelector('#salesTable tbody');
    let totalAmount = 0; // Initialize totalAmount to track the total price of all items
    let orderDate = new Date().toLocaleDateString();

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

    function confirmSettings() {
    document.getElementById('settingsModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('settingsModal').style.display = 'none';
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

    function closeModal() {
        document.getElementById('settingsModal').style.display = 'none';
    }

    function confirmSettings() {
        document.getElementById('settingsModal').style.display = 'block';
    }

    function cancelSettings() {
        document.getElementById('settingsModal').style.display = 'none';
    }

    function navigateTo(page) {
        window.location.href = page + '.php';
    }

    function filterCategory(category) {
        const boxes = document.querySelectorAll('#productCategories .box');
        boxes.forEach(box => {
            if (category === 'Cancel' || box.dataset.category === category) {
                box.style.display = '';
            } else {
                box.style.display = 'none';
            }
        });
    }

    function selectProduct(productId, product_name, productPrice) {
        const quantity = prompt('Enter quantity:');
        if (quantity && !isNaN(quantity) && quantity > 0) {
            const orderType = document.getElementById('orderType').value;
            const customerType = document.getElementById('customerType').value;
            let discount = 0;
            if (customerType === 'Senior') {
                discount = 0.20; // 20% discount
            }
            const total = (productPrice * quantity) * (1 - discount);
            addSale(orderDate, product_name, quantity, productPrice, total);
        } else {
            alert('Invalid quantity.');
        }
    }

    

    function addSale(date, product, quantity, price, total) {
    const row = document.createElement('tr');
    row.innerHTML = `
         <td style="display:none;">${date}</td> <!-- Hidden date -->
        <td>${product}</td>
        <td>${quantity}</td>
        <td>${price.toFixed(2)}</td>
        <td>${total.toFixed(2)}</td>
        <td><button onclick="cancelSale(this, '${date}', '${product}')">Cancel</button></td>
    `;
    salesTableBody.appendChild(row);

    // Update the total amount for calculating change
    totalAmount += total;
    document.getElementById('totalAmount').value = totalAmount.toFixed(2) + ' Php';

    // Prepare data for server-side processing
    const saleData = {
        saleDate: date,
        product_name: product,
        quantity: quantity,
        price: price,
        total: total,
        orderType: document.getElementById('orderType').value,
        customerType: document.getElementById('customerType').value
    };

    // Send AJAX request to record the sale
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'record_sale.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log(response.message); // Sale recorded successfully
            } else {
                console.error(response.message); // Failed to record sale
            }
        } else {
            console.error('An error occurred while recording the sale.');
        }
    };

    // Encode data for URL-encoded format
    const encodedData = Object.keys(saleData).map(key => encodeURIComponent(key) + '=' + encodeURIComponent(saleData[key])).join('&');
    xhr.send(encodedData);
}



    function calculateChange() {
        const amountPaid = parseFloat(document.getElementById('amountPaid').value);
        if (isNaN(amountPaid)) {
            alert('Please enter a valid amount paid.');
            return;
        }

        const changeAmount = amountPaid - totalAmount;
        if (changeAmount < 0) {
            alert('Amount paid is insufficient. Please enter a higher amount.');
            document.getElementById('changeAmount').value = '';
        } else {
            document.getElementById('changeAmount').value = changeAmount.toFixed(2) + ' Php';
        }
    }

    function cancelSale(button, date, product) {
        // Confirm cancellation
        if (confirm('Are you sure you want to cancel this sale?')) {
            const row = button.closest('tr');
            const totalCell = row.querySelector('td:nth-child(5)');
            const total = parseFloat(totalCell.textContent.replace(' Php', ''));
            row.remove(); // Remove the row from the table

            // Update the total amount for calculating change
            totalAmount -= total; // Subtract the cancelled sale total from the existing amount
            document.getElementById('totalAmount').value = totalAmount.toFixed(2) + ' Php'; // Display updated total amount

            // Send AJAX request to cancel the sale
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'cancel_sale.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Prepare data for server-side processing
            const cancelData = {
                saleDate: date,
                productName: product
            };

            // Handle server response
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log(response.message); // Sale cancelled successfully
                    } else {
                        console.error(response.message); // Failed to cancel sale
                    }
                } else {
                    console.error('An error occurred while cancelling the sale.');
                }
            };

            // Encode data for URL-encoded format
            const encodedData = Object.keys(cancelData).map(key => encodeURIComponent(key) + '=' + encodeURIComponent(cancelData[key])).join('&');
            xhr.send(encodedData);
        }
    }

    document.getElementById('addOrderButton').addEventListener('click', function() {
        // Perform any necessary validation before submitting the order
        const totalAmount = parseFloat(document.getElementById('totalAmount').value.replace(' Php', ''));
        if (isNaN(totalAmount) || totalAmount <= 0) {
            alert('Please add items to the order before placing it.');
            return;
        }

        // Call the submitOrder function
        submitOrder();
    });

    function submitOrder() {
    const orderType = document.getElementById('orderType').value;
    const customerType = document.getElementById('customerType').value;
    const totalAmount = parseFloat(document.getElementById('totalAmount').value.replace(' Php', ''));
    const amountPaid = parseFloat(document.getElementById('amountPaid').value);
    const changeAmount = parseFloat(document.getElementById('changeAmount').value.replace(' Php', ''));
   
    // Format orderDate as YYYY-MM-DD
    const orderDate = new Date().toISOString().split('T')[0]; // Format as YYYY-MM-DD
    

    // Generate or get a unique customer ID (this could be from session or a unique session-based ID)
    const customerId = generateUniqueCustomerId(); // Implement this function based on your requirements

    // Record the customer data
    recordCustomer(customerId, customerType, orderDate);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'record_order.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert('Order placed successfully!');
                // Clear form or refresh table
            } else {
                alert('Failed to place the order. ' + response.message);
            }
        } else {
            alert('An error occurred while placing the order.');
        }
    };
    const data = `orderType=${orderType}&customerType=${customerType}&totalAmount=${totalAmount}&amountPaid=${amountPaid}&changeAmount=${changeAmount}&customerId=${customerId}`;
    xhr.send(data);
}


function generateUniqueCustomerId() {
    // Implement a method to generate a unique customer ID
    // For example, use a UUID or a session-based ID
    return 'CUSTOMER_' + Math.random().toString(36).substr(2, 9); // Example implementation
}


function recordCustomer(customerId, customerType, orderDate) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'record_customer.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                console.log(response.message); // Customer recorded successfully
            } else {
                console.error(response.message); // Failed to record customer
            }
        } else {
            console.error('An error occurred while recording the customer.');
        }
    };

    // Encode data for URL-encoded format
    const data = `customerId=${encodeURIComponent(customerId)}&customerType=${encodeURIComponent(customerType)}&orderDate=${encodeURIComponent(orderDate)}`;
    xhr.send(data);
}



    
</script>

</body>
</html>