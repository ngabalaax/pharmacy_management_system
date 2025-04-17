<?php
// modules/sales/process_sale.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotLoggedIn();
if (!isPharmacist() && !isCashier()) {
    header("Location: ../../index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        // Add item to cart
        $drugId = (int) $_POST['drug_id'];
        $quantity = (int) $_POST['quantity'];

        try {
            // Check if drug exists and has sufficient quantity
            $stmt = $pdo->prepare("SELECT * FROM drugs WHERE drug_id = ? AND quantity >= ?");
            $stmt->execute([$drugId, $quantity]);
            $drug = $stmt->fetch();

            if ($drug) {
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                // Check if item already in cart
                $itemIndex = array_search($drugId, array_column($_SESSION['cart'], 'drug_id'));

                if ($itemIndex !== false) {
                    $_SESSION['cart'][$itemIndex]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][] = [
                        'drug_id' => $drugId,
                        'name' => $drug['name'],
                        'quantity' => $quantity,
                        'unit_price' => $drug['unit_price']
                    ];
                }

                $success = "Item added to cart!";
            } else {
                $error = "Drug not found or insufficient quantity!";
            }
        } catch (PDOException $e) {
            $error = "Error adding item to cart: " . $e->getMessage();
        }
    } elseif (isset($_POST['remove_from_cart'])) {
        // Remove item from cart
        $index = (int) $_POST['item_index'];

        if (isset($_SESSION['cart'][$index])) {
            array_splice($_SESSION['cart'], $index, 1);
            $success = "Item removed from cart!";
        }
    } elseif (isset($_POST['complete_sale'])) {
        // Complete the sale
        if (!empty($_SESSION['cart'])) {
            try {
                $pdo->beginTransaction();

                // Create sale record
                $totalAmount = array_sum(array_map(function ($item) {
                    return $item['quantity'] * $item['unit_price'];
                }, $_SESSION['cart']));

                $stmt = $pdo->prepare("INSERT INTO sales (user_id, total_amount) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $totalAmount]);
                $saleId = $pdo->lastInsertId();

                // Create sale items and update inventory
                foreach ($_SESSION['cart'] as $item) {
                    // Add sale item
                    $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, drug_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$saleId, $item['drug_id'], $item['quantity'], $item['unit_price']]);

                    // Update drug quantity
                    $stmt = $pdo->prepare("UPDATE drugs SET quantity = quantity - ? WHERE drug_id = ?");
                    $stmt->execute([$item['quantity'], $item['drug_id']]);
                }

                $pdo->commit();
                $success = "Sale completed successfully! Sale ID: " . $saleId;
                unset($_SESSION['cart']);
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Error completing sale: " . $e->getMessage();
            }
        } else {
            $error = "Cart is empty!";
        }
    }
}

// Get all drugs for selection
try {
    $stmt = $pdo->query("SELECT * FROM drugs WHERE quantity > 0 ORDER BY name");
    $drugs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching drugs: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Sale | Hanan Pharmacy</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a
                            href="../dashboard/<?php echo isPharmacist() ? 'pharmacist' : 'cashier'; ?>.php">Dashboard</a>
                    </li>
                    <li><a href="process_sale.php">Process Sale</a></li>
                    <li class="active"><a href="sales_history.php">Sales History</a></li>
                    <?php if (isPharmacist()): ?>
                        <li><a href="../inventory/view_drugs.php">View Drugs</a></li>
                    <?php endif; ?>
                    <li><a href="../../logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <h1>Process Sale</h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="sale-process">
                <div class="drug-selection">
                    <h2>Add Items to Sale</h2>
                    <form action="process_sale.php" method="post">
                        <div class="form-group">
                            <label for="drug_id">Select Drug</label>
                            <select id="drug_id" name="drug_id" required>
                                <option value="">-- Select Drug --</option>
                                <?php foreach ($drugs as $drug): ?>
                                    <option value="<?php echo $drug['drug_id']; ?>">
                                        <?php echo htmlspecialchars($drug['name']); ?>
                                        (Available: <?php echo $drug['quantity']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" min="1" required>
                        </div>

                        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                    </form>
                </div>

                <div class="cart-summary">
                    <h2>Current Sale</h2>

                    <?php if (!empty($_SESSION['cart'])): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Drug Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $grandTotal = 0;
                                foreach ($_SESSION['cart'] as $index => $item):
                                    $itemTotal = $item['quantity'] * $item['unit_price'];
                                    $grandTotal += $itemTotal;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td><?php echo number_format($itemTotal, 2); ?></td>
                                        <td>
                                            <form action="process_sale.php" method="post">
                                                <input type="hidden" name="item_index" value="<?php echo $index; ?>">
                                                <button type="submit" name="remove_from_cart"
                                                    class="btn btn-danger">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Grand Total</th>
                                    <th colspan="2"><?php echo number_format($grandTotal, 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>

                        <form action="process_sale.php" method="post">
                            <button type="submit" name="complete_sale" class="btn btn-success">Complete Sale</button>
                        </form>
                    <?php else: ?>
                        <p>Your cart is empty. Add items to begin a sale.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script src="../../assets/js/script.js"></script>
</body>

</html>