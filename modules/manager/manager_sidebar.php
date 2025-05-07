<?php
// modules/dashboard/manager_sidebar.php
?>
<link rel="stylesheet" href="../assets/css/sidebar.css">
<aside class="sidebar">
    <div class="user-profile">
        <div class="user-info">
            <span class="welcome">Welcome,</span>
            <span class="username"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
            <span class="role">(<?php echo ucfirst($_SESSION['role']); ?>)</span>
        </div>
    </div>

    <nav class="dashboard-nav">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'manager.php' ? 'active' : ''; ?>">
                <a href="/modules/manager/manager.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : ''; ?>">
                <a href="/modules/manager/manage_users.php"><i class="fas fa-users-cog"></i> Manage Employees</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'view_drugs.php' ? 'active' : ''; ?>">
                <a href="/modules/manager/view_drugs.php"><i class="fas fa-pills"></i> View Inventory</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'sales_report.php' ? 'active' : ''; ?>">
                <a href="/modules/manager/sales_report.php"><i class="fas fa-chart-line"></i> Sales Reports</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'report.php' ? 'active' : ''; ?>">
                <a href="/modules/manager/inventory/report.php"><i class="fas fa-clipboard-list"></i> Inventory Reports</a>
            </li>
            <li>
                <a href="/modules/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
</aside>