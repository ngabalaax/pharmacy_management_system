<?php
// modules/reports/inventory/report.php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';

redirectIfNotLoggedIn();
if (!isStoreCoordinator() ) {
    header("Location: ../../index.php");
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

// Get report data
try {
    // Summary statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_items,
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN quantity > 0 AND quantity <= reorder_level THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date > CURDATE() THEN 1 ELSE 0 END) as expiring_soon,
            SUM(CASE WHEN expiry_date <= CURDATE() THEN 1 ELSE 0 END) as expired,
            SUM(quantity * unit_price) as total_value
        FROM drugs
        WHERE is_active = TRUE
    ");
    $stmt->execute();
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Detailed inventory
    $stmt = $pdo->prepare("
        SELECT 
            drug_id, name, batch_number, category, 
            quantity, reorder_level, unit_price, 
            expiry_date, supplier,
            CASE 
                WHEN quantity = 0 THEN 'out-of-stock'
                WHEN quantity <= reorder_level THEN 'low-stock'
                ELSE 'in-stock'
            END as stock_status,
            CASE 
                WHEN expiry_date <= CURDATE() THEN 'expired'
                WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring'
                ELSE 'valid'
            END as expiry_status,
            (quantity * unit_price) as total_value
        FROM drugs
        WHERE is_active = TRUE
        ORDER BY stock_status, expiry_date ASC
    ");
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$pageTitle = "Inventory Report | Hanan Pharmacy";
include __DIR__ . '/../../../includes/header.php';
?>
<link rel="stylesheet" href="../../../assets/css/dashboard.css">
<link rel="stylesheet" href="../../../assets/css/inventory_report.css">
<link rel="stylesheet" href="../../../assets/css/cashier.css">

<div class="dashboard-container">
    <?php include '../store_coordinator_sidebar.php';
    ?>

    <main class="content">
        <h1><i class="fas fa-boxes"></i> Inventory Report</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Date Filter -->
        <div class="report-filters">
            <form method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date"
                            value="<?php echo htmlspecialchars($startDate); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date"
                            value="<?php echo htmlspecialchars($endDate); ?>" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-print" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card total-items">
                <div class="card-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="card-content">
                    <h3>Total Items</h3>
                    <p><?php echo $summary['total_items']; ?></p>
                </div>
            </div>

            <div class="summary-card out-of-stock">
                <div class="card-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="card-content">
                    <h3>Out of Stock</h3>
                    <p><?php echo $summary['out_of_stock']; ?></p>
                </div>
            </div>

            <div class="summary-card low-stock">
                <div class="card-icon">
                    <i class="fas fa-battery-quarter"></i>
                </div>
                <div class="card-content">
                    <h3>Low Stock</h3>
                    <p><?php echo $summary['low_stock']; ?></p>
                </div>
            </div>

            <div class="summary-card expiring-soon">
                <div class="card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-content">
                    <h3>Expiring Soon</h3>
                    <p><?php echo $summary['expiring_soon']; ?></p>
                </div>
            </div>

            <div class="summary-card expired">
                <div class="card-icon">
                    <i class="fas fa-skull-crossbones"></i>
                </div>
                <div class="card-content">
                    <h3>Expired</h3>
                    <p><?php echo $summary['expired']; ?></p>
                </div>
            </div>

            <div class="summary-card total-value">
                <div class="card-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="card-content">
                    <h3>Total Value</h3>
                    <p>$<?php echo number_format($summary['total_value'], 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Detailed Report -->
        <div class="inventory-table-container">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Inventory Details</h2>
                <div class="table-actions">
                    <input type="text" id="searchInput" placeholder="Search inventory...">
                </div>
            </div>

            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Drug Name</th>
                        <th>Batch No.</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Reorder Level</th>
                        <th>Unit Price</th>
                        <th>Total Value</th>
                        <th>Expiry Date</th>
                        <th>Supplier</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $item): ?>
                        <tr class="<?php echo $item['stock_status'] . ' ' . $item['expiry_status']; ?>">
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['batch_number']); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['reorder_level']; ?></td>
                            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td>$<?php echo number_format($item['total_value'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($item['expiry_date'])); ?></td>
                            <td><?php echo htmlspecialchars($item['supplier']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $item['stock_status']; ?>">
                                    <?php echo ucfirst(str_replace('-', ' ', $item['stock_status'])); ?>
                                </span>
                                <?php if ($item['expiry_status'] !== 'valid'): ?>
                                    <span class="status-badge <?php echo $item['expiry_status']; ?>">
                                        <?php echo ucfirst($item['expiry_status']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    // Simple table search functionality
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const input = this.value.toLowerCase();
        const rows = document.querySelectorAll('.inventory-table tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    });
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>