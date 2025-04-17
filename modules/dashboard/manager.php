<?php
// modules/dashboard/manager.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotLoggedIn();

// Get counts for dashboard
try {
    $drugCount = $pdo->query("SELECT COUNT(*) FROM drugs")->fetchColumn();
    $employeeCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $todaySales = $pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn();
    $expiringSoon = $pdo->query("SELECT COUNT(*) FROM drugs WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Hanan Pharmacy</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/cashier.css">

</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li class="active"><a href="manager.php">Dashboard</a></li>
                    <li><a href="../users/manage_users.php">Manage Employees</a></li>
                    <li><a href="../inventory/view_drugs.php">View Inventory</a></li>
                    <li><a href="../reports/sales_report.php">Sales Reports</a></li>
                    <li><a href="../reports/inventory/report.php">Inventory Reports</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <h1>Manager Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</p>
            
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Total Drugs</h3>
                    <p><?php echo $drugCount; ?></p>
                </div>
                
                <div class="card">
                    <h3>Total Employees</h3>
                    <p><?php echo $employeeCount; ?></p>
                </div>
                
                <div class="card">
                    <h3>Today's Sales</h3>
                    <p><?php echo $todaySales; ?></p>
                </div>
                
                <div class="card">
                    <h3>Expiring Soon</h3>
                    <p><?php echo $expiringSoon; ?></p>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Recent activity would be populated here -->
                        <tr>
                            <td>2025-01-15</td>
                            <td>Added new drug: Paracetamol</td>
                            <td>Store Coordinator</td>
                        </tr>
                        <tr>
                            <td>2025-01-14</td>
                            <td>Processed sale #1001</td>
                            <td>Cashier</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>