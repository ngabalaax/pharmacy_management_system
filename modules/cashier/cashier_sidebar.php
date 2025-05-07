<?php
// modules/dashboard/cashier_sidebar.php
?>
<link rel="stylesheet" href="../../assets/css/dashboard.css">
<link rel="stylesheet" href="../../assets/css/cashier.css">


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
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'cashier.php' ? 'active' : ''; ?>">
                <a href="/modules/cashier/cashier.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'process_sale.php' ? 'active' : ''; ?>">
                <a href="/modules/cashier/process_sale.php"><i class="fas fa-cash-register"></i> Process Sale</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'sales_history.php' ? 'active' : ''; ?>">
                <a href="/modules/cashier/sales_history.php"><i class="fas fa-history"></i> Sales History</a>
            </li>
            <li>
                <a href="/modules/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
</aside>