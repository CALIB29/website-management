<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'database.php';

if (isset($_GET['id'])) {
    $website_id = $_GET['id'];

    $sql = "DELETE FROM websites WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $website_id);

    if ($stmt->execute()) {
        // Activity log
        $admin_id = $_SESSION['admin_id'];
        $action = 'delete_website';
        $details = 'Deleted website ID: ' . $website_id;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $conn->query("CREATE TABLE IF NOT EXISTS activity_log (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NOT NULL, action VARCHAR(64) NOT NULL, details TEXT, ip_address VARCHAR(45), timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX (admin_id), INDEX (timestamp)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $stmt2 = $conn->prepare("INSERT INTO activity_log (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $admin_id, $action, $details, $ip);
        $stmt2->execute();
        $stmt2->close();
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
