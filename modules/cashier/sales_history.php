<?php
// modules/sales/sales_history.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
if (!isPharmacist() && !isCashier()) {
    header("Location: ../../index.php");
    exit();
}

// Set default date range (last 7 days)
$startDate = date('Y-m-d', strtotime('-7 days'));
$endDate = date('Y-m-d');

// Handle date filter submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? $startDate;
    $endDate = $_POST['end_date'] ?? $endDate;
}

// Get sales history data
try {
    $query = "SELECT s.sale_id, s.sale_date, s.total_amount, 
                     GROUP_CONCAT(d.name SEPARATOR ', ') AS items,
                     COUNT(si.item_id) AS item_count
              FROM sales s
              JOIN sale_items si ON s.sale_id = si.sale_id
              JOIN drugs d ON si.drug_id = d.drug_id
              WHERE DATE(s.sale_date) BETWEEN :start_date AND :end_date
              AND s.user_id = :user_id
              GROUP BY s.sale_id
              ORDER BY s.sale_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':user_id' => $_SESSION['user_id']
    ]);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total sales amount for the period
    $totalQuery = "SELECT SUM(total_amount) 
                   FROM sales 
                   WHERE DATE(sale_date) BETWEEN :start_date AND :end_date
                   AND user_id = :user_id";
    $totalStmt = $pdo->prepare($totalQuery);
    $totalStmt->execute([
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':user_id' => $_SESSION['user_id']
    ]);
    $totalSales = $totalStmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Error fetching sales history: " . $e->getMessage());
    $sales = [];
    $totalSales = 0;
}

$pageTitle = "Sales History | Hanan Pharmacy";
include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/sales_history.css">
    <link rel="stylesheet" href="../../assets/css/cashier.css">


</head>

<body>
    <div class="dashboard-container">
        <?php include 'cashier_sidebar.php'?>

        <main class="content">
            <div class="dashboard-header">
                <h1><i class="fas fa-history"></i> Sales History</h1>
                <p class="welcome-message">View and analyze your sales records</p>
            </div>

            <div class="filter-container">
                <form method="post" class="date-filter">
                    <div class="form-group">
                        <label for="start_date"><i class="fas fa-calendar-alt"></i> From Date</label>
                        <input type="date" id="start_date" name="start_date"
                            value="<?php echo htmlspecialchars($startDate); ?>" required
                            max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_date"><i class="fas fa-calendar-alt"></i> To Date</label>
                        <input type="date" id="end_date" name="end_date"
                            value="<?php echo htmlspecialchars($endDate); ?>" required
                            max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button type="button" id="printReport" class="btn btn-secondary">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </form>
            </div>

            <div class="summary-cards">
                <div class="card revenue-card">
                    <div class="card-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Revenue</h3>
                        <p><?php echo number_format($totalSales, 2); ?> ETB</p>
                        <span class="card-period">
                            <?php echo date('M j', strtotime($startDate)); ?> -
                            <?php echo date('M j, Y', strtotime($endDate)); ?>
                        </span>
                    </div>
                </div>

                <div class="card transactions-card">
                    <div class="card-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Transactions</h3>
                        <p><?php echo count($sales); ?></p>
                        <span class="card-avg">
                            <?php echo count($sales) > 0 ? round(count($sales) / 7, 1) : 0; ?> avg/day
                        </span>
                    </div>
                </div>
            </div>

            <div class="data-section">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Sales Records</h2>
                    <div class="section-actions">
                        <span class="results-count">Showing <?php echo count($sales); ?> records</span>
                    </div>
                </div>

                <?php if (empty($sales)): ?>
                    <div class="no-data">
                        <i class="fas fa-info-circle"></i>
                        <p>No sales found for the selected period</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Date & Time</th>
                                    <th>Items</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td>#<?php echo $sale['sale_id']; ?></td>
                                        <td><?php echo date('M j, Y h:i A', strtotime($sale['sale_date'])); ?></td>
                                        <td class="items-list"><?php echo htmlspecialchars($sale['items']); ?></td>
                                        <td><?php echo $sale['item_count']; ?></td>
                                        <td class="amount"><?php echo number_format($sale['total_amount'], 2); ?> ETB</td>
                                        <td>
                                            <a href="sale_details.php?id=<?php echo $sale['sale_id']; ?>"
                                                class="btn btn-sm btn-view">
                                                <i class="fas fa-eye"></i> Details
                                            </a>
                                        </td>
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

    <script>
        document.getElementById('printReport').addEventListener('click', function () {
            window.print();
        });

        // Set start date max to end date and vice versa
        document.getElementById('start_date').addEventListener('change', function () {
            document.getElementById('end_date').min = this.value;
        });

        document.getElementById('end_date').addEventListener('change', function () {
            document.getElementById('start_date').max = this.value;
        });
    </script>
</body>

</html>