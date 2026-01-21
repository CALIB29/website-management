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
    <title>Activity Log - SRC Website Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="app-header">
                <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h2 class="header-title">Activity Log</h2>
            </div>
            <div class="content">
                <h3 style="display:flex;align-items:center;gap:10px;font-size:1.3em;margin-bottom:18px;">
                  <i class="fas fa-history" style="color:#1a3a6b;"></i> Recent Admin Activities
                </h3>
                <div class="activity-log-table-wrapper" style="overflow-x:auto;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(44,62,80,0.07);padding:0;">
                <table class="activity-log-table" style="width:100%;border-collapse:separate;border-spacing:0;">
                    <thead style="background:#f5f7fa;">
                        <tr style="border-bottom:2px solid #e0e0e0;">
                            <th style="padding:12px 10px;text-align:left;font-weight:600;color:#1a3a6b;"><i class="far fa-clock"></i> Date/Time</th>
                            <th style="padding:12px 10px;text-align:left;font-weight:600;color:#1a3a6b;"><i class="fas fa-user"></i> Admin</th>
                            <th style="padding:12px 10px;text-align:left;font-weight:600;color:#1a3a6b;"><i class="fas fa-tasks"></i> Action</th>
                            <th style="padding:12px 10px;text-align:left;font-weight:600;color:#1a3a6b;"><i class="fas fa-info-circle"></i> Details</th>
                            <th style="padding:12px 10px;text-align:left;font-weight:600;color:#1a3a6b;"><i class="fas fa-network-wired"></i> IP Address</th>
                        </tr>
                    </thead>
                    <tbody style="font-size:0.98em;">
                    <?php
                    $sql = "SELECT l.*, a.username FROM activity_log l LEFT JOIN admins a ON l.admin_id = a.id ORDER BY l.timestamp DESC LIMIT 100";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr style='border-bottom:1px solid #f0f0f0;'>";
                            echo "<td style='padding:10px 8px;color:#555;'>" .
                                "<i class='far fa-clock' style='color:#8e9aaf;margin-right:4px;'></i>" .
                                htmlspecialchars(date('M d, Y H:i', strtotime($row['timestamp']))) . "</td>";
                            echo "<td style='padding:10px 8px;color:#1a3a6b;font-weight:500;'><i class='fas fa-user-circle' style='color:#8e9aaf;margin-right:4px;'></i>" . htmlspecialchars($row['username'] ?? 'Unknown') . "</td>";
                            // Action icon
                            $actionIcon = 'fa-question-circle';
                            $actionColor = '#8e9aaf';
                            if ($row['action'] === 'add_website') { $actionIcon = 'fa-plus-circle'; $actionColor = '#27ae60'; }
                            elseif ($row['action'] === 'edit_website') { $actionIcon = 'fa-edit'; $actionColor = '#f39c12'; }
                            elseif ($row['action'] === 'delete_website') { $actionIcon = 'fa-trash'; $actionColor = '#e74c3c'; }
                            elseif ($row['action'] === 'view_analysis_report') { $actionIcon = 'fa-shield-alt'; $actionColor = '#1a3a6b'; }
                            echo "<td style='padding:10px 8px;'><i class='fas $actionIcon' style='color:$actionColor;margin-right:4px;'></i>" . htmlspecialchars($row['action']) . "</td>";
                            // Details with link for analysis report
                            echo "<td style='padding:10px 8px;color:#444;'>" . htmlspecialchars($row['details']);
                            if ($row['action'] === 'view_analysis_report') {
                                if (preg_match('/website ID: (\d+)/', $row['details'], $matches)) {
                                    $website_id = $matches[1];
                                    echo "<br><a href='analysis_report.php?id=" . urlencode($website_id) . "' class='btn-analyze' target='_blank' style='display:inline-block;margin-top:4px;background:#1a3a6b;color:#fff;padding:4px 10px;border-radius:6px;font-size:0.95em;text-decoration:none;'><i class='fas fa-shield-alt'></i> View Report</a>";
                                }
                            }
                            echo "</td>";
                            echo "<td style='padding:10px 8px;color:#888;'><i class='fas fa-network-wired' style='color:#8e9aaf;margin-right:4px;'></i>" . htmlspecialchars($row['ip_address']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;padding:30px 0;color:#aaa;'><i class='fas fa-info-circle'></i> No activity found.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
