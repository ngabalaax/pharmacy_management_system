<?php
// login.php

require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $selectedRole = trim($_POST['role']);

    if (empty($username) || empty($password) || empty($selectedRole)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($selectedRole !== $user['role']) {
                    $error = "Incorrect role selected.";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];

                    // Redirect based on role
                    switch ($user['role']) {
                        case 'manager':
                            header("Location: /modules/manager/manager.php");
                            break;
                        case 'pharmacist':
                            header("Location: /modules/pharmacist/pharmacist.php");
                            break;
                        case 'store_coordinator':
                            header("Location: /modules/store_coordinator/store_coordinator.php");
                            break;
                        case 'cashier':
                            header("Location: /modules/cashier/cashier.php");
                            break;
                        default:
                            header("Location: index.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hanan Pharmacy - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Hanan Pharmacy</h1>
            <h2>Login</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="manager">Manager</option>
                        <option value="pharmacist">Pharmacist</option>
                        <option value="store_coordinator">Store Coordinator</option>
                        <option value="cashier">Cashier</option>
                    </select>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
