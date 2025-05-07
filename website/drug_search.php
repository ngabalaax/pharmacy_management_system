<?php
require_once '../config/database.php';
$pageTitle = "Drug Search";
// Get search parameters
$searchTerm = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$inStockOnly = isset($_GET['in_stock']);

try {
    // Build the query
    $query = "SELECT * FROM drugs WHERE is_active = TRUE";
    $params = [];
    
    if (!empty($searchTerm)) {
        $query .= " AND (name LIKE ? OR generic_name LIKE ?)";
        $searchParam = "%$searchTerm%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($category)) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($inStockOnly) {
        $query .= " AND quantity > 0";
    }
    
    $query .= " ORDER BY name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $drugs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error searching drugs: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Hanan Pharmacy</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .drug-search-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .search-results-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .search-results-header h2 {
            color: #1e3a8a;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .search-query {
            color: #64748b;
            font-size: 1.1rem;
        }
        
        .drug-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .drug-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .drug-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        .drug-header {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .drug-header h3 {
            color: #1e293b;
            margin: 0;
            font-size: 1.25rem;
        }
        
        .generic-name {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0.25rem 0 0;
        }
        
        .drug-details p {
            margin: 0.5rem 0;
            color: #4a5568;
        }
        
        .stock-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .in-stock {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .out-of-stock {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .price {
            font-weight: 700;
            color: #1e3a8a;
            font-size: 1.1rem;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            background: #f8fafc;
            border-radius: 10px;
            margin-top: 2rem;
        }
        
        .no-results i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }
        
        .no-results p {
            color: #64748b;
            font-size: 1.1rem;
        }
        
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .back-to-search {
            display: inline-block;
            margin-top: 1.5rem;
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .back-to-search:hover {
            color: #1e3a8a;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include '../includes/website_header.php'; ?>
    
    <main class="drug-search-container">
        <div class="search-results-header">
            <h2>Medication Search Results</h2>
            <p class="search-query">Showing results for: 
                <strong>"<?php echo htmlspecialchars($searchTerm); ?>"</strong>
                <?php if (!empty($category)): ?>
                    in category: <strong><?php echo htmlspecialchars(ucfirst($category)); ?></strong>
                <?php endif; ?>
                <?php if ($inStockOnly): ?>
                    (In Stock Only)
                <?php endif; ?>
            </p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php elseif (empty($drugs)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <p>No medications found matching your search criteria.</p>
                <a href="../index.php" class="back-to-search">
                    <i class="fas fa-arrow-left"></i> Back to Search
                </a>
            </div>
        <?php else: ?>
            <div class="drug-grid">
                <?php foreach ($drugs as $drug): ?>
                    <div class="drug-card">
                        <div class="drug-header">
                            <h3><?php echo htmlspecialchars($drug['name']); ?></h3>
                            <?php if (!empty($drug['generic_name'])): ?>
                                <p class="generic-name"><?php echo htmlspecialchars($drug['generic_name']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="drug-details">
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($drug['category']); ?></p>
                            <p><strong>Manufacturer:</strong> <?php echo htmlspecialchars($drug['manufacturer'] ?? 'N/A'); ?></p>
                            <p><strong>Price:</strong> <span class="price">$<?php echo number_format($drug['selling_price'], 2); ?></span></p>
                            <p class="stock-status <?php echo $drug['quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                <?php echo $drug['quantity'] > 0 ? 'In Stock (' . $drug['quantity'] . ' available)' : 'Currently Out of Stock'; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <a href="../index.php" class="back-to-search">
                <i class="fas fa-arrow-left"></i> Back to Search
            </a>
        <?php endif; ?>
    </main>
    
    <?php include '../includes/system_footer.php'; ?>
</body>
</html>