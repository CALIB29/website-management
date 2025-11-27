<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'database.php';

$admin_id = $_SESSION['admin_id'];
// Fetch admin info
$stmt = $conn->prepare("SELECT id, username FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$success = '';
$error = '';

// Update profile (username)
if (isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    if (empty($new_username)) {
        $error = "Username cannot be empty.";
    } else {
        $u_stmt = $conn->prepare("UPDATE admins SET username = ? WHERE id = ?");
        $u_stmt->bind_param("si", $new_username, $admin_id);
        if ($u_stmt->execute()) {
            $_SESSION['username'] = $new_username;
            $success = "Profile updated successfully.";
            $admin['username'] = $new_username;
        } else {
            $error = "Error updating profile: " . $u_stmt->error;
        }
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (empty($current) || empty($new) || empty($confirm)) {
        $error = "All password fields are required.";
    } elseif ($new !== $confirm) {
        $error = "New password and confirmation do not match.";
    } else {
        // Verify current
        $p_stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
        $p_stmt->bind_param("i", $admin_id);
        $p_stmt->execute();
        $p_res = $p_stmt->get_result()->fetch_assoc();
        $hashed = $p_res['password'];

        if (!password_verify($current, $hashed)) {
            $error = "Current password is incorrect.";
        } else {
            $new_hashed = password_hash($new, PASSWORD_DEFAULT);
            $c_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $c_stmt->bind_param("si", $new_hashed, $admin_id);
            if ($c_stmt->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "Error updating password: " . $c_stmt->error;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SRC Website Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="app-header">
                <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h2 class="header-title">Settings</h2>
            </div>

            <div class="content">
                <div class="form-container">
                    <?php if ($success) { echo "<p class='success'>" . htmlspecialchars($success) . "</p>"; } ?>
                    <?php if ($error) { echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; } ?>

                    <h3>Profile</h3>
                    <form method="POST" action="">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                        <button type="submit" name="update_profile" class="btn"><i class="fas fa-save"></i> Update Profile</button>
                    </form>

                    <hr style="margin:20px 0;">

                    <h3>Change Password</h3>
                    <form method="POST" action="">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>

                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>

                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>

                        <button type="submit" name="change_password" class="btn"><i class="fas fa-key"></i> Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
    <script src="script.js"></script>
</body>
</html>
