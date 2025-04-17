<?php
// modules/dashboard/cashier.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotLoggedIn();
if (!isCashier()) {
    header("Location: ../index.php");
    exit();
}

// Initialize variables with default values
$todaySales = 0;
$todayRevenue = 0.00;
$recentTransactions = [];

try {
    // Today's sales count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales 
                          WHERE DATE(sale_date) = CURDATE() 
                          AND user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $todaySales = $stmt->fetchColumn();

    // Today's revenue
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM sales 
                          WHERE DATE(sale_date) = CURDATE() 
                          AND user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $todayRevenue = $stmt->fetchColumn();

    // Recent transactions
    $stmt = $pdo->prepare("SELECT sale_id, sale_date, total_amount 
                          FROM sales 
                          WHERE user_id = :user_id
                          ORDER BY sale_date DESC 
                          LIMIT 5");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $recentTransactions = $stmt->fetchAll();

} catch (PDOException $e) {
    // Log error but don't die - show empty dashboard
    error_log("Error fetching dashboard data: " . $e->getMessage());
}

$pageTitle = "Cashier Dashboard | Hanan Pharmacy";
include '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cashier Dashboard | Hanan Pharmacy</title>
    <link rel="stylesheet" href="../../assets/css/cashier.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="user-profile">
                <div class="user-info">
                    <span class="welcome">Welcome,</span>
                    <span
                        class="username"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                    <span class="role">(<?php echo ucfirst($_SESSION['role']); ?>)</span>
                </div>
            </div>

            <nav class="dashboard-nav">
                <ul>
                    <li class="active"><a href="cashier.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../sales/process_sale.php"><i class="fas fa-cash-register"></i> Process Sale</a></li>
                    <li><a href="../sales/sales_history.php"><i class="fas fa-history"></i> Sales History</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Cashier Dashboard</h1>
            </div>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon sales-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Today's Sales</h3>
                        <p><?php echo $todaySales; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon revenue-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Today's Revenue</h3>
                        <p><?php echo number_format($todayRevenue, 2); ?> ETB</p>
                    </div>
                </div>
            </div>

            <div class="recent-transactions">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Recent Transactions</h2>
                </div>

                <div class="transactions-table">
                    <?php if (empty($recentTransactions)): ?>
                        <div class="no-transactions">
                            <i class="fas fa-info-circle"></i>
                            <p>No recent transactions found</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Time</th>
                                    <th>Amount</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo $transaction['sale_id']; ?></td>
                                        <td><?php echo date('h:i A', strtotime($transaction['sale_date'])); ?></td>
                                        <td><?php echo number_format($transaction['total_amount'], 2); ?> ETB</td>
                                        <td>
                                            <a href="../sales/sale_details.php?id=<?php echo $transaction['sale_id']; ?>"
                                                class="btn btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
<?php include '../../includes/footer.php'; ?>