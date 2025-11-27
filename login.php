<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // --- Rate limiting / throttling ---
    // Ensure table exists (lightweight, safe to run)
    $createTableSql = "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        username VARCHAR(150) DEFAULT NULL,
        attempt_time DATETIME NOT NULL,
        success TINYINT(1) NOT NULL DEFAULT 0,
        INDEX (username),
        INDEX (ip_address),
        INDEX (attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($createTableSql);

    // ensure login_audit table exists
    $createAuditSql = "CREATE TABLE IF NOT EXISTS login_audit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        session_id VARCHAR(128) NOT NULL,
        user_agent TEXT,
        device_type VARCHAR(32),
        ip_address VARCHAR(45),
        login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        logout_time DATETIME DEFAULT NULL,
        INDEX (admin_id),
        INDEX (session_id),
        INDEX (login_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($createAuditSql);

    $throttleWindow = 15 * 60; // 15 minutes in seconds
    $maxAttemptsUser = 5; // per-account attempts

    // Count failed attempts for this username in the window (per-account throttling only)
    $stmtUserAttempts = $conn->prepare("SELECT COUNT(*) AS fail_count FROM login_attempts WHERE username = ? AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmtUserAttempts->bind_param("s", $username);
    $stmtUserAttempts->execute();
    $resUserAttempts = $stmtUserAttempts->get_result()->fetch_assoc();
    $userFailCount = (int)$resUserAttempts['fail_count'];
    $stmtUserAttempts->close();

    if ($userFailCount >= $maxAttemptsUser) {
        $error = "Too many failed login attempts for this account. Please wait 15 minutes and try again.";
    } else {
        // proceed with authentication below
        $sql = "SELECT * FROM admins WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                // successful login: record success and clear previous failed attempts for this username
                $ins = $conn->prepare("INSERT INTO login_attempts (ip_address, username, attempt_time, success) VALUES (?, ?, NOW(), 1)");
                $ins->bind_param("ss", $ip, $username);
                $ins->execute();
                $ins->close();

                // Remove older failed attempts for this username (across IPs) for cleanliness
                $del = $conn->prepare("DELETE FROM login_attempts WHERE username = ? AND success = 0");
                $del->bind_param("s", $username);
                $del->execute();
                $del->close();

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];

                // --- Insert login audit row and store its id in session ---
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $deviceType = 'desktop';
                if (preg_match('/Tablet|iPad/i', $userAgent)) {
                    $deviceType = 'tablet';
                } elseif (preg_match('/Mobile|Android|iPhone|iPod|Windows Phone/i', $userAgent)) {
                    $deviceType = 'mobile';
                }
                $sessionId = session_id();
                if ($stmtAudit = $conn->prepare("INSERT INTO login_audit (admin_id, session_id, user_agent, device_type, ip_address) VALUES (?, ?, ?, ?, ?)")) {
                    $stmtAudit->bind_param("issss", $admin['id'], $sessionId, $userAgent, $deviceType, $ip);
                    $stmtAudit->execute();
                    $_SESSION['login_audit_id'] = $stmtAudit->insert_id;
                    $stmtAudit->close();
                }

                header("Location: dashboard.php");
                exit();
            } else {
                // record failed attempt
                $ins = $conn->prepare("INSERT INTO login_attempts (ip_address, username, attempt_time, success) VALUES (?, ?, NOW(), 0)");
                $ins->bind_param("ss", $ip, $username);
                $ins->execute();
                $ins->close();

                $error = "Invalid password.";
            }
        } else {
            // record failed attempt even if username not found
            $ins = $conn->prepare("INSERT INTO login_attempts (ip_address, username, attempt_time, success) VALUES (?, ?, NOW(), 0)");
            $ins->bind_param("ss", $ip, $username);
            $ins->execute();
            $ins->close();

            $error = "No user found with that username.";
        }

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SRC Website Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="login-page-body">
    <div class="login-container">
        <div class="login-box">
            <img src="images/src_logo.png" alt="SRC Logo" class="login-logo">
            <h2>SRC Admin Panel</h2>
            <p>Please log in to continue</p>
            <form method="POST" action="">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit">Login</button>
                <?php if (isset($error)) { echo "<p class='error'>".htmlspecialchars($error)."</p>"; } ?>
            </form>
        </div>
    </div>
</body>
</html>
