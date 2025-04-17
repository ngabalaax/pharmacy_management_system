<?php
// modules/dashboard/store_coordinator.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotLoggedIn();
if (!isStoreCoordinator()) {
    header("Location: ../../index.php");
    exit();
}

// Get store coordinator-specific data
try {
    // Total drugs count
    $totalDrugs = $pdo->query("SELECT COUNT(*) FROM drugs")->fetchColumn();

    // Expiring soon count (within 30 days)
    $expiringSoon = $pdo->query("SELECT COUNT(*) FROM drugs 
                                WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

    // Low stock count (quantity < 10)
    $lowStock = $pdo->query("SELECT COUNT(*) FROM drugs WHERE quantity < 10")->fetchColumn();

    // Recent inventory updates
    $stmt = $pdo->prepare("SELECT name, batch_number, quantity, expiry_date, 
                          CASE 
                              WHEN expiry_date < CURDATE() THEN 'expired'
                              WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring'
                              ELSE 'valid'
                          END as status
                          FROM drugs 
                          ORDER BY created_at DESC 
                          LIMIT 5");
    $stmt->execute();
    $recentUpdates = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
    $totalDrugs = 0;
    $expiringSoon = 0;
    $lowStock = 0;
    $recentUpdates = [];
}

$pageTitle = "Store Coordinator Dashboard | Hanan Pharmacy";
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
        /* Additional custom styles for store coordinator dashboard */
        .inventory-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .inventory-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .inventory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .inventory-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .total-drugs::before {
            background: linear-gradient(90deg, #3498db, #2980b9);
        }
        
        .expiring-soon::before {
            background: linear-gradient(90deg, #f39c12, #e67e22);
        }
        
        .low-stock::before {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }
        
        .inventory-card h3 {
            color: #7f8c8d;
            font-size: 1rem;
            margin: 0 0 0.5rem 0;
            font-weight: 500;
        }
        
        .inventory-card p {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: #2c3e50;
        }
        
        .inventory-card .card-icon {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 1.5rem;
            color: rgba(0,0,0,0.1);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-expired {
            background-color: #fadbd8;
            color: #c0392b;
        }
        
        .status-expiring {
            background-color: #fef5e7;
            color: #f39c12;
        }
        
        .status-valid {
            background-color: #d5f5e3;
            color: #27ae60;
        }
        
        .low-quantity {
            color: #e74c3c;
            font-weight: 600;
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
                    <span class="role">(Store Coordinator)</span>
                </div>
            </div>

            <nav class="dashboard-nav">
                <ul>
                    <li class="active"><a href="store_coordinator.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../inventory/register_drug.php"><i class="fas fa-plus-circle"></i> Register Drug</a></li>
                    <li><a href="../inventory/view_drugs.php"><i class="fas fa-pills"></i> View Drugs</a></li>
                    <li><a href="../inventory/check_expiry.php"><i class="fas fa-clock"></i> Check Expiry</a></li>
                    <li><a href="../reports/inventory/report.php"><i class="fas fa-file-alt"></i> Inventory Reports</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <div class="dashboard-header">
                <h1><i class="fas fa-warehouse"></i> Store Coordinator Dashboard</h1>
                <p class="welcome-message">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! Here's your inventory overview.</p>
            </div>

            <div class="inventory-summary">
                <div class="inventory-card total-drugs">
                    <div class="card-icon">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h3>Total Drugs in Inventory</h3>
                    <p><?php echo $totalDrugs; ?></p>
                    <span class="card-trend">All medications</span>
                </div>

                <div class="inventory-card expiring-soon">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Expiring Soon (≤30 days)</h3>
                    <p><?php echo $expiringSoon; ?></p>
                    <span class="card-trend">Needs attention</span>
                </div>

                <div class="inventory-card low-stock">
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Low Stock Items (<10)</h3>
                    <p><?php echo $lowStock; ?></p>
                    <span class="card-trend">Reorder needed</span>
                </div>
            </div>

            <div class="recent-activity">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Recent Inventory Updates</h2>
                    <a href="../inventory/view_drugs.php" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
                </div>

                <?php if (empty($recentUpdates)): ?>
                    <div class="no-data">
                        <i class="fas fa-info-circle"></i>
                        <p>No recent inventory updates found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Drug Name</th>
                                    <th>Batch Number</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUpdates as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['batch_number']); ?></td>
                                        <td class="<?php echo $item['quantity'] < 10 ? 'low-quantity' : ''; ?>">
                                            <?php echo $item['quantity']; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($item['expiry_date'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $item['status']; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
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
        // Simple animation for cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.inventory-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate__animated', 'animate__fadeInUp');
            });
        });
    </script>
</body>
</html>