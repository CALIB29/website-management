<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Unauthorized access';
    exit();
}

require_once 'database.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Invalid CSRF token';
    exit();
}

// Check if website IDs are provided
if (!isset($_POST['website_ids']) || !is_array($_POST['website_ids'])) {
    $_SESSION['error'] = 'No websites selected';
    header('Location: dashboard.php');
    exit();
}

$websiteIds = array_map('intval', $_POST['website_ids']);
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'delete':
            // Delete selected websites
            $placeholders = rtrim(str_repeat('?,', count($websiteIds)), ',');
            
            // First, delete related status entries
            $sql = "DELETE FROM website_status WHERE website_id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $types = str_repeat('i', count($websiteIds));
            $stmt->bind_param($types, ...$websiteIds);
            $stmt->execute();
            
            // Then delete the websites
            $sql = "DELETE FROM websites WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$websiteIds);
            $stmt->execute();
            
            $_SESSION['success'] = count($websiteIds) . ' website(s) deleted successfully';
            break;

        case 'check-status':
            // Trigger status check for selected websites
            // This would typically be handled by a background job or cron
            // For now, we'll just update the last_checked timestamp
            $currentTime = date('Y-m-d H:i:s');
            $placeholders = rtrim(str_repeat('?,', count($websiteIds)), ',');
            
            $sql = "UPDATE website_status SET last_checked = ? WHERE website_id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $types = 's' . str_repeat('i', count($websiteIds));
            $params = array_merge([$currentTime], $websiteIds);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            $_SESSION['success'] = 'Status check initiated for ' . count($websiteIds) . ' website(s)';
            break;

        case 'export':
            // Export website URLs
            $placeholders = rtrim(str_repeat('?,', count($websiteIds)), ',');
            $sql = "SELECT name, url, description FROM websites WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $types = str_repeat('i', count($websiteIds));
            $stmt->bind_param($types, ...$websiteIds);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $websites = [];
            while ($row = $result->fetch_assoc()) {
                $websites[] = $row;
            }
            
            // Output as CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="website_export_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fputs($output, "\xEF\xBB\xBF");
            
            // Add headers
            fputcsv($output, ['Name', 'URL', 'Description']);
            
            // Add data
            foreach ($websites as $website) {
                fputcsv($output, [
                    $website['name'],
                    $website['url'],
                    $website['description']
                ]);
            }
            
            fclose($output);
            exit();

        default:
            $_SESSION['error'] = 'Invalid action';
            header('Location: dashboard.php');
            exit();
    }

    header('Location: dashboard.php');
    exit();

} catch (Exception $e) {
    error_log('Bulk action error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while processing your request';
    header('Location: dashboard.php');
    exit();
}
