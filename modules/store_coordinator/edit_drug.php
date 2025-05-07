<?php
// modules/inventory/edit_drug.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

redirectIfNotLoggedIn();
if (!isStoreCoordinator()) {
    header("Location: ../../index.php");
    exit();
}

// Check if drug ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid drug ID";
    header("Location: view_drugs.php");
    exit();
}

$drugId = $_GET['id'];

// Fetch drug details
try {
    $stmt = $pdo->prepare("SELECT * FROM drugs WHERE drug_id = :drug_id");
    $stmt->execute([':drug_id' => $drugId]);
    $drug = $stmt->fetch();

    if (!$drug) {
        $_SESSION['error_message'] = "Drug not found";
        header("Location: view_drugs.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error fetching drug details: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $genericName = $_POST['generic_name'];
    $batchNumber = $_POST['batch_number'];
    $category = $_POST['category'];
    $quantity = (int) $_POST['quantity'];
    $reorderLevel = (int) $_POST['reorder_level'];
    $unitPrice = (float) $_POST['unit_price'];
    $sellingPrice = (float) $_POST['selling_price'];
    $expiryDate = $_POST['expiry_date'];
    $manufacturer = $_POST['manufacturer'];
    $supplier = $_POST['supplier'];
    $storageConditions = $_POST['storage_conditions'];
    $description = $_POST['description'];

    try {
        $stmt = $pdo->prepare("UPDATE drugs SET 
                            name = :name,
                            generic_name = :generic_name,
                            batch_number = :batch_number,
                            category = :category,
                            quantity = :quantity,
                            reorder_level = :reorder_level,
                            unit_price = :unit_price,
                            selling_price = :selling_price,
                            expiry_date = :expiry_date,
                            manufacturer = :manufacturer,
                            supplier = :supplier,
                            storage_conditions = :storage_conditions,
                            description = :description,
                            updated_at = CURRENT_TIMESTAMP
                            WHERE drug_id = :drug_id");

        $stmt->execute([
            ':name' => $name,
            ':generic_name' => $genericName,
            ':batch_number' => $batchNumber,
            ':category' => $category,
            ':quantity' => $quantity,
            ':reorder_level' => $reorderLevel,
            ':unit_price' => $unitPrice,
            ':selling_price' => $sellingPrice,
            ':expiry_date' => $expiryDate,
            ':manufacturer' => $manufacturer,
            ':supplier' => $supplier,
            ':storage_conditions' => $storageConditions,
            ':description' => $description,
            ':drug_id' => $drugId
        ]);

        // Log the update
        $logStmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, table_name, record_id) 
                                VALUES (:user_id, 'update', 'drugs', :record_id)");
        $logStmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':record_id' => $drugId
        ]);

        $_SESSION['success_message'] = "Drug updated successfully";
        header("Location: view_drugs.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error updating drug: " . $e->getMessage();
    }
}

$pageTitle = "Edit Drug | Store Coordinator | Hanan Pharmacy";
include '../../includes/header.php';
;
?>
<link rel="stylesheet" href="../../assets/css/edit_drug.css">
<link rel="stylesheet" href="../../assets/css/dashboard.css">
<link rel="stylesheet" href="../../assets/css/cashier.css">


<div class="dashboard-container">
    <?php include 'store_coordinator_sidebar.php'; ?>


    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>
    <div class="content">
        <form method="post" class="drug-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Drug Name*</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($drug['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="generic_name">Generic Name</label>
                    <input type="text" id="generic_name" name="generic_name"
                        value="<?= htmlspecialchars($drug['generic_name']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="batch_number">Batch Number*</label>
                    <input type="text" id="batch_number" name="batch_number"
                        value="<?= htmlspecialchars($drug['batch_number']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Category*</label>
                    <input type="text" id="category" name="category" value="<?= htmlspecialchars($drug['category']) ?>"
                        required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quantity">Quantity*</label>
                    <input type="number" id="quantity" name="quantity" min="0" value="<?= $drug['quantity'] ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="reorder_level">Reorder Level*</label>
                    <input type="number" id="reorder_level" name="reorder_level" min="0"
                        value="<?= $drug['reorder_level'] ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="unit_price">Unit Price (ETB)*</label>
                    <input type="number" id="unit_price" name="unit_price" min="0" step="0.01"
                        value="<?= $drug['unit_price'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="selling_price">Selling Price (ETB)*</label>
                    <input type="number" id="selling_price" name="selling_price" min="<?= $drug['unit_price'] ?>"
                        step="0.01" value="<?= $drug['selling_price'] ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="expiry_date">Expiry Date*</label>
                    <input type="date" id="expiry_date" name="expiry_date" value="<?= $drug['expiry_date'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="manufacturer">Manufacturer</label>
                    <input type="text" id="manufacturer" name="manufacturer"
                        value="<?= htmlspecialchars($drug['manufacturer']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="supplier">Supplier</label>
                    <input type="text" id="supplier" name="supplier" value="<?= htmlspecialchars($drug['supplier']) ?>">
                </div>
                <div class="form-group">
                    <label for="storage_conditions">Storage Conditions</label>
                    <input type="text" id="storage_conditions" name="storage_conditions"
                        value="<?= htmlspecialchars($drug['storage_conditions']) ?>">
                </div>
            </div>

            <div class="form-group full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                    rows="3"><?= htmlspecialchars($drug['description']) ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="view_drugs.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>

</div>

<script>
    // Ensure selling price is never less than unit price
    document.getElementById('unit_price').addEventListener('change', function () {
        const sellingPrice = document.getElementById('selling_price');
        if (parseFloat(sellingPrice.value) < parseFloat(this.value)) {
            sellingPrice.value = this.value;
        }
        sellingPrice.min = this.value;
    });
</script>

<?php include '../../includes/footer.php'; ?>