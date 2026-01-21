<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $url = $_POST['url'];
    $description = $_POST['description'];

    $sql = "INSERT INTO websites (name, url, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $url, $description);

    if ($stmt->execute()) {
        // Activity log
        $admin_id = $_SESSION['admin_id'];
        $action = 'add_website';
        $details = 'Added website: ' . $name . ' (' . $url . ')';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $conn->query("CREATE TABLE IF NOT EXISTS activity_log (id INT AUTO_INCREMENT PRIMARY KEY, admin_id INT NOT NULL, action VARCHAR(64) NOT NULL, details TEXT, ip_address VARCHAR(45), timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX (admin_id), INDEX (timestamp)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $stmt2 = $conn->prepare("INSERT INTO activity_log (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $admin_id, $action, $details, $ip);
        $stmt2->execute();
        $stmt2->close();
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Website - SRC Website Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="app-header">
                <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h2 class="header-title">Add New Website</h2>
            </div>

            <div class="content">
                <div class="form-container">
                    <form method="POST" action="">
                        <label for="name">Website Name</label>
                        <input type="text" id="name" name="name" placeholder="e.g., SRC Student Portal" required>

                        <label for="url">URL</label>
                        <input type="text" id="url" name="url" placeholder="https://portal.src.edu.ph" required>

                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="A brief description of the website's purpose."></textarea>

                        <button type="submit" class="btn"><i class="fas fa-save"></i> Save Website</button>
                        <a href="dashboard.php" class="btn" style="background-color: var(--secondary-color); margin-left: 10px;">Cancel</a>
                    </form>
                    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
                </div>
                </div>
                </div>
                </div>
                <?php include 'bottom-nav.php'; ?>
                <script src="script.js"></script>
</body>
</html>
