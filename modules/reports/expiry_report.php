<?php
// modules/reports/expiry_report.php 
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

checkPermission('view_inventory_reports'); // Check if user has permission to view expiry reports
if (!hasPermission('view_expiry_reports')) {
    header('Location: /access_denied.php');
    exit();
}

// Set default date range (next 90 days)
$endDate = date('Y-m-d', strtotime('+90 days'));
$startDate = date('Y-m-d');
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? $startDate;
    $endDate = $_POST['end_date'] ?? $endDate;

    // Validate dates
    if (strtotime($startDate) > strtotime($endDate)) {
        $error = "End date must be after start date";
    }
}

// Get expiry report data from database
$expiryReport = [];
$summaryData = [
    'total_expiring' => 0,
    'expired_items' => 0,
    'critical_items' => 0,
    'total_value' => 0
];

try {
    // Query for summary data - Fixed table name from 'inventory' to 'drugs'
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_expiring,
            SUM(CASE WHEN expiry_date <= CURDATE() THEN 1 ELSE 0 END) as expired_items, 
            SUM(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as critical_items, 
            SUM(quantity * unit_price) as total_value 
        FROM drugs 
        WHERE expiry_date BETWEEN :start_date AND :end_date 
        AND is_active = TRUE
    ");
    $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
    $summaryData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query for detailed report - Fixed table name and column names to match your database
    $stmt = $pdo->prepare("
        SELECT 
            d.drug_id as id, 
            d.name as drug_name, 
            d.batch_number, 
            d.category, 
            d.quantity, 
            d.unit_price,
            d.expiry_date, 
            DATEDIFF(d.expiry_date, CURDATE()) as days_remaining, 
            (d.quantity * d.unit_price) as total_value,
            CASE 
                WHEN d.expiry_date <= CURDATE() THEN 'expired' 
                WHEN DATEDIFF(d.expiry_date, CURDATE()) <= 30 THEN 'critical' 
                ELSE 'warning' 
            END as expiry_status 
        FROM drugs d 
        WHERE d.expiry_date BETWEEN :start_date AND :end_date 
        AND d.is_active = TRUE
        ORDER BY d.expiry_date ASC
    ");
    $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
    $expiryReport = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$pageTitle = "Drug Expiry Report";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Hanan Pharmacy</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/report.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="../dashboard/manager.php">Dashboard</a></li>
                    <li><a href="sales_report.php">Sales Report</a></li>
                    <li><a href="inventory/report.php">Inventory Report</a></li>
                    <li class="active"><a href="expiry_report.php">Expiry Report</a></li>
                    <li><a href="../../logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <div class="content">
            <div class="dashboard-header">
                <h1><i class="fas fa-hourglass-half"></i> Drug Expiry Report</h1>
                <p class="text-muted">Track drugs that are expiring soon or have already expired</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Expiring</h3>
                        <p><?php echo $summaryData['total_expiring']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Already Expired</h3>
                        <p><?php echo $summaryData['expired_items']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #d35400, #e67e22);">
                        <i class="fas fa-skull-crossbones"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Critical (≤30 days)</h3>
                        <p><?php echo $summaryData['critical_items']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Value</h3>
                        <p>$<?php echo number_format($summaryData['total_value'], 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="filter-container">
                <form method="post" class="date-filter">
                    <div class="form-group">
                        <label for="start_date">From Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                            value="<?php echo htmlspecialchars($startDate); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="end_date">To Date</label>
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
                        <button type="button" class="btn btn-danger" onclick="exportToCSV()">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </button>
                    </div>
                </form>
            </div>

            <!-- Detailed Report -->
            <div class="recent-transactions">
                <div class="section-header">
                    <h2><i class="fas fa-list-ul"></i> Expiry Details</h2>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> Drugs expiring within 30 days are marked as critical.
                    </div>
                </div>

                <?php if (empty($expiryReport)): ?>
                    <div class="no-transactions">
                        <i class="fas fa-box-open"></i>
                        <p>No drugs found expiring in the selected date range</p>
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
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Expiry Date</th>
                                    <th>Days Remaining</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expiryReport as $item): ?>
                                    <tr class="<?php echo $item['expiry_status']; ?>">
                                        <td><?php echo htmlspecialchars($item['drug_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['batch_number']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td>$<?php echo number_format($item['total_value'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($item['expiry_date'])); ?></td>
                                        <td>
                                            <?php if ($item['days_remaining'] < 0): ?>
                                                <span class="expired">Expired <?php echo abs($item['days_remaining']); ?> days
                                                    ago</span>
                                            <?php else: ?>
                                                <?php echo $item['days_remaining']; ?> days
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $item['expiry_status']; ?>">
                                                <?php echo ucfirst($item['expiry_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/modules/inventory/view_drug.php?id=<?php echo $item['id']; ?>"
                                                class="btn btn-sm btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function printReport() {
            window.print();
        }

        function exportToCSV() {
            // Convert dates to readable format for filename
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const formattedStart = startDate.split('-').reverse().join('-');
            const formattedEnd = endDate.split('-').reverse().join('-');
            const filename = `drug_expiry_report_${formattedStart}_to_${formattedEnd}.csv`;

            // Create CSV content
            let csvContent = "Drug Name,Batch No.,Category,Quantity,Unit Price,Total Value,Expiry Date,Days Remaining,Status\n";

            <?php foreach ($expiryReport as $item): ?>
                csvContent += `"<?php echo addslashes($item['drug_name']); ?>",` +
                    `"<?php echo addslashes($item['batch_number']); ?>",` +
                    `"<?php echo addslashes($item['category']); ?>",` +
                    `<?php echo $item['quantity']; ?>,` +
                    `<?php echo $item['unit_price']; ?>,` +
                    `<?php echo $item['total_value']; ?>,` +
                    `"<?php echo date('M d, Y', strtotime($item['expiry_date'])); ?>",` +
                    `<?php echo $item['days_remaining']; ?>,` +
                    `"<?php echo ucfirst($item['expiry_status']); ?>"\n`;
            <?php endforeach; ?>

            // Create download link
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

    <style>
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge.expired {
            background-color: #e74c3c;
            color: white;
        }

        .status-badge.critical {
            background-color: #f39c12;
            color: white;
        }

        .status-badge.warning {
            background-color: #f1c40f;
            color: #2c3e50;
        }

        tr.expired {
            background-color: rgba(231, 76, 60, 0.1) !important;
        }

        tr.critical {
            background-color: rgba(243, 156, 18, 0.1) !important;
        }

        tr.warning {
            background-color: rgba(241, 196, 15, 0.1) !important;
        }
    </style>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>