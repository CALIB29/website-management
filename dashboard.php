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
    <link rel="stylesheet" type="text/css" href="css/bulk-actions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php $pageTitle = 'Dashboard'; ?>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <?php include 'header.php'; ?>

            <div class="content">
                <?php
                // Generate CSRF token if not exists
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                
                // Display success/error messages
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                
                <div class="dashboard-actions">
                    <a href="add_website.php" class="btn"><i class="fas fa-plus"></i> Add New Website</a>
                    
                    <div class="bulk-actions" style="display: none;">
                        <select id="bulk-action" class="form-control">
                            <option value="">Bulk Actions</option>
                            <option value="delete">Delete Selected</option>
                            <option value="check-status">Check Status</option>
                            <option value="export">Export URLs</option>
                        </select>
                        <button type="button" id="apply-bulk-action" class="btn btn-secondary">Apply</button>
                        <button type="button" id="cancel-bulk-actions" class="btn btn-text">Cancel</button>
                        <span id="selected-count">0 selected</span>
                    </div>
                </div>
                
                <form id="websites-form" method="post" action="bulk_actions.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="select-all-container" style="display: none;">
                        <input type="checkbox" id="select-all-checkbox">
                        <label for="select-all-checkbox">Select All</label>
                    </div>
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
                            
                            // Get website status
                            $statusSql = "SELECT status, last_checked, response_time 
                                        FROM website_status 
                                        WHERE website_id = ? 
                                        ORDER BY last_checked DESC 
                                        LIMIT 1";
                            $statusStmt = $conn->prepare($statusSql);
                            $statusStmt->bind_param("i", $row['id']);
                            $statusStmt->execute();
                            $statusResult = $statusStmt->get_result();
                            $status = $statusResult->fetch_assoc();
                            
                            $statusClass = $status['status'] ?? 'unknown';
                            $statusText = ucfirst($status['status'] ?? 'Unknown');
                            $responseTime = isset($status['response_time']) ? "{$status['response_time']}ms" : "N/A";

                            echo "<div class='website-card'>";
                            echo "<div class='website-checkbox'>";
                            echo "<input type='checkbox' name='website_ids[]' value='" . $row['id'] . "' class='website-checkbox-input' id='website-" . $row['id'] . "'>";
                            echo "<label for='website-" . $row['id'] . "' class='checkmark'></label>";
                            echo "</div>";
                            echo "<div class='website-status status-{$statusClass}' title='Status: {$statusText}, Response: {$responseTime}'></div>";
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
                </form>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
    <script src="js/bulk-actions.js"></script>
</body>
</html>
