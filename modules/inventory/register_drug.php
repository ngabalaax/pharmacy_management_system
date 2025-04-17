<?php
// modules/inventory/register_drug.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotLoggedIn();
if (!isStoreCoordinator()) {
    header("Location: ../../index.php");
    exit();
}

// Initialize variables
$errors = [];
$success = false;
$formData = [
    'name' => '',
    'generic_name' => '',
    'description' => '',
    'batch_number' => '',
    'barcode' => '',
    'quantity' => '',
    'reorder_level' => 10, // Default reorder level
    'unit_price' => '',
    'selling_price' => '',
    'expiry_date' => '',
    'supplier' => '',
    'category' => '',
    'storage_conditions' => 'Room Temperature' // Default storage
];

// Get active suppliers for dropdown
try {
    $suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers WHERE is_active = TRUE")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $suppliers = [];
    $errors[] = "Error loading suppliers: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => trim($_POST['name'] ?? ''),
        'generic_name' => trim($_POST['generic_name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'batch_number' => trim($_POST['batch_number'] ?? ''),
        'barcode' => trim($_POST['barcode'] ?? ''),
        'quantity' => trim($_POST['quantity'] ?? ''),
        'reorder_level' => trim($_POST['reorder_level'] ?? 10),
        'unit_price' => trim($_POST['unit_price'] ?? ''),
        'selling_price' => trim($_POST['selling_price'] ?? ''),
        'expiry_date' => trim($_POST['expiry_date'] ?? ''),
        'supplier' => trim($_POST['supplier'] ?? ''),
        'category' => trim($_POST['category'] ?? $_POST['new_category'] ?? ''),
        'storage_conditions' => trim($_POST['storage_conditions'] ?? 'Room Temperature')
    ];

    // calculate selling price if not provided
    if (empty($formData['selling_price'])) {
        $formData['selling_price'] = $formData['unit_price'] * 1.2; // Default selling price is 20% more than unit price
    }

    // Validate inputs
    if (empty($formData['name'])) $errors[] = "Drug name is required";
    if (empty($formData['batch_number'])) $errors[] = "Batch number is required";
    if (!is_numeric($formData['quantity']) || $formData['quantity'] < 1) $errors[] = "Valid quantity is required";
    if (!is_numeric($formData['reorder_level']) || $formData['reorder_level'] < 0) $errors[] = "Valid reorder level is required";
    if (!is_numeric($formData['unit_price']) || $formData['unit_price'] <= 0) $errors[] = "Valid unit price is required";
    if (!is_numeric($formData['selling_price']) || $formData['selling_price'] < $formData['unit_price']) {
        $errors[] = "Selling price must be greater than unit price";
    }
    if (empty($formData['expiry_date'])) $errors[] = "Expiry date is required";
    elseif (strtotime($formData['expiry_date']) < strtotime('today')) $errors[] = "Expiry date cannot be in the past";

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO drugs 
                                  (name, generic_name, description, batch_number, barcode, 
                                   quantity, reorder_level, unit_price, selling_price, 
                                   expiry_date, supplier, category, storage_conditions) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $formData['name'],
                $formData['generic_name'],
                $formData['description'],
                $formData['batch_number'],
                $formData['barcode'],
                $formData['quantity'],
                $formData['reorder_level'],
                $formData['unit_price'],
                $formData['selling_price'],
                $formData['expiry_date'],
                $formData['supplier'],
                $formData['category'],
                $formData['storage_conditions']
            ]);
            
            $pdo->commit();
            $success = true;
            // Reset form data after successful submission
            $formData = array_fill_keys(array_keys($formData), '');
            $formData['reorder_level'] = 10; // Reset to default
            $formData['storage_conditions'] = 'Room Temperature'; // Reset to default
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error registering drug: " . $e->getMessage();
        }
    }
}

$pageTitle = "Register Drug | Hanan Pharmacy";
include '../../includes/header.php';

// Get existing categories for dropdown
try {
    $categories = $pdo->query("SELECT DISTINCT category FROM drugs WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}
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
        /* Additional styles for drug registration */
        .drug-form {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .form-row {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
            border: none;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 1rem;
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
                    <span class="role">(Store Coordinator)</span>
                </div>
            </div>

            <nav class="dashboard-nav">
                <ul>
                    <li><a href="../dashboard/store_coordinator.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="register_drug.php"><i class="fas fa-plus-circle"></i> Register Drug</a></li>
                    <li><a href="view_drugs.php"><i class="fas fa-pills"></i> View Drugs</a></li>
                    <li><a href="check_expiry.php"><i class="fas fa-clock"></i> Check Expiry</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <div class="dashboard-header">
                <h1><i class="fas fa-pills"></i> Register New Drug</h1>
                <p class="welcome-message">Add new medications to the pharmacy inventory</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Drug registered successfully!
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="drug-form">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Drug Name *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="generic_name">Generic Name</label>
                            <input type="text" id="generic_name" name="generic_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['generic_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="batch_number">Batch Number *</label>
                            <input type="text" id="batch_number" name="batch_number" class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['batch_number']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="barcode">Barcode</label>
                            <input type="text" id="barcode" name="barcode" class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['barcode']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" 
                                  rows="3"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity">Quantity *</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" 
                                   min="1" value="<?php echo htmlspecialchars($formData['quantity']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="reorder_level">Reorder Level</label>
                            <input type="number" id="reorder_level" name="reorder_level" class="form-control" 
                                   min="0" value="<?php echo htmlspecialchars($formData['reorder_level']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="unit_price">Unit Price (ETB) *</label>
                            <input type="number" id="unit_price" name="unit_price" class="form-control" 
                                   min="0" step="0.01" value="<?php echo htmlspecialchars($formData['unit_price']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="selling_price">Selling Price (ETB)</label>
                            <input type="number" id="selling_price" name="selling_price" class="form-control" 
                                   min="0" step="0.01" value="<?php echo htmlspecialchars($formData['selling_price']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date *</label>
                            <input type="date" id="expiry_date" name="expiry_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['expiry_date']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"
                                        <?php echo $formData['category'] === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new_category">+ Add New Category</option>
                            </select>
                            <input type="text" id="new_category" name="new_category" class="form-control" 
                                   style="margin-top: 0.5rem; display: none;" placeholder="Enter new category">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier">Supplier</label>
                            <select id="supplier" name="supplier" class="form-control">
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier['name']); ?>"
                                        <?php echo $formData['supplier'] === $supplier['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="storage_conditions">Storage Conditions</label>
                            <select id="storage_conditions" name="storage_conditions" class="form-control">
                                <option value="Room Temperature" <?php echo $formData['storage_conditions'] === 'Room Temperature' ? 'selected' : ''; ?>>Room Temperature</option>
                                <option value="Refrigerated" <?php echo $formData['storage_conditions'] === 'Refrigerated' ? 'selected' : ''; ?>>Refrigerated</option>
                                <option value="Frozen" <?php echo $formData['storage_conditions'] === 'Frozen' ? 'selected' : ''; ?>>Frozen</option>
                                <option value="Other" <?php echo $formData['storage_conditions'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Register Drug
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script>
        // Set minimum date for expiry date (today)
        document.getElementById('expiry_date').min = new Date().toISOString().split('T')[0];
        
        // Handle category selection
        document.getElementById('category').addEventListener('change', function() {
            const newCategoryInput = document.getElementById('new_category');
            if (this.value === 'new_category') {
                newCategoryInput.style.display = 'block';
                newCategoryInput.setAttribute('name', 'category');
                this.setAttribute('name', '');
            } else {
                newCategoryInput.style.display = 'none';
                newCategoryInput.setAttribute('name', '');
                this.setAttribute('name', 'category');
            }
        });
        
        // Auto-calculate selling price if empty
        document.getElementById('unit_price').addEventListener('change', function() {
            const sellingPriceInput = document.getElementById('selling_price');
            if (!sellingPriceInput.value && this.value) {
                sellingPriceInput.value = (parseFloat(this.value) * 1.2).toFixed(2);
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const expiryDate = new Date(document.getElementById('expiry_date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (expiryDate < today) {
                e.preventDefault();
                alert('Expiry date cannot be in the past');
                document.getElementById('expiry_date').focus();
            }
            
            const unitPrice = parseFloat(document.getElementById('unit_price').value);
            const sellingPrice = parseFloat(document.getElementById('selling_price').value);
            
            if (sellingPrice < unitPrice) {
                e.preventDefault();
                alert('Selling price must be greater than unit price');
                document.getElementById('selling_price').focus();
            }
        });
    </script>
</body>
</html>