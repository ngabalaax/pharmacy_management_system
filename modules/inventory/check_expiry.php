<?php
// modules/inventory/check_expiry.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotLoggedIn();
if (!isPharmacist() && !isStoreCoordinator()) {
    header("Location: ../../index.php");
    exit();
}

// Set default filter (expiring soon - within 30 days)
$filter = $_GET['filter'] ?? 'expiring';

try {
    // Base query for expiring drugs
    $query = "SELECT * FROM drugs WHERE 1=1";
    
    // Apply filter
    switch ($filter) {
        case 'expired':
            $query .= " AND expiry_date < CURDATE()";
            break;
        case 'critical': // Within 15 days
            $query .= " AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)";
            break;
        case 'all':
            // No additional filter
            break;
        default: // expiring (within 30 days)
            $query .= " AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    }
    
    $query .= " ORDER BY expiry_date ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $drugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get summary counts
    $expiredCount = $pdo->query("SELECT COUNT(*) FROM drugs WHERE expiry_date < CURDATE()")->fetchColumn();
    $criticalCount = $pdo->query("SELECT COUNT(*) FROM drugs WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)")->fetchColumn();
    $expiringCount = $pdo->query("SELECT COUNT(*) FROM drugs WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

} catch (PDOException $e) {
    error_log("Error fetching expiry data: " . $e->getMessage());
    $drugs = [];
    $expiredCount = 0;
    $criticalCount = 0;
    $expiringCount = 0;
}

$pageTitle = "Check Drug Expiry | Hanan Pharmacy";
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
    <style>
        /* Additional styles for expiry check */
        .expiry-filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .expiry-filter {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .expiry-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .expiry-filter.active {
            border-color: #3498db;
            background-color: #f8fafc;
        }
        
        .expiry-filter.expired {
            border-left: 4px solid #e74c3c;
        }
        
        .expiry-filter.critical {
            border-left: 4px solid #f39c12;
        }
        
        .expiry-filter.expiring {
            border-left: 4px solid #f1c40f;
        }
        
        .expiry-filter.all {
            border-left: 4px solid #3498db;
        }
        
        .days-remaining {
            font-weight: 600;
        }
        
        .days-remaining.expired {
            color: #e74c3c;
        }
        
        .days-remaining.critical {
            color: #f39c12;
        }
        
        .days-remaining.expiring {
            color: #f1c40f;
        }
        
        .days-remaining.valid {
            color: #2ecc71;
        }
        
        .expiry-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .expiry-filters {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="user-profile">
                <div class="user-info">
                    <span class="welcome">Welcome,</span>
                    <span class="username"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                    <span class="role">(<?php echo isPharmacist() ? 'Pharmacist' : 'Store Coordinator'; ?>)</span>
                </div>
            </div>

            <nav class="dashboard-nav">
                <ul>
                    <li><a href="../dashboard/<?php echo isPharmacist() ? 'pharmacist' : 'store_coordinator'; ?>.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php if (isStoreCoordinator()): ?>
                        <li><a href="register_drug.php"><i class="fas fa-plus-circle"></i> Register Drug</a></li>
                    <?php endif; ?>
                    <li><a href="view_drugs.php"><i class="fas fa-pills"></i> View Drugs</a></li>
                    <li class="active"><a href="check_expiry.php"><i class="fas fa-clock"></i> Check Expiry</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <div class="dashboard-header">
                <h1><i class="fas fa-clock"></i> Drug Expiry Check</h1>
                <p class="welcome-message">Monitor and manage expiring medications</p>
            </div>

            <div class="expiry-filters">
                <a href="?filter=expired" class="expiry-filter expired <?php echo $filter === 'expired' ? 'active' : ''; ?>">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <h3>Expired</h3>
                        <p><?php echo $expiredCount; ?> items</p>
                    </div>
                </a>
                
                <a href="?filter=critical" class="expiry-filter critical <?php echo $filter === 'critical' ? 'active' : ''; ?>">
                    <i class="fas fa-skull-crossbones"></i>
                    <div>
                        <h3>Critical (≤15 days)</h3>
                        <p><?php echo $criticalCount; ?> items</p>
                    </div>
                </a>
                
                <a href="?filter=expiring" class="expiry-filter expiring <?php echo $filter === 'expiring' ? 'active' : ''; ?>">
                    <i class="fas fa-hourglass-half"></i>
                    <div>
                        <h3>Expiring (≤30 days)</h3>
                        <p><?php echo $expiringCount; ?> items</p>
                    </div>
                </a>
                
                <a href="?filter=all" class="expiry-filter all <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i>
                    <div>
                        <h3>All Drugs</h3>
                        <p><?php echo count($drugs); ?> items</p>
                    </div>
                </a>
            </div>

            <div class="data-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-list-ol"></i> 
                        <?php echo ucfirst($filter); ?> Drugs
                        <span class="badge"><?php echo count($drugs); ?></span>
                    </h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>

                <?php if (empty($drugs)): ?>
                    <div class="no-data">
                        <i class="fas fa-check-circle"></i>
                        <p>No <?php echo $filter; ?> drugs found in inventory</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Drug Name</th>
                                    <th>Batch No.</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Days Remaining</th>
                                    <th>Status</th>
                                    <?php if (isStoreCoordinator()): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drugs as $drug): 
                                    $expiryDate = new DateTime($drug['expiry_date']);
                                    $today = new DateTime();
                                    $interval = $today->diff($expiryDate);
                                    $daysRemaining = $interval->days;
                                    $isExpired = $expiryDate < $today;
                                    
                                    if ($isExpired) {
                                        $status = 'Expired';
                                        $statusClass = 'expired';
                                        $daysClass = 'expired';
                                        $daysRemaining = -$daysRemaining;
                                    } elseif ($daysRemaining <= 15) {
                                        $status = 'Critical';
                                        $statusClass = 'critical';
                                        $daysClass = 'critical';
                                    } elseif ($daysRemaining <= 30) {
                                        $status = 'Expiring';
                                        $statusClass = 'expiring';
                                        $daysClass = 'expiring';
                                    } else {
                                        $status = 'Valid';
                                        $statusClass = 'valid';
                                        $daysClass = 'valid';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($drug['name']); ?></td>
                                        <td><?php echo htmlspecialchars($drug['batch_number']); ?></td>
                                        <td class="<?php echo $drug['quantity'] < 10 ? 'low-stock' : ''; ?>">
                                            <?php echo $drug['quantity']; ?>
                                        </td>
                                        <td><?php echo $expiryDate->format('M j, Y'); ?></td>
                                        <td class="days-remaining <?php echo $daysClass; ?>">
                                            <?php echo $isExpired ? "Expired $daysRemaining days ago" : "$daysRemaining days"; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                        <?php if (isStoreCoordinator()): ?>
                                            <td class="expiry-actions">
                                                <a href="edit_drug.php?id=<?php echo $drug['drug_id']; ?>" class="btn btn-sm btn-edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </td>
                                        <?php endif; ?>
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
        // Highlight row on hover
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = 'rgba(52, 152, 219, 0.05)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>
</body>
</html>