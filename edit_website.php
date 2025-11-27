<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'database.php';

$website_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $url = $_POST['url'];
    $description = $_POST['description'];

    $sql = "UPDATE websites SET name = ?, url = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $url, $description, $website_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
} else {
    $sql = "SELECT * FROM websites WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $website_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $website = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Website - SRC Website Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="app-header">
                <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h2 class="header-title">Edit Website</h2>
            </div>

            <div class="content">
                <div class="form-container">
                    <form method="POST" action="">
                        <label for="name">Website Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($website['name']); ?>" required>

                        <label for="url">URL</label>
                        <input type="text" id="url" name="url" value="<?php echo htmlspecialchars($website['url']); ?>" required>

                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($website['description']); ?></textarea>

                        <button type="submit" class="btn"><i class="fas fa-save"></i> Update Website</button>
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
