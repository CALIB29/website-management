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
