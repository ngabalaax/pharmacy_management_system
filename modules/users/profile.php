<?php
// modules/profile/profile.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotLoggedIn();

// Initialize variables
$errors = [];
$success = false;
$user = [];
$profile_pic = '';

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $profile_pic = $user['profile_pic'] ?: '../../assets/images/default-profile.png';
} catch (PDOException $e) {
    $errors[] = "Error fetching user data: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and update profile info
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Basic validation
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_pic']['type'];
        $file_size = $_FILES['profile_pic']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed";
        } elseif ($file_size > 2 * 1024 * 1024) { // 2MB max
            $errors[] = "File size must be less than 2MB";
        } else {
            $upload_dir = '../../uploads/profile_pics/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $new_filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
                // Delete old profile pic if it exists and isn't the default
                if ($user['profile_pic'] && $user['profile_pic'] !== 'default-profile.png') {
                    @unlink($upload_dir . $user['profile_pic']);
                }
                
                $profile_pic = '../../uploads/profile_pics/' . $new_filename;
            } else {
                $errors[] = "Failed to upload profile picture";
            }
        }
    }
    
    // Handle password change if provided
    if (!empty($current_password)) {
        if (empty($new_password)) $errors[] = "New password is required";
        elseif (strlen($new_password) < 8) $errors[] = "Password must be at least 8 characters";
        elseif ($new_password !== $confirm_password) $errors[] = "Passwords do not match";
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
    }
    
    // Update database if no errors
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Update basic profile info
            $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?";
            $params = [$first_name, $last_name, $email];
            
            // Add profile pic if uploaded
            if (isset($new_filename)) {
                $update_query .= ", profile_pic = ?";
                $params[] = $new_filename;
            }
            
            // Add password if changing
            if (!empty($current_password)) {
                $update_query .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }
            
            $update_query .= " WHERE user_id = ?";
            $params[] = $_SESSION['user_id'];
            
            $stmt = $pdo->prepare($update_query);
            $stmt->execute($params);
            
            $pdo->commit();
            
            // Update session variables
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            if (isset($new_filename)) {
                $_SESSION['profile_pic'] = $new_filename;
            }
            
            $success = true;
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error updating profile: " . $e->getMessage();
        }
    }
}

$pageTitle = "My Profile | Hanan Pharmacy";
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
        /* Profile-specific styles */
        .profile-container {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .profile-sidebar {
            width: 300px;
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            align-self: flex-start;
        }
        
        .profile-content {
            flex: 1;
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f5f7fa;
            display: block;
            margin: 0 auto 1rem;
        }
        
        .profile-upload {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .profile-upload label {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #3498db;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .profile-upload label:hover {
            background: #2980b9;
        }
        
        .profile-upload input[type="file"] {
            display: none;
        }
        
        .user-info-card {
            text-align: center;
        }
        
        .user-info-card h3 {
            margin: 0.5rem 0;
            color: #2c3e50;
        }
        
        .user-info-card p {
            color: #7f8c8d;
            margin: 0.25rem 0;
        }
        
        .user-role {
            display: inline-block;
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
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
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .btn-update {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-update:hover {
            background: #27ae60;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-sidebar {
                width: 100%;
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
                    <span class="role">(<?php echo ucfirst($_SESSION['role']); ?>)</span>
                </div>
            </div>

            <nav class="dashboard-nav">
                <ul>
                    <li><a href="../dashboard/<?php echo $_SESSION['role']; ?>.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="../profile/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <div class="dashboard-header">
                <h1><i class="fas fa-user-cog"></i> My Profile</h1>
                <p class="welcome-message">Manage your account information and settings</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Profile updated successfully!
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

            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-upload">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-picture" id="profile-preview">
                        <label for="profile_pic">
                            <i class="fas fa-camera"></i> Change Photo
                        </label>
                    </div>
                    
                    <div class="user-info-card">
                        <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <span class="user-role"><?php echo ucfirst($user['role']); ?></span>
                        
                        <div class="user-stats" style="margin-top: 1.5rem;">
                            <p><i class="fas fa-calendar-alt"></i> Member since: <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                            <?php if ($user['last_login']): ?>
                                <p><i class="fas fa-clock"></i> Last login: <?php echo date('M j, Y g:i A', strtotime($user['last_login'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="profile-content">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" name="profile_pic" id="profile_pic" accept="image/*" style="display: none;">
                        
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <h3 style="margin: 2rem 0 1rem; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">
                            <i class="fas fa-lock"></i> Change Password
                        </h3>
                        <p style="color: #7f8c8d; margin-bottom: 1.5rem;">Leave blank to keep current password</p>
                        
                        <div class="form-group password-toggle">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control">
                            <i class="fas fa-eye" onclick="togglePassword('current_password')"></i>
                        </div>
                        
                        <div class="form-group password-toggle">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control">
                            <i class="fas fa-eye" onclick="togglePassword('new_password')"></i>
                        </div>
                        
                        <div class="form-group password-toggle">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                            <i class="fas fa-eye" onclick="togglePassword('confirm_password')"></i>
                        </div>
                        
                        <button type="submit" class="btn-update">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script>
        // Profile picture preview
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Password toggle visibility
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>