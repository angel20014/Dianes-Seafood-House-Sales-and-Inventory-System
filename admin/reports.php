<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
            margin-left: 250px; /* Align content with the sidebar */
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Align items to the start of the container */
            gap: 20px;
            width: calc(100% - 250px); /* Adjust width considering the sidebar */
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

        .reports-container {
            margin-top: 80px; /* Adjust margin to avoid overlapping with header */
            margin-right: 10px;
        }

        /* CSS for reports form */
        #reportForm {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
        }
        #reportForm label {
            display: block;
            margin-bottom: 10px;
            font-size: 20px;
        }
        #reportForm input,
        #reportForm select {
            display: block;
            margin-bottom: 20px;
            padding: 5px;
            font-size: 20px;
            box-sizing: border-box;
        }
        #reportForm button {
            display: block;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.1s;
        }
        #reportForm button:hover {
            background-color: #45a049;
        }
        #reportResult {
            margin-top: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
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

body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            margin-top: 10px;
        }

        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            margin-top: 10px;
        }

    </style>
</head>
<body>
    <div class="header">
        <div class="title">Sales and Inventory System   </div>

        <a href="#" class="admin-settings-icon" onclick="confirmSettings()">
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
    <a href="settings.php" onclick="navigateTo('expired')">
        <i class="fas fa-bell"></i>
        <span>Settings</span>
    </a>
</div>

    <div class="content">
    <h1>Generate Reports</h1>
    <form id="reportForm">
        <!-- Filter for Start Date -->
        <label for="startDate">Start Date:</label>
        <input type="date" id="startDate" name="startDate">

        <!-- Filter for End Date -->
        <label for="endDate">End Date:</label>
        <input type="date" id="endDate" name="endDate">

        <!-- Filter for Selecting Sales Type -->
        <label for="salesType">Select Sales Type:</label>
        <select id="salesType" name="salesType">
            <option value="product">Sales Report</option>
            <option value="inventory">Inventory Report</option>
        </select>

        <!-- Button to Generate Report -->
        <button type="submit">Generate Report</button>
    </form>

    <!-- Placeholder for the generated report -->
    <div id="reportResult"></div>
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
        // Function to handle navigation and update content
        function navigateTo(section) {
            var sections = document.querySelectorAll('.content');
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = 'none';
            }
            document.getElementById(section).style.display = 'block';
        }

        // Function to handle logout confirmation
        function confirmLogout() {
            var confirmLogout = confirm("Are you sure you want to log out?");
            if (confirmLogout) {
                logout();
            }
        }

        // Function to handle logout
        function logout() {
            window.location.href = "logout.php";
        }

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

    document.getElementById('reportForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const salesType = document.getElementById('salesType').value;

    if (salesType === 'product') {
        generateSalesReport(startDate, endDate);
    } else if (salesType === 'inventory') {
        generateInventoryReport();
    }
});

function generateSalesReport(startDate, endDate) {
    const query = `
        SELECT id, saleDate, product_name, quantity, price, total, orderType, customerType
        FROM sales
        WHERE saleDate BETWEEN '${startDate}' AND '${endDate}'
    `;

    sendQuery(query);
}

function generateInventoryReport() {
    const query = `
        SELECT product_id, product_code, product_name, category, current_stock, unit, price, expiration_date
        FROM products
        WHERE current_stock > 0
    `;

    sendQuery(query);
}

function sendQuery(query) {
    fetch('/generate-report', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ query }),
    })
    .then(response => response.json())
    .then(data => {
        // Render the report in the placeholder
        document.getElementById('reportResult').innerHTML = renderReport(data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function renderReport(data) {
    let html = '<table><thead><tr>';
    
    if (data.length > 0) {
        Object.keys(data[0]).forEach(key => {
            html += `<th>${key}</th>`;
        });
        html += '</tr></thead><tbody>';

        data.forEach(row => {
            html += '<tr>';
            Object.values(row).forEach(value => {
                html += `<td>${value}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';
    } else {
        html = '<p>No data found.</p>';
    }

    return html;
}
    </script>
</body>
</html>
