<?php
require_once 'database.php';

// Function to check if a website is up
function checkWebsiteStatus($url) {
    // Ensure the URL has a protocol
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    
    $start = microtime(true);
    
    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $totalTime = round((microtime(true) - $start) * 1000); // in ms
    
    $error = '';
    if (curl_errno($ch)) {
        $error = curl_error($ch);
    }
    
    curl_close($ch);
    
    $status = ($httpCode >= 200 && $httpCode < 400) ? 'up' : 'down';
    
    return [
        'status' => $status,
        'response_time' => $totalTime,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Get all websites
$sql = "SELECT id, url FROM websites";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = checkWebsiteStatus($row['url']);
        
        // Prepare error message if any
        $errorMessage = '';
        if (!empty($status['error'])) {
            $errorMessage = $status['error'];
        } elseif ($status['http_code'] >= 400) {
            $errorMessage = "HTTP {$status['http_code']}";
        }
        
        // Update status in database
        $stmt = $conn->prepare("
            INSERT INTO website_status 
            (website_id, status, last_checked, response_time, last_error) 
            VALUES (?, ?, NOW(), ?, ?) 
            ON DUPLICATE KEY UPDATE 
            status = VALUES(status), 
            last_checked = VALUES(last_checked), 
            response_time = VALUES(response_time),
            last_error = VALUES(last_error)
        ");
        
        $stmt->bind_param(
            "isis", 
            $row['id'],
            $status['status'],
            $status['response_time'],
            $errorMessage
        );
        
        $stmt->execute();
    }
    
    echo "Website status check completed at " . date('Y-m-d H:i:s');
} else {
    echo "No websites found to check.";
}

$conn->close();
?>
