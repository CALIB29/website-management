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
                    <h2>Edit Website</h2>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> 
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="website-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Website Name <span class="required">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($website['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="url">Website URL <span class="required">*</span></label>
                                <input type="url" id="url" name="url" class="form-control"
                                       value="<?php echo htmlspecialchars($website['url']); ?>" required>
                                <small class="form-text">Include http:// or https://</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" 
                                      rows="4" placeholder="Enter a brief description of the website"><?php echo htmlspecialchars($website['description']); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
