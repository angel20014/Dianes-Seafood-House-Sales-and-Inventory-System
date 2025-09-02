<?php
session_start();

// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include('db.php'); // Adjust the path as necessary

// Fetch cashiers for the dropdown
$cashierSql = "SELECT cashier_id, name FROM cashiers";
$cashiersResult = $conn->query($cashierSql);
$cashiers = $cashiersResult->fetch_all(MYSQLI_ASSOC);

// Get selected cashier and date range from form submission
$selectedCashierId = isset($_POST['cashier']) ? $_POST['cashier'] : '';
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';

// Prepare sales query with optional cashier and date filters
$salesSql = "
    SELECT s.id, s.saleDate, s.product_name, s.quantity, s.price, s.total, s.orderType, s.customerType, s.cashType, c.name AS cashier_name
    FROM sales s
    LEFT JOIN cashiers c ON s.cashier_id = c.cashier_id
    WHERE 1 = 1
";


// Add cashier filter if a specific cashier is selected
if (!empty($selectedCashierId)) {
    $salesSql .= " AND s.cashier_id = '" . $conn->real_escape_string($selectedCashierId) . "'";
}

// Add date range filter
if (!empty($startDate) && !empty($endDate)) {
    $salesSql .= " AND s.saleDate BETWEEN '" . $conn->real_escape_string($startDate) . "' AND '" . $conn->real_escape_string($endDate) . "'";
}

// Execute the query
$result = $conn->query($salesSql);

// Check if there are sales records for the selected cashier
$cashierFound = $result && $result->num_rows > 0;

$totalSales = 0;
$cashTypeTotals = ['Cash' => 0, 'GCash' => 0]; // Add other cash types as necessary
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

        .date-time {
            margin-top: 0   ;
    font-size: 25px; /* Adjust size as needed */
    text-align: right;
}

.content {
    margin-left: 250px; /* Space for sidebar */
    padding: 20px;
    padding-top: 3px; /* Adjusted to ensure content starts below the fixed header */
    padding-bottom: 100px; /* Add space for fixed footer */
    display: flex;
    flex-direction: column; /* Stack items vertically */
    gap: 20px; /* Space between sections */
    width: calc(100% - 250px); /* Full width minus sidebar width */
    box-sizing: border-box; /* Include padding and border in width calculation */
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

.salesTableWrapper {
    background-color: #fff; /* White background for contrast */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    overflow: hidden; /* Prevents overflow from rounded corners */
    margin-top: 20px; /* Space above the table */
}

#salesTable {
    width: 100%; /* Full width */
    border-collapse: collapse; /* Removes space between table cells */
}

#salesTable th, #salesTable td {
    padding: 7px; /* Cell padding for better spacing */
    text-align: center; /* Align text to the left */
    border-bottom: 1px solid #ddd; /* Light gray border below each row */
    font-size: 23px;
}

#salesTable th {
    background-color: whitesmoke; /* Header background color */
    color: black; /* Header text color */
    font-weight: bold; /* Make header text bold */
    
}

h3 {
    display: flex; /* Use flexbox for alignment */
    justify-content: space-between; /* Space between title and icon */
    align-items: center; /* Center items vertically */
}

h3 i.fas.fa-print {
    cursor: pointer; /* Pointer cursor for clickable icon */
    margin-left: 1px; /* Space between title and icon */
    font-size: 24px; /* Size of the print icon */
    color: #2E8B57; /* Match the color with your theme */
    transition: color 0.3s; /* Smooth transition for color change */
    align-items: right;
}

h3 i.fas.fa-print:hover {
    color: #4CAF50; /* Change color on hover for better UX */
}

.date-range {
    text-align: right;  /* Aligns the content to the right */
    font-size: 21px;    /* Adjust the font size as needed */
    font-weight: bold;
}


        </style>

</head>
<body>
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
    <div class="date-time">
        <?php
        date_default_timezone_set('Asia/Manila');
        echo "<br>" . date('F j, Y \a\t g:i A');
        ?>
    </div>

    <div class="column"> 
    <h3 style="display: flex; justify-content: space-between; align-items: center; font-size: 27px;">
        <span>Sales Report</span>
        <div style="display: flex; align-items: center;">
            <span style="font-size: 22px; color: #2E8B57; align-items: right">Print</span>
            <i class="fas fa-print" onclick="printSalesTable()" style="margin-left: 5px; cursor: pointer;"></i>
        </div>
    </h3>
    
    <form method="post" id="filterForm" style="margin-bottom: 20px; display: flex; justify-content: flex-end; font-size: 25px; margin-right: 10px;">
        <label for="cashier" style="margin-right: 10px;">Cashier:</label>
        <select id="cashier" name="cashier" style="margin-right: 40px; font-size: 20px;">
            <option value="">All Cashiers</option>
            <?php foreach ($cashiers as $cashier): ?>
                <option value="<?php echo htmlspecialchars($cashier['cashier_id']); ?>" 
                    <?php echo ($selectedCashierId == $cashier['cashier_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cashier['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="startDate" style="margin-right: 10px;">Start Date:</label>
        <input type="date" id="startDate" name="startDate" 
               style="margin-bottom: 20px; font-size: 25px; margin-right: 10px;" 
               value="<?php echo htmlspecialchars($startDate); ?>" required>
        
        <label for="endDate" style="margin-left: 30px;">End Date:</label>
        <input type="date" id="endDate" name="endDate" 
               style="margin-bottom: 20px; font-size: 27px; margin-right: 10px;" 
               value="<?php echo htmlspecialchars($endDate); ?>" required>
        
        <input type="submit" name="submit" 
               style="margin-bottom: 20px; font-size: 25px; margin-right: 10px;" 
               value="Filter">
    </form>

   


<!-- Display selected date range -->
<div class="date-range">
    <strong>Date Range:</strong> <?php echo htmlspecialchars($startDate) . ' to ' . htmlspecialchars($endDate); ?>
</div>

<!-- Sales Table -->
<div class="salesTableWrapper">
    <table id="salesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Sale Date</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Order Type</th>
                <th>Customer Type</th>
                <th>Cash Type</th> <!-- New column for Cash Type -->
            </tr>
        </thead>
        <tbody>
        <?php if (isset($result) && $result->num_rows > 0): ?>
            <?php $totalSales = 0; // Initialize total sales ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['saleDate']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['total']); ?></td>
                    <td><?php echo htmlspecialchars($row['orderType']); ?></td>
                    <td><?php echo htmlspecialchars($row['customerType']); ?></td>
                    <td><?php echo htmlspecialchars($row['cashType']); ?></td> <!-- Display cashType -->
                </tr>
                <?php $totalSales += $row['total']; ?>
            <?php endwhile; ?>
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold; font-size: 22px;">Total Sales:</td>
                <td style="font-weight: bold; font-size: 22px;"><?php echo htmlspecialchars($totalSales); ?></td>
                <td colspan="3"></td> <!-- Adjust colspan to match new column count -->
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="9">No sales records found for the selected cashier.</td> <!-- Adjust colspan to match new column count -->
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
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


<script>
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

    document.getElementById('cashier').addEventListener('change', function() {
        const selectedCashierId = this.value;
        const selectedCashierName = this.options[this.selectedIndex].text;

        // Display the selected cashier's name
        const cashierDisplay = document.getElementById('selectedCashier');
        cashierDisplay.textContent = selectedCashierId ? `Cashier: ${selectedCashierName}` : ''; // Clear if "All Cashiers" is selected
    });

    function printSalesTable() {
        var salesTableWrapper = document.querySelector('.salesTableWrapper').innerHTML; // Get the table content
        var totalSales = "<?php echo htmlspecialchars($totalSales); ?>"; // Get the total sales value
        var selectedCashierName = document.getElementById('cashier').options[document.getElementById('cashier').selectedIndex].text; // Get selected cashier's name

        // Create the print content with title and cashier name
        var printContents = `
            <h2 style="text-align: center; font-size: 24px; font-weight: bold;">SALES REPORT</h2>
            <p style="text-align: center; font-size: 18px;">Cashier: ${selectedCashierName}</p>
            <div>${salesTableWrapper}</div>
            
        `;

        var originalContents = document.body.innerHTML; // Save the original body content
        document.body.innerHTML = printContents; // Replace the body content with the print content
        window.print(); // Trigger the print dialog
        document.body.innerHTML = originalContents; // Restore the original content after printing
    }
    </script>
</body>
</html>
