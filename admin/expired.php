<?php
include('db.php'); // Include your database connection file


// SQL query to fetch products
$sql = "SELECT product_id, product_name, expiration_date FROM products";
$result = $conn->query($sql);

$products = array();

if ($result->num_rows > 0) {
    // Fetch data from each row and add to $products array
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}


// Close connection
$conn->close();


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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

        .header .logout-icon {
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

        .header .logout-icon i {
            margin-right: 0;
        }

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

.header .admin-settings-icon:hover {
    color: #4CAF50; /* Change to a color of your choice for the hover effect */
}

.header .admin-settings-icon i {
    margin-right: 0;
}
 

        /* Modal styles */
        /* Modal CSS */
        .modal {
    display: none; /* Initially hidden */
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
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
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

        .content {
            margin-left: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }

        .box {
            padding: 20px;
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
        
        
        /* CSS for content */
        .content {
            margin-left: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: left; /* Center content horizontally */
        }
        /* CSS for logout button */
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
        .reports-container {
            margin-top: 80px; /* Adjust margin to avoid overlapping with header */
            margin-right: 20px;
        }
        /* CSS for reports form */
        #reportForm { 
            display: grid;
            grid-template-columns: 200px 200px 200px 200px;
            gap: 80px;
            margin-bottom: 10px;
        }
        #reportForm label {
            display: block;
            margin-bottom: 10px;
            font-size: 20px;
        }
        #reportForm input,
        #reportForm select {
            display: block;
            flex: 1;
            margin-bottom: 20px;
            padding: 5px;
            width: 100%;
            font-size: 20px;
            box-sizing: border-box;
        }
        #reportForm button {
            display: block;
            padding: 10px 10px;
            background-color: red;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
          
        }
        #reportForm button:hover {
            background-color: red;
        }
        #reportResult {
            margin-top: 20px;
            padding: 20px;
            background-color: red;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        /* CSS for expiration notification box */
        .expiration-box {
    background-color: #f9f9f9; /* Light gray background */
    border: 1px solid #ddd; /* Border for the box */
    border-radius: 8px; /* Rounded corners */
    padding: 20px; /* Padding inside the box */
    margin: 20px 0; /* Margin around the box */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}

.expiration-box h2 {
    margin-bottom: 20px; /* Space below the header */
    color: #333; /* Darker text for the header */
    font-size: 24px; /* Font size for the header */
    text-align: center; /* Center the header text */
}

.expiration-box-container {
    display: flex; /* Use flexbox for layout */
    flex-direction: row; /* Arrange items horizontally */
    gap: 15px; /* Space between items */
    justify-content: space-between; /* Space out items evenly */
}

.expiration-box-item {
    display: flex; /* Use flexbox for items */
    align-items: center; /* Center items vertically */
    background-color: #fff; /* White background for items */
    padding: 77px; /* Padding inside each item */
    border-radius: 5px; /* Rounded corners for items */
    border: 1px solid #ddd; /* Border for items */
    transition: background-color 0.3s; /* Smooth background color transition */
    flex: 1; /* Allow items to grow and take equal space */
}

.expiration-box-item:hover {
    background-color: #e7f3ff; /* Light blue background on hover */
}

.expiration-box-item h3 {
    margin: 0 10px; /* Space between icon and text */
    flex-grow: 1; /* Allow text to grow and take available space */
    font-size: 18px; /* Font size for the text */
    color: #555; /* Darker text color */
}

.expiration-box-item span {
    font-weight: bold; /* Bold text for values */
    color: #333; /* Darker text for values */
}

.expiration-box-item i {
    font-size: 39px; /* Size of Font Awesome icons */
    margin-right: 25px;
    margin-bottom: 10px;
    color: #007bff; /* Color for icons */
}

        h4 {
    text-align: left; /* Center align the text */
    font-size: 24px; /* Adjust the font size */
    color: black; /* Set the text color */
    margin-left: 120px ;
    margin-bottom: 20px; /* Add margin bottom for spacing */
}
    
        /* CSS for expiration details table */
        #expirationDetails {
    margin: 0 auto; /* Center the table horizontally */
    width: 80%; /* Adjust the width as needed */
    margin-right: 190px;
}
        #expirationDetails td, #expirationDetails th {
            
            border: 1px solid black;
            padding: 2px;
            text-align: center;
        }
       
        #expirationDetails th {
            padding-top: 12px;
            padding-bottom: 12px;
            background-color:grey;
            color: black;
        }

        .expiration-box-item {
    display: grid;
    grid-template-columns: auto 1fr;
    align-items: center;
}

.expiration-box-item span {
    font-size: 48px; /* Adjust the size of the icons */
    margin-right: 10px; /* Add some spacing between the icon and the text */
}


.expiration-box-item .icon {
    justify-self: center;
    margin-right: 10px; /* Adjust as needed */
}

.expiration-box-item h3 {
    margin: 0;
}

/* CSS for status text colors */
.status-expired {
    color: #cc0000; /* Dark red color for expired */
}

.status-valid {
    color: #006600; /* Dark green color for valid */
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

/* Styles for action icon */
.action-icon {
    font-size: 18px; /* Adjust the size of the icon */
    color: #ff0000; /* Red color for the trash icon */
    cursor: pointer;
    transition: color 0.3s;
}

.action-icon:hover {
    color: #cc0000; /* Darker red when hovered */
}

    /* CSS for notification container */
    .notification-container {
    position: relative;
    display: inline-block;
}

.notification-icon {
    font-size: 24px;
    color: whitesmoke;
    cursor: pointer;
    
}

.notification-dropdown {
    display: none; /* Hidden by default */
    position: absolute;
    right: 0;
    top: 100%;
    background-color: white;
    border: 1px solid #ddd;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    width: 250px;
    z-index: 1000;
}

.notification-dropdown ul {
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 24px;
    color: black;
}

.notification-dropdown li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.notification-dropdown li:last-child {
    border-bottom: none;
}



    </style>
</head>
<body>
<div class="header">
    <div class="title">Sales and Inventory System</div>

   
    <div class="notification-container">
    <span class="notification-bell" onclick="toggleNotifications(event)">
        &#128276; <!-- Notification bell icon -->
        <span id="notificationCount" class="notification-count">0</span> <!-- Notification count -->
    </span>
    <div id="notificationDropdown" class="notification-dropdown" style="display: none;">
        <ul id="notificationList">
            <!-- Notifications will be dynamically populated here -->
        </ul>
    </div>
</div>


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
    
    <!-- User Menu -->
    <div class="user-menu">
    <a href="user_management.php" onclick="navigateTo('user_management')">
    <i class="fas fa-users"></i>
    <span>Users</span>
</a>

<a href="#" class="admin-settings-icon" onclick="confirmSettings()">
    <i class="fas fa-cogs"></i>
            <span>Settings</span>
        </a>
    
    </div>
</div>
    
</div>
<div class="content">
    <button class="logout-btn" onclick="confirmLogout()">Logout</button>
    <!-- Expiration Notification Box -->
    <div class="expiration-box">
    <h2> Notifications</h2>
    <div class="expiration-box-container">
        <div class="expiration-box-item">
            <i class="fas fa-calendar-day"></i><h3>EXPIRES TODAY </h3>
            <span id="expiresToday"></span>
        </div>

        <div class="expiration-box-item">
            <i class="fas fa-calendar-week"></i><h3>EXPIRES IN 7 DAYS </h3>
            <span id="expiresIn7Days"></span>
        </div>

        <div class="expiration-box-item">
            <i class="fas fa-calendar-alt"></i><h3>EXPIRES IN MONTHS </h3>
            <span id="expiresInMonths"></span>
        </div>

        <div class="expiration-box-item">
            <i class="fas fa-calendar-times"></i><h3>EXPIRED IN YEARS </h3>
            <span id="expiredInYears"></span>
        </div>
    </div>
</div>

    <!-- Expiration Details Table -->
    <h4>Expiration Details</h4>
    <table id="expirationDetails">
    <thead>
        <tr>
            <th>PRODUCT ID</th>
            <th>PRODUCT NAME</th>
            <th>EXPIRATION DATE</th>
            <th>STATUS</th>
            <th>ACTION</th>
        </tr>
    </thead>
    <tbody id="expirationDetailsBody">
        <!-- Data will be populated dynamically -->
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

        document.addEventListener('DOMContentLoaded', function() {
    console.log('Document loaded'); // Confirm script is running

    fetchProducts();
    fetchNotifications(); // Ensure this is called

    
    document.getElementById('settingsForm').addEventListener('submit', handleSettingsFormSubmit);
    document.getElementById('settingsModal').addEventListener('click', closeModalOnClick);
});

function fetchProducts() {
    fetch('getproducts.php')
        .then(response => response.json())
        .then(data => {
            const expirationDetailsBody = document.getElementById('expirationDetailsBody');
            expirationDetailsBody.innerHTML = ''; // Clear previous table rows

            let todayCount = 0;
            let in7DaysCount = 0;
            let inMonthsCount = 0;
            let inYearsCount = 0;

            let notifications = [];
            let notificationShown = false; // Flag for showing expired product notification

            data.forEach(product => {
                const expirationDate = new Date(product.expiration_date);
                const today = new Date();

                // Calculate time difference in days
                const timeDiff = Math.ceil((expirationDate - today) / (1000 * 60 * 60 * 24));

                // Check if product is expired and update notification counts
                if (timeDiff === 0) {
                    todayCount++;
                } else if (timeDiff <= 7) {
                    in7DaysCount++;
                } else if (timeDiff <= 30) {
                    inMonthsCount++;
                } else if (timeDiff > 365) {
                    inYearsCount++;
                }

                // Determine the status
                const status = expirationDate < today ? 'Expired' : 'Valid';

                // Add rows to the table with an icon for action
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.product_id}</td>
                    <td>${product.product_name}</td>
                    <td>${product.expiration_date}</td>
                    <td>${status}</td>
                    <td>
                        <i class="fas fa-eye action-icon" onclick="viewProduct(${product.product_id})"></i>
                    </td>
                `;
                expirationDetailsBody.appendChild(row);

                // Add expired product notifications
                if (timeDiff < 0) {
                    notifications.push(`Product ID: ${product.product_id} - ${product.product_name} has expired.`);
                } else if (timeDiff <= 1) {
                    notifications.push(`Product ID: ${product.product_id} - ${product.product_name} expires in ${timeDiff} day(s).`);
                }
            });

            // Update expiration notification counts
            document.getElementById('expiresToday').textContent = todayCount;
            document.getElementById('expiresIn7Days').textContent = in7DaysCount;
            document.getElementById('expiresInMonths').textContent = inMonthsCount;
            document.getElementById('expiredInYears').textContent = inYearsCount;

            // Display notifications in the dropdown
            const notificationList = document.getElementById('notificationList');
            notificationList.innerHTML = ''; // Clear previous notifications
            notifications.forEach(notification => {
                const li = document.createElement('li');
                li.textContent = notification;
                notificationList.appendChild(li);
            });
            document.getElementById('notificationCount').textContent = notifications.length;
        })
        .catch(error => console.error('Error fetching product data:', error));
}


function toggleNotifications(event) {
    event.preventDefault(); // Prevent default link behavior
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
}




// Example function for handling actions
function handleAction(productId) {
    Swal.fire({
        title: 'Action Required',
        text: 'Handling action for product ID: ' + productId,
        icon: 'info',
        confirmButtonText: 'OK'
    });
    // Implement your logic for handling actions here
}

// Example function for confirming logout
function confirmLogout() {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to logout?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'No, cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Logged Out!',
                'You have been logged out successfully.',
                'success'
            );
            // Implement logout logic here
        }
    });
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


    function handleAction(productId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to remove this product?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'No, cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Make a request to the PHP script to delete the product
            fetch('delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_id: productId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Removed!',
                        'The product has been removed.',
                        'success'
                    );
                    // Refresh the product list
                    fetchProducts();
                } else {
                    Swal.fire(
                        'Error!',
                        'There was a problem removing the product.',
                        'error'
                    );
                }
            })
            .catch(error => console.error('Error removing product:', error));
        }
    });
}



document.addEventListener('DOMContentLoaded', function() {
    // Fetch notifications and update UI
    fetchNotifications();

    // Function to fetch notifications
    function fetchNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                const notificationList = document.getElementById('notificationList');
                const notificationCount = document.getElementById('notificationCount');
                
                notificationList.innerHTML = ''; // Clear previous notifications

                if (data.notifications.length > 0) {
                    notificationCount.textContent = data.notifications.length;

                    data.notifications.forEach(notification => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.href = notification.link; // If you have a link for each notification
                        a.textContent = notification.message;
                        li.appendChild(a);
                        notificationList.appendChild(li);
                    });
                } else {
                    notificationCount.textContent = '0';
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    // Function to toggle notification dropdown
    window.toggleNotifications = function() {
        const dropdown = document.getElementById('notificationDropdown');
        const display = dropdown.style.display === 'block' ? 'none' : 'block';
        dropdown.style.display = display;
    }
});

function viewProduct(productId) {
    // Fetch product details from the server
    fetch('get_product_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show product details in an alert or modal
            const product = data.product;
            Swal.fire({
                title: `Product Details: ${product.product_name}`,
                html: `
                    <p><strong>Product ID:</strong> ${product.product_id}</p>
                    <p><strong>Name:</strong> ${product.product_name}</p>
                    <p><strong>Expiration Date:</strong> ${product.expiration_date}</p>
                `,
                icon: 'info',
                confirmButtonText: 'Close'
            });
        } else {
            Swal.fire(
                'Error!',
                'There was a problem fetching the product details.',
                'error'
            );
        }
    })
    .catch(error => console.error('Error fetching product details:', error));
}

function fetchProducts() {
    fetch('getproducts.php')
        .then(response => response.json())
        .then(data => {
            const expirationDetailsBody = document.getElementById('expirationDetailsBody');
            expirationDetailsBody.innerHTML = ''; // Clear previous table rows

            let notifications = [];

            data.forEach(product => {
                const expirationDate = new Date(product.expiration_date);
                const today = new Date();

                // Determine the status and only proceed if the product is expired
                if (expirationDate < today) {
                    const status = 'Expired';

                    // Add rows to the table with an icon for action
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${product.product_id}</td>
                        <td>${product.product_name}</td>
                        <td>${product.expiration_date}</td>
                        <td>${status}</td>
                        <td>
                            <i class="fas fa-eye action-icon" onclick="viewProduct(${product.product_id})"></i>
                        </td>
                    `;
                    expirationDetailsBody.appendChild(row);

                    // Add expired product notifications
                    notifications.push(`Product ID: ${product.product_id} - ${product.product_name} has expired.`);
                }
            });

            // Update notifications in the UI (optional)
            if (notifications.length > 0) {
                const notificationList = document.getElementById('notificationList');
                notificationList.innerHTML = ''; // Clear previous notifications
                notifications.forEach(notification => {
                    const li = document.createElement('li');
                    li.textContent = notification;
                    notificationList.appendChild(li);
                });
                document.getElementById('notificationCount').textContent = notifications.length;
            }
        })
        .catch(error => console.error('Error fetching product data:', error));
}


</script>


</body>
</html>