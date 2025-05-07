<?php
// modules/inventory/view_drugs.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
if ( !isManager()) {
    header("Location: ../../index.php");
    exit();
}


// Handle search and filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$expiryFilter = $_GET['expiry'] ?? '';

try {
    // Get all distinct categories for filter dropdown
    $categories = $pdo->query("SELECT DISTINCT category FROM drugs WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    
    // Base query
    $query = "SELECT * FROM drugs WHERE 1=1";
    $params = [];
    
    // Apply search filter
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    // Apply category filter
    if (!empty($category)) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    // Apply expiry filter
    if ($expiryFilter === 'expiring') {
        $query .= " AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($expiryFilter === 'expired') {
        $query .= " AND expiry_date < CURDATE()";
    }
    
    $query .= " ORDER BY name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $drugs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching drugs: " . $e->getMessage());
}

$pageTitle = "View Drugs | Hanan Pharmacy";
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
    <link rel="stylesheet" href="../../assets/css/view_drugs.css">
  
</head>

<body>
    <div class="dashboard-container">
    <?php include 'manager_sidebar.php'; ?>

        <main class="content">
            <div class="dashboard-header">
                <h1><i class="fas fa-pills"></i> Drug Inventory</h1>
                <p class="welcome-message">Manage and track your pharmacy's medications</p>
            </div>

            <div class="filter-container">
                <form method="get" class="filter-form">
                    <div class="form-group search-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search drugs..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    </div>
                    
                    <div class="form-group">
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <select name="expiry" class="filter-select">
                            <option value="">All Status</option>
                            <option value="expiring" <?php echo $expiryFilter === 'expiring' ? 'selected' : ''; ?>>Expiring Soon</option>
                            <option value="expired" <?php echo $expiryFilter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="view_drugs.php" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="data-section">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Drug List</h2>
                    <div class="section-actions">
                        <span class="results-count">
                            <?php echo count($drugs); ?> drug<?php echo count($drugs) !== 1 ? 's' : ''; ?> found
                        </span>
                    </div>
                </div>

                <?php if (empty($drugs)): ?>
                    <div class="no-data">
                        <i class="fas fa-box-open"></i>
                        <p>No drugs found matching your criteria</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Drug Name</th>
                                    <th>Category</th>
                                    <th>Batch No.</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <?php if (isStoreCoordinator()): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drugs as $drug): ?>
                                    <?php
                                    $expiryDate = strtotime($drug['expiry_date']);
                                    $today = time();
                                    $thirtyDaysFromNow = strtotime('+30 days');
                                    
                                    if ($expiryDate < $today) {
                                        $expiryStatus = 'Expired';
                                        $statusClass = 'expired';
                                    } elseif ($expiryDate < $thirtyDaysFromNow) {
                                        $expiryStatus = 'Expiring Soon';
                                        $statusClass = 'expiring';
                                    } else {
                                        $expiryStatus = 'Valid';
                                        $statusClass = 'valid';
                                    }
                                    
                                    $quantityClass = $drug['quantity'] < 10 ? 'low-stock' : '';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($drug['name']); ?></td>
                                        <td><?php echo htmlspecialchars($drug['category'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($drug['batch_number']); ?></td>
                                        <td class="<?php echo $quantityClass; ?>">
                                            <?php echo $drug['quantity']; ?>
                                            <?php if ($quantityClass): ?>
                                                <span class="status-badge warning">Low Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($drug['unit_price'], 2); ?> ETB</td>
                                        <td class="<?php echo $statusClass; ?>">
                                            <?php echo date('M j, Y', $expiryDate); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $expiryStatus; ?>
                                            </span>
                                        </td>
                                        <?php if (isStoreCoordinator()): ?>
                                            <td class="actions">
                                                <a href="edit_drug.php?id=<?php echo $drug['drug_id']; ?>" 
                                                   class="btn btn-sm btn-edit">
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
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.max = today;
            });
        });
    </script>
</body>
</html>
