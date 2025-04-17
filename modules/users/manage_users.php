<?php
// modules/users/manage_users.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotLoggedIn();
if (!isManager()) {
    header("Location: ../../index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_employee'])) {
        // Add new employee
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name, email, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password, $firstName, $lastName, $email, $role]);
            $success = "Employee added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding employee: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_employee'])) {
        // Delete employee
        $userId = $_POST['user_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $success = "Employee deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error deleting employee: " . $e->getMessage();
        }
    }
}

// Get all employees
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY role, last_name, first_name");
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching employees: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees | Hanan Pharmacy</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/cashier.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="../dashboard/manager.php">Dashboard</a></li>
                    <li class="active"><a href="manage_users.php">Manage Employees</a></li>
                    <li><a href="../inventory/view_drugs.php">View Inventory</a></li>
                    <li><a href="../reports/sales_report.php">Sales Reports</a></li>
                    <li><a href="../reports/inventory/report.php">Inventory Reports</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <h1>Manage Employees</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="employee-actions">
                <button id="addEmployeeBtn" class="btn btn-primary">Add New Employee</button>
            </div>
            
            <!-- Add Employee Form (Initially Hidden) -->
            <div id="addEmployeeForm" class="form-container" style="display: none;">
                <h2>Add New Employee</h2>
                <form action="manage_users.php" method="post">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="manager">Manager</option>
                            <option value="pharmacist">Pharmacist</option>
                            <option value="store_coordinator">Store Coordinator</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
                    <button type="button" id="cancelAddEmployee" class="btn btn-secondary">Cancel</button>
                </form>
            </div>
            
            <div class="employee-list">
                <h2>Current Employees</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo $employee['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['username']); ?></td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $employee['role'])); ?></td>
                                <td>
                                    <?php if ($employee['user_id'] != $_SESSION['user_id']): ?>
                                        <form action="manage_users.php" method="post" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $employee['user_id']; ?>">
                                            <button type="submit" name="delete_employee" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span>Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script src="../../assets/js/script.js"></script>
    <script>
        document.getElementById('addEmployeeBtn').addEventListener('click', function() {
            document.getElementById('addEmployeeForm').style.display = 'block';
        });
        
        document.getElementById('cancelAddEmployee').addEventListener('click', function() {
            document.getElementById('addEmployeeForm').style.display = 'none';
        });
    </script>
</body>
</html>