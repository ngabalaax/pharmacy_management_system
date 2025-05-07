<?php
// modules/dashboard/pharmacist.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
if (!isPharmacist()) {
    header("Location: ../../index.php");
    exit();
}

// Get pharmacist-specific data
try {
    // Today's sales count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales 
                          WHERE DATE(sale_date) = CURDATE() 
                          AND user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $todaySales = $stmt->fetchColumn();

    // Low stock drugs count
    $lowStockDrugs = $pdo->query("SELECT COUNT(*) FROM drugs 
                                 WHERE quantity < 10")->fetchColumn();

    // Recent sales
    $stmt = $pdo->prepare("SELECT sale_id, sale_date, total_amount 
                          FROM sales 
                          WHERE user_id = :user_id
                          ORDER BY sale_date DESC 
                          LIMIT 5");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
    $todaySales = 0;
    $lowStockDrugs = 0;
    $recentSales = [];
}

$pageTitle = "Pharmacist Dashboard | Hanan Pharmacy";
include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/cashier.css">

</head>

<body>
    <div class="dashboard-container">
        <?php include 'pharmacist_sidebar.php'; ?>

        <main class="content">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Pharmacist Dashboard</h1>
                <p class="welcome-message">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</p>
            </div>

            <div class="dashboard-cards">
                <div class="card sales-card">
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-content">
                        <h3>Today's Sales</h3>
                        <p><?php echo $todaySales; ?></p>
                        <span class="card-trend <?php echo $todaySales > 0 ? 'up' : 'neutral'; ?>">
                            <i class="fas fa-arrow-<?php echo $todaySales > 0 ? 'up' : 'right'; ?>"></i>
                            Today
                        </span>
                    </div>
                </div>

                <div class="card stock-card">
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-content">
                        <h3>Low Stock Drugs</h3>
                        <p><?php echo $lowStockDrugs; ?></p>
                        <span class="card-alert <?php echo $lowStockDrugs > 0 ? 'alert' : 'safe'; ?>">
                            <?php echo $lowStockDrugs > 0 ? 'Attention Needed' : 'All Good'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="recent-activity">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Recent Sales</h2>
                    <a href="../sales/sales_history.php" class="view-all">View All <i
                            class="fas fa-chevron-right"></i></a>
                </div>

                <?php if (empty($recentSales)): ?>
                    <div class="no-activity">
                        <i class="fas fa-info-circle"></i>
                        <p>No recent sales found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Date & Time</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td>#<?php echo $sale['sale_id']; ?></td>
                                        <td><?php echo date('M j, Y h:i A', strtotime($sale['sale_date'])); ?></td>
                                        <td><?php echo number_format($sale['total_amount'], 2); ?> ETB</td>
                                        <td><span class="status-badge completed">Completed</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>

</html>