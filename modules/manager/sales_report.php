<?php
// modules/reports/sales_report.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
if (!isManager() && !isPharmacist() && !isCashier()) {
    header("Location: ../../index.php");
    exit();
}

// Set default date range (last 30 days)
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');

// Handle date filter submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
}

// Get sales report data
try {
    $stmt = $pdo->prepare("
        SELECT s.sale_id, s.sale_date, s.total_amount, 
               u.first_name, u.last_name, 
               COUNT(si.item_id) AS item_count
        FROM sales s
        JOIN users u ON s.user_id = u.user_id
        JOIN sale_items si ON s.sale_id = si.sale_id
        WHERE DATE(s.sale_date) BETWEEN ? AND ?
        GROUP BY s.sale_id
        ORDER BY s.sale_date DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    $sales = $stmt->fetchAll();

    // Get total sales amount
    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) AS total_sales
        FROM sales
        WHERE DATE(sale_date) BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $totalSales = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("Error fetching sales report: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report | Hanan Pharmacy</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/cashier.css">
    <link rel="stylesheet" href="../../assets/css/sales_report.css">
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        <?php include 'manager_sidebar.php'?>

        <main class="content">
            <h1>Sales Report</h1> 

            <div class="report-filters">
                <form action="sales_report.php" method="post">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Filter</button>
                    <button type="button" id="printReport" class="btn btn-secondary">Print Report</button>
                </form>
            </div>

            <div class="report-summary">
                <h2>Summary</h2>
                <p>Report Period: <?php echo date('M j, Y', strtotime($startDate)); ?> to
                    <?php echo date('M j, Y', strtotime($endDate)); ?></p>
                <p>Total Sales: <?php echo number_format($totalSales, 2); ?></p>
                <p>Number of Transactions: <?php echo count($sales); ?></p>
            </div>

            <div class="report-details">
                <h2>Sales Details</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Date</th>
                            <th>Processed By</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo $sale['sale_id']; ?></td>
                                <td><?php echo date('M j, Y h:i A', strtotime($sale['sale_date'])); ?></td>
                                <td><?php echo htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']); ?></td>
                                <td><?php echo $sale['item_count']; ?></td>
                                <td><?php echo number_format($sale['total_amount'], 2); ?></td>
                                <td>
                                    <a href="sale_details.php?id=<?php echo $sale['sale_id']; ?>"
                                        class="btn btn-primary">View Details</a>
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
        document.getElementById('printReport').addEventListener('click', function () {
            window.print();
        });
    </script>
</body>

</html>