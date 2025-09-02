<?php
 session_start();

 // Check if the user is logged in by verifying session variables
 if (!isset($_SESSION['user_id'])) {
     // If the user is not logged in, redirect to the login page
     header("Location: login.php");
     exit;
 }
 
include 'db.php';
// Fetch cashiers from the database
$result = $conn->query("SELECT cashier_id, username, name FROM cashiers");

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
.content {
            margin-left: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }

        .h1 {
            text-align: center;
        }
        .columns {
    display: flex;
    justify-content: space-between;
    width: 90%;
    gap: 20px; /* Add space between the columns */
}

.column {
    flex: 1; /* Allow both columns to grow equally */
    padding: 30px; /* Padding around each column */
    background-color: #f9f9f9; /* Light background for columns */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}

.column:last-child {
    margin-right: 0; /* Remove right margin from the last column */
}

.cashiers-container {
    padding: 15px;
    background-color: #f9f9f9; /* Light background color */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    margin-top: 10px; /* Space above the container */
   
}

.cashier-table-container {
    width: 100%; /* Full width for the table */
    overflow-x: auto; /* Allow horizontal scrolling if needed */
    margin-top: 20px; /* Space above the table */
  
}

table {
    width: 100%; /* Make table full width */
    border-collapse: collapse; /* Remove gaps between cells */
}

th, td {
    padding: 15px; /* Increased padding for better spacing */
    text-align: left; /* Align text to the left */
    border-bottom: 1px solid #ddd; /* Bottom border for rows */
}

th {
    background-color: #f2f2f2; /* Light background for headers */
}

tr:hover {
    background-color: #f5f5f5; /* Highlight row on hover */
}

.modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            justify-content: center; /* Centering */
            align-items: center; /* Centering */
        }

        .action-btn {
    background-color: #007bff; /* Bootstrap primary color */
    color: white;              /* Text color */
    padding: 10px 15px;       /* Padding for the button */
    border: none;             /* Remove border */
    border-radius: 5px;      /* Rounded corners */
    cursor: pointer;          /* Pointer cursor on hover */
    display: inline-flex;     /* Align icon and text */
    align-items: center;      /* Center items vertically */
    text-decoration: none;    /* Remove underline for links */
    transition: background-color 0.3s ease; /* Smooth transition for background color */
    font-size: 16px;          /* Font size */
}

td .action-btn {
    display: inline-flex; /* Ensure icons are displayed inline */
    margin-right: 10px; /* Add some space between icons */
    align-items: center;
}

td .action-btn:last-child {
    margin-right: 0; /* Remove the margin from the last button */
}

.modal {
    display: none; 
    position: fixed;
    z-index: 1; 
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 30%;
    border-radius: 8px;
}

.close-btn {
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-btn:hover,
.close-btn:focus {
    color: red;
    text-decoration: none;
    cursor: pointer;
}

.btn-submit {
    background-color: #4CAF50; /* Green */
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
}

.btn-cancel {
    background-color: #f44336; /* Red */
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
}

.cashier-table-container {
    margin-top: 20px;
}

.modal-content {
    padding: 20px;
    background-color: #f9f9f9;
}

.show-entries {
    margin-top: 20px;
    display: flex;
    align-items: center;
}

.show-entries span {
    margin-right: 10px;
    margin-left: 600px;
    font-size: 25px;
}

.show-entries button {
    margin-right: 5px; /* Space between buttons */
    padding: 5px 10px; /* Adjust padding as needed */
    cursor: pointer; /* Change cursor to pointer */
    border: 1px solid #ccc; /* Optional: add border */
    border-radius: 4px; /* Optional: rounded corners */
    background-color: #f0f0f0; /* Optional: button background */
    font-size: 25px;
   
}

.show-entries button:hover {
    background-color: #e0e0e0; /* Optional: change background on hover */
}

#message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            display: none; /* Hidden by default */
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
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
    <h1>CASHIER LIST</h1>
    
    <button onclick="openAddCashierModal()" class="btn-submit">Add Cashier</button>
    
    <div id="addCashierModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAddCashierModal()">&times;</span>
            <h2>Add Cashier</h2>
            <form id="createCashierForm" method="POST">
                <div class="form-group">
                    <label for="cashierName">Name:</label>
                    <input type="text" id="cashierName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="cashierUsername">Username:</label>
                    <input type="text" id="cashierUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="cashierPassword">Password:</label>
                    <input type="password" id="cashierPassword" name="password" required>
                </div>
                <button type="submit" class="btn-submit">ADD</button>
                <button type="button" class="btn-cancel" onclick="closeAddCashierModal()">Cancel</button>
            </form>
            <div id="message"></div> <!-- Area to display messages -->
        </div>
    </div>

    <div class="cashier-table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Password</th>
                <th>Name</th>
                <th>Date Added</th> <!-- Added Date column -->
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "salesrecord_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initial fetch to display 5 cashiers on page load
// Fetch cashiers from the database including date_added
$result = $conn->query("SELECT cashier_id, username, password, name, date_added FROM cashiers LIMIT 5");

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['cashier_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['password']) . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['date_added']) . "</td>"; // Display date_added
    echo "<td>
        <button onclick=\"openChangePasswordModal('" . htmlspecialchars($row['username']) . "')\" class='action-btn'>
            <i class='fas fa-key'></i> 
        </button>
        <button onclick=\"openEditNameModal('" . htmlspecialchars($row['cashier_id']) . "', '" . htmlspecialchars($row['name']) . "')\" class='action-btn'>
            <i class='fas fa-edit'></i> 
        </button>
    </td>";
    echo "</tr>";
}
?>
        </tbody>
    </table>
</div>



     <!-- Show Entries Dropdown (Initially Hidden) -->
    <!-- Show Entries Buttons -->
<div class="show-entries" id="showEntriesContainer">
    <span>Show entries:</span>
    <button onclick="updateEntries(1)">1</button>
    <button onclick="updateEntries(2)">2</button>
    <button onclick="updateEntries(5)">5</button>
    <button onclick="updateEntries(10)">10</button>
    <button onclick="updateEntries(20)">20</button>
    
</div>



<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeChangePasswordModal()">&times;</span>
        <h2>Change Password</h2>
        <form id="updatePasswordForm">
            <div class="form-group">
                <label for="cashierId">Cashier Username:</label>
                <input type="text" id="cashierId" name="cashierId" required readonly>
            </div>
            <div class="form-group">
                <label for="newCashierPassword">New Password:</label>
                <input type="password" id="newCashierPassword" name="newCashierPassword" required>
            </div>
            <button type="submit" class="btn-submit">Update Password</button>
        </form>
        <div id="responseMessage"></div>
    </div>
</div>

<!-- Edit Name Modal -->
<div id="editNameModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeEditNameModal()">&times;</span>
        <h2>Edit Cashier Name</h2>
        <form id="editNameForm">
            <div class="form-group">
                <label for="editCashierId">Cashier ID:</label>
                <input type="text" id="editCashierId" name="cashierId" required readonly>
            </div>
            <div class="form-group">
                <label for="editCashierName">Cashier Name:</label>
                <input type="text" id="editCashierName" name="name" required>
            </div>
            <button type="submit" class="btn-submit">Update Name</button>
        </form>
        <div id="editNameResponseMessage"></div>
    </div>
</div>

<!-- Add your existing Change Password Modal here -->

<script>

function updateEntries(value) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `fetch_cashiers.php?limit=${value}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.querySelector('.cashier-table-container tbody').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

document.getElementById('editNameForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const cashierId = document.getElementById('editCashierId').value;
    const newName = document.getElementById('editCashierName').value;

    fetch('update_cashier_name.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cashierId=${cashierId}&name=${encodeURIComponent(newName)}`
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === 'success') {
            // Locate row with cashier ID and update the name cell
            const row = document.querySelector(`tr[data-cashier-id="${cashierId}"]`);
            if (row) {
                row.querySelector('.cashier-name').innerText = newName;
            }

            // Display success message
            document.getElementById('editNameResponseMessage').innerText = 'Cashier name updated successfully.';
            closeEditNameModal();
        } else {
            document.getElementById('editNameResponseMessage').innerText = 'Error updating cashier name.';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('editNameResponseMessage').innerText = 'An error occurred. Try again.';
    });
});


    // Function to open the Edit Name Modal
function openEditNameModal(cashierId, name) {
    // Get modal and input elements
    const editNameModal = document.getElementById('editNameModal');
    const editCashierId = document.getElementById('editCashierId');
    const editCashierName = document.getElementById('editCashierName');
    
    // Set the input values
    editCashierId.value = cashierId;
    editCashierName.value = name;
    
    // Display the modal
    editNameModal.style.display = 'block';
}

// Function to close the Edit Name Modal
function closeEditNameModal() {
    const editNameModal = document.getElementById('editNameModal');
    editNameModal.style.display = 'none';
}

function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    // Handle creating a new cashier
    function openAddCashierModal() {
            document.getElementById('addCashierModal').style.display = 'block';
        }

        function closeAddCashierModal() {
            document.getElementById('addCashierModal').style.display = 'none';
        }

        // Handle creating a new cashier
        document.getElementById('createCashierForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('create_cashier.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log(response); // Check the response
        return response.json();
    })
    .then(data => {
        const messageDiv = document.getElementById('message');
        messageDiv.style.display = 'block'; // Show the message div
        messageDiv.className = ''; // Clear previous classes

        if (data.status === 'error') {
            // Display the error message
            messageDiv.classList.add('error');
            messageDiv.innerHTML = data.message; // Show error message
        } else {
            // Display the success message
            messageDiv.classList.add('success');
            messageDiv.innerHTML = data.message; // Show success message
            document.getElementById('createCashierForm').reset(); // Optionally clear the form
        }
    })
    .catch(error => console.error('Error:', error));
});

    function openChangePasswordModal(username) {
        document.getElementById('cashierId').value = username; // Set username in the modal
        document.getElementById('changePasswordModal').style.display = "flex"; // Show modal
    }

    function closeChangePasswordModal() {
        document.getElementById('changePasswordModal').style.display = "none"; // Hide modal
    }

    // Handle updating the cashier's password
    document.getElementById('updatePasswordForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        var formData = new FormData(this); // Create FormData object

        fetch('change_password.php', { // Ensure this points to the correct PHP file
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('responseMessage').innerText = data; // Show response message
            closeChangePasswordModal(); // Close modal after submission
        })
        .catch(error => {
            document.getElementById('responseMessage').innerText = "Error: " + error; // Handle errors
        });
    });

    
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
</script>

</body>
</html>
