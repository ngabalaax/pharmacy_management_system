<?php
// modules/reports/inventory/report.php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';


checkPermission('view_inventory_reports');

// Check if user has permission to view inventory reports
if (!hasPermission('view_inventory_reports')) {
    header('Location: /login.php');
    exit();
}

// Set default date range (last 30 days)
$endDate = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-30 days'));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? $startDate;
    $endDate = $_POST['end_date'] ?? $endDate;

    // Validate dates
    if (strtotime($startDate) > strtotime($endDate)) {
        $error = "End date must be after start date";
    }
}

// Get report data from database
$inventoryReport = [];
$summaryData = [
    'total_items' => 0,
    'out_of_stock' => 0,
    'low_stock' => 0,
    'expiring_soon' => 0,
    'expired' => 0
];

try {
    // Query for summary data
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_items,
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN quantity > 0 AND quantity <= reorder_level THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date > CURDATE() THEN 1 ELSE 0 END) as expiring_soon,
            SUM(CASE WHEN expiry_date <= CURDATE() THEN 1 ELSE 0 END) as expired
        FROM inventory
    ");
    $stmt->execute();
    $summaryData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query for detailed report
    $stmt = $pdo->prepare("
        SELECT 
            i.id,
            i.drug_name,
            i.batch_number,
            i.category,
            i.quantity,
            i.reorder_level,
            i.supplier,
            i.unit_price,
            i.expiry_date,
            CASE 
                WHEN i.expiry_date <= CURDATE() THEN 'expired'
                WHEN i.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring'
                ELSE 'valid'
            END as expiry_status,
            CASE 
                WHEN i.quantity = 0 THEN 'out-of-stock'
                WHEN i.quantity <= i.reorder_level THEN 'low-stock'
                ELSE 'in-stock'
            END as stock_status
        FROM inventory i
        WHERE i.expiry_date BETWEEN :start_date AND :end_date
        ORDER BY i.expiry_date ASC
    ");
    $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
    $inventoryReport = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$pageTitle = "Inventory Report";
include __DIR__ . '/../../../includes/header.php';
?>
<link rel="stylesheet" href="../../../assets/css/inventory/report.css">
<link rel="stylesheet" href="../../../assets/css/sidebar.css">
<div class="dashboard-container">
    <!-- Add this sidebar section -->
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
                <li><a href="../../dashboard/manager.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

                <?php if (hasPermission('view_inventory')): ?>
                    <li class="active"><a href="/modules/reports/inventory/report.php"><i class="fas fa-boxes"></i>
                            Inventory Report</a></li>
                <?php endif; ?>

                <?php if (hasPermission('view_sales_reports')): ?>
                    <li><a href="/modules/reports/sales/report.php"><i class="fas fa-chart-line"></i> Sales Report</a></li>
                <?php endif; ?>

                <?php if (hasPermission('manage_inventory')): ?>
                    <li><a href="/modules/inventory/manage.php"><i class="fas fa-edit"></i> Manage Inventory</a></li>
                <?php endif; ?>

                <?php if (hasPermission('view_financial_reports')): ?>
                    <li><a href="/modules/reports/financial/report.php"><i class="fas fa-money-bill-wave"></i> Financial
                            Report</a></li>
                <?php endif; ?>

                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>
    <main class="dashboard-content"></main>
    <div class="content">
        <div class="dashboard-header">
            <h1><i class="fas fa-file-alt"></i> Inventory Report</h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon sales-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Items</h3>
                    <p><?php echo $summaryData['total_items']; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon revenue-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Out of Stock</h3>
                    <p><?php echo $summaryData['out_of_stock']; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                    <i class="fas fa-battery-quarter"></i>
                </div>
                <div class="stat-info">
                    <h3>Low Stock</h3>
                    <p><?php echo $summaryData['low_stock']; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Expiring Soon</h3>
                    <p><?php echo $summaryData['expiring_soon']; ?></p>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-container">
            <form method="post" class="date-filter">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control"
                        value="<?php echo htmlspecialchars($startDate); ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control"
                        value="<?php echo htmlspecialchars($endDate); ?>" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="printReport()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </form>
        </div>

        <!-- Detailed Report -->
        <div class="recent-transactions">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Inventory Details</h2>
            </div>

            <?php if (empty($inventoryReport)): ?>
                <div class="no-transactions">
                    <i class="fas fa-box-open"></i>
                    <p>No inventory items found for the selected date range</p>
                </div>
            <?php else: ?>
                <div class="transactions-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Drug Name</th>
                                <th>Batch No.</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Reorder Level</th>
                                <th>Unit Price</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventoryReport as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['drug_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['batch_number']); ?></td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td
                                        class="<?php echo $item['quantity'] == 0 ? 'expired' : ($item['quantity'] <= $item['reorder_level'] ? 'expiring' : ''); ?>">
                                        <?php echo htmlspecialchars($item['quantity']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['reorder_level']); ?></td>
                                    <td><?php echo '$' . number_format($item['unit_price'], 2); ?></td>
                                    <td class="<?php echo $item['expiry_status']; ?>">
                                        <?php echo date('M d, Y', strtotime($item['expiry_date'])); ?>
                                        <?php if ($item['expiry_status'] != 'valid'): ?>
                                            <span class="status-badge">
                                                <?php echo ucfirst($item['expiry_status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $item['stock_status']; ?>">
                                            <?php echo ucfirst(str_replace('-', ' ', $item['stock_status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </main>
</div>

<script>
    function printReport() {
        window.print();
    }
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>