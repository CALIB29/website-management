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
                <!-- Dashboard Analytics & Trends removed as requested -->
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-bottom:18px;">
                  <a href="add_website.php" class="btn" style="margin-bottom: 0;"><i class="fas fa-plus"></i> Add New Website</a>
                  <div style="margin-left:auto;">
                    <!-- Customize Widgets button removed -->
                  </div>
                </div>
                <div id="widget-customizer-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.18);z-index:3000;align-items:center;justify-content:center;">
                  <!-- Customizable widget modal removed -->
                </div>
                <div class="website-grid">
                    <?php


                    // --- Website Cards (also count uptime for pie chart) ---
                    $sql = "SELECT * FROM websites ORDER BY name ASC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            // --- Automated Thumbnail Generation ---
                            $website_url = htmlspecialchars($row['url']);
                            $thumbnail_service_url = "https://s0.wordpress.com/mshots/v1/" . urlencode($website_url) . "?w=400";
                            // -------------------------------------

                            // --- Uptime Monitoring removed ---
                            $uptime_status = 'unknown';
                            $uptime_icon = 'fa-question-circle';
                            $uptime_color = '#aaa';

                            // --- Performance Optimization Suggestions ---
                            $perf_suggestions = [];
                            $headers = @get_headers($row['url'], 1);
                            if ($headers !== false) {
                                $encoding = isset($headers['Content-Encoding']) ? $headers['Content-Encoding'] : '';
                                if (stripos($encoding, 'gzip') === false && stripos($encoding, 'deflate') === false) {
                                    $perf_suggestions[] = 'Enable GZIP or Brotli compression for faster load times.';
                                }
                                $scheme = parse_url($row['url'], PHP_URL_SCHEME);
                                $host = parse_url($row['url'], PHP_URL_HOST);
                                $http2 = false;
                                if ($scheme === 'https') {
                                    $stream = @stream_socket_client("ssl://$host:443", $errno, $errstr, 2, STREAM_CLIENT_CONNECT, stream_context_create(["ssl"=>["capture_peer_cert_chain"=>true]]));
                                    if ($stream) {
                                        $meta = stream_get_meta_data($stream);
                                        if (isset($meta['crypto'])) {
                                            $crypto = $meta['crypto'];
                                            if (is_array($crypto)) {
                                                $crypto = implode(' ', $crypto);
                                            }
                                            if (is_string($crypto) && stripos($crypto, 'HTTP/2') !== false) {
                                                $http2 = true;
                                            }
                                        }
                                        fclose($stream);
                                    }
                                }
                                if (!$http2) {
                                    $perf_suggestions[] = 'Consider enabling HTTP/2 for improved performance.';
                                }
                                $cache = isset($headers['Cache-Control']) ? $headers['Cache-Control'] : '';
                                if (stripos($cache, 'max-age') === false && stripos($cache, 'public') === false) {
                                    $perf_suggestions[] = 'Set proper Cache-Control headers for static assets.';
                                }
                                if (isset($headers['Content-Type'])) {
                                    $contentType = $headers['Content-Type'];
                                    if (is_array($contentType)) {
                                        $contentType = implode(' ', $contentType);
                                    }
                                    if (is_string($contentType) && stripos($contentType, 'image/') !== false) {
                                        if (stripos($contentType, 'webp') === false) {
                                            $perf_suggestions[] = 'Use next-gen image formats like WebP for better performance.';
                                        }
                                    }
                                }
                            } else {
                                $perf_suggestions[] = 'Unable to fetch headers for performance suggestions.';
                            }

                            echo "<div class='website-card'>";
                            echo "<div class='website-card-thumbnail'><img src='" . $thumbnail_service_url . "' alt='" . htmlspecialchars($row['name']) . " Thumbnail'></div>";
                            echo "<div style='display:flex;align-items:center;gap:8px;margin-bottom:4px;'>";
                            echo "<h4 style='margin:0;flex:1;'>" . htmlspecialchars($row['name']) . "</h4>";
                            echo "</div>";
                            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                            echo "<div class='card-actions'>";
                            // Widget: Visit Site
                            echo "<a class='widget-visit-site' href='" . htmlspecialchars($row['url']) . "' target='_blank' class='visit-link'><i class='fas fa-external-link-alt'></i> Visit Site</a>";
                            echo "<div class='action-buttons'>";
                            echo "<div class='action-button-item'><a href='edit_website.php?id=" . $row['id'] . "' class='btn-edit'><i class='fas fa-edit'></i></a></div>";
                            echo "<div class='action-button-item'><a href='analysis_report.php?id=" . $row['id'] . "' class='btn-analyze'><i class='fas fa-shield-alt'></i></a></div>";
                            echo "<div class='action-button-item'><a href='delete_website.php?id=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this website?\");'><i class='fas fa-trash'></i></a></div>";
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
    <?php include 'bottom-nav.php'; ?>

    <script src="script.js"></script>
    <!-- Customizable widget logic removed -->
</body>
</html>
