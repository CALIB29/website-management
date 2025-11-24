<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SRC Website Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="app-header">
                <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h2 class="header-title">Dashboard</h2>
            </div>

            <div class="content">
                <a href="add_website.php" class="btn" style="margin-bottom: 30px;"><i class="fas fa-plus"></i> Add New Website</a>
                <div class="website-grid">
                    <?php
                    $sql = "SELECT * FROM websites ORDER BY name ASC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            // --- Automated Thumbnail Generation ---
                            $website_url = htmlspecialchars($row['url']);
                            $thumbnail_service_url = "https://s0.wordpress.com/mshots/v1/" . urlencode($website_url) . "?w=400";
                            // -------------------------------------

                            echo "<div class='website-card'>";
                            echo "<div class='website-card-thumbnail'><img src='" . $thumbnail_service_url . "' alt='" . htmlspecialchars($row['name']) . " Thumbnail'></div>";
                            echo "<h4>" . htmlspecialchars($row['name']) . "</h4>";
                            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                            echo "<div class='card-actions'>";
                            echo "<a href='" . htmlspecialchars($row['url']) . "' target='_blank' class='visit-link'><i class='fas fa-external-link-alt'></i> Visit Site</a>";
                            echo "<div class='action-buttons'>";
                            echo "<a href='edit_website.php?id=" . $row['id'] . "' class='btn-edit'><i class='fas fa-edit'></i></a>";
                            echo "<a href='delete_website.php?id=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this website?\");'><i class='fas fa-trash'></i></a>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No websites found. Click 'Add New Website' to get started.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
