<?php
// modules/dashboard/store_coordinator_sidebar.php
?>
<link rel="stylesheet" href="../../assets/css/dashboard.css">

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
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'store_coordinator.php' ? 'active' : ''; ?>">
                <a href="/modules/store_coordinator/store_coordinator.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'register_drug.php' ? 'active' : ''; ?>">
                <a href="/modules/store_coordinator/register_drug.php"><i class="fas fa-plus-circle"></i> Register Drug</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'view_drugs.php' ? 'active' : ''; ?>">
                <a href="/modules/store_coordinator/view_drugs.php"><i class="fas fa-pills"></i> View Drugs</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'check_expiry.php' ? 'active' : ''; ?>">
                <a href="/modules/store_coordinator/check_expiry.php"><i class="fas fa-clock"></i> Check Expiry</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'report.php' ? 'active' : ''; ?>">
                <a href="/modules/store_coordinator/inventory/report.php"><i class="fas fa-file-alt"></i> Inventory Reports</a>
            </li>
            <li>
                <a href="/modules/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
</aside>