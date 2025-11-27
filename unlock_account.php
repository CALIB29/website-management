<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'database.php';

/*
{ changed code }
*/
// ensure unlock_audit has required columns and unlock_audit_ips exists
function column_exists($conn, $table, $column) {
    // use real_escape_string and build the query directly because prepared statements
    // cannot bind identifiers (table/column names)
    $tableEsc = $conn->real_escape_string($table);
    $colEsc = $conn->real_escape_string($column);
    $sql = "SHOW COLUMNS FROM `{$tableEsc}` LIKE '{$colEsc}'";
    $res = $conn->query($sql);
    $exists = ($res && $res->num_rows > 0);
    if ($res) { $res->free(); }
    return $exists;
}

// create unlock_audit table if missing (base structure)
$conn->query("CREATE TABLE IF NOT EXISTS unlock_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    username VARCHAR(150) NOT NULL,
    unlocked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    admin_ip VARCHAR(45),
    fail_count INT DEFAULT 0,
    ip_list TEXT,
    INDEX (admin_id),
    INDEX (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// add missing columns if table existed without them
if (!column_exists($conn, 'unlock_audit', 'fail_count')) {
    $conn->query("ALTER TABLE unlock_audit ADD COLUMN fail_count INT DEFAULT 0");
}
if (!column_exists($conn, 'unlock_audit', 'ip_list')) {
    $conn->query("ALTER TABLE unlock_audit ADD COLUMN ip_list TEXT");
}

// create normalized IP table
$conn->query("CREATE TABLE IF NOT EXISTS unlock_audit_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    FOREIGN KEY (audit_id) REFERENCES unlock_audit(id) ON DELETE CASCADE,
    INDEX (audit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$success = '';
$error = '';
$throttleWindowMinutes = 15;
$threshold = 5;
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Handle CSV export of selected audit rows
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv']) && !empty($_POST['selected']) && is_array($_POST['selected'])) {
    $ids = array_map('intval', $_POST['selected']);
    if (empty($ids)) {
        // nothing selected
    } else {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        // Build types and params dynamically
        $types = str_repeat('i', count($ids));
        $sql = "SELECT ua.*, a.username AS admin_username FROM unlock_audit ua LEFT JOIN admins a ON ua.admin_id = a.id WHERE ua.id IN (" . $placeholders . ") ORDER BY ua.unlocked_at DESC";
        $stmt = $conn->prepare($sql);
        // bind params dynamically
        $bind_names[] = $types;
        foreach ($ids as $k => $id) { $bind_names[] = &$ids[$k]; }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        $auditIds = [];
        while ($r = $res->fetch_assoc()) { $rows[] = $r; $auditIds[] = $r['id']; }

        // fetch ips
        $ipMap = [];
        if (!empty($auditIds)) {
            $placeholders2 = implode(',', array_fill(0, count($auditIds), '?'));
            $types2 = str_repeat('i', count($auditIds));
            $sql2 = "SELECT audit_id, GROUP_CONCAT(ip_address SEPARATOR ', ') AS ips FROM unlock_audit_ips WHERE audit_id IN (" . $placeholders2 . ") GROUP BY audit_id";
            $stmt2 = $conn->prepare($sql2);
            $bind2[] = $types2;
            foreach ($auditIds as $k => $v) { $bind2[] = &$auditIds[$k]; }
            call_user_func_array(array($stmt2, 'bind_param'), $bind2);
            $stmt2->execute();
            $r2 = $stmt2->get_result();
            while ($rr = $r2->fetch_assoc()) { $ipMap[$rr['audit_id']] = $rr['ips']; }
        }

        // Output CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=unlock_audit_export.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','admin','username','unlocked_at','admin_ip','fail_count','ip_list']);
        foreach ($rows as $r) {
            $aid = $r['id'];
            $admin = $r['admin_username'] ?: $r['admin_id'];
            $ips = isset($ipMap[$aid]) ? $ipMap[$aid] : $r['ip_list'];
            fputcsv($out, [$aid, $admin, $r['username'], $r['unlocked_at'], $r['admin_ip'], $r['fail_count'], $ips]);
        }
        exit();
    }
}

// Handle unlock action
if (isset($_POST['unlock_username'])) {
    $u = $_POST['unlock_username'];
    if (!empty($u)) {
        // Before deleting, collect fail count and IP list for audit
        $infoStmt = $conn->prepare("SELECT COUNT(*) AS cnt, GROUP_CONCAT(DISTINCT ip_address SEPARATOR ', ') AS ips FROM login_attempts WHERE username = ? AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
        $infoStmt->bind_param("si", $u, $throttleWindowMinutes);
        $infoStmt->execute();
        $info = $infoStmt->get_result()->fetch_assoc();
        $failCountBefore = (int)$info['cnt'];
        $ipList = $info['ips'];

        // Delete failed attempts for this username
        $del = $conn->prepare("DELETE FROM login_attempts WHERE username = ? AND success = 0");
        $del->bind_param("s", $u);
        if ($del->execute()) {
            // Insert audit record with details
            $admin_id = $_SESSION['admin_id'];
            $admin_ip = $_SERVER['REMOTE_ADDR'];

            $ins = $conn->prepare("INSERT INTO unlock_audit (admin_id, username, unlocked_at, admin_ip, fail_count, ip_list) VALUES (?, ?, NOW(), ?, ?, ?)");
            $ins->bind_param("issis", $admin_id, $u, $admin_ip, $failCountBefore, $ipList);
            $ins->execute();
            $audit_id = $ins->insert_id;
            // normalize ips into unlock_audit_ips
            if (!empty($ipList)) {
                $ips = explode(', ', $ipList);
                $ipIns = $conn->prepare("INSERT INTO unlock_audit_ips (audit_id, ip_address) VALUES (?, ?)");
                foreach ($ips as $ip) {
                    $ipIns->bind_param("is", $audit_id, $ip);
                    $ipIns->execute();
                }
            }

            $success = "Cleared failed attempts for user '" . htmlspecialchars($u) . "'.";
        } else {
            $error = "Error clearing attempts: " . $del->error;
        }
    }
}

// Prepare search pattern
$like = '%' . $q . '%';

// Count total matching locked accounts for pagination
$countSql = "SELECT COUNT(*) AS total FROM (
    SELECT username FROM login_attempts
    WHERE username IS NOT NULL AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    GROUP BY username HAVING COUNT(*) >= ? AND username LIKE ?
) AS t";
$cntStmt = $conn->prepare($countSql);
$cntStmt->bind_param("iis", $throttleWindowMinutes, $threshold, $like);
$cntStmt->execute();
$totalRes = $cntStmt->get_result()->fetch_assoc();
$total = (int)$totalRes['total'];

// Get page of locked accounts
$stmt = $conn->prepare("SELECT username, COUNT(*) AS fail_count, MAX(attempt_time) AS last_attempt FROM login_attempts WHERE username IS NOT NULL AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE) GROUP BY username HAVING fail_count >= ? AND username LIKE ? ORDER BY last_attempt DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iisii", $throttleWindowMinutes, $threshold, $like, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$locked = [];
while ($row = $result->fetch_assoc()) {
    $locked[] = $row;
}

// Fetch recent unlock audit entries
$auditStmt = $conn->prepare("SELECT ua.*, a.username AS admin_username FROM unlock_audit ua LEFT JOIN admins a ON ua.admin_id = a.id ORDER BY ua.unlocked_at DESC LIMIT 20");
$auditStmt->execute();
$auditRes = $auditStmt->get_result();
$audits = [];
while ($r = $auditRes->fetch_assoc()) { $audits[] = $r; }

// Fetch normalized IPs for the fetched audits
if (!empty($audits)) {
    $auditIds = array_column($audits, 'id');
    $placeholders = implode(',', array_fill(0, count($auditIds), '?'));
    $types = str_repeat('i', count($auditIds));
    $sql = "SELECT audit_id, GROUP_CONCAT(ip_address SEPARATOR ', ') AS ips FROM unlock_audit_ips WHERE audit_id IN (" . $placeholders . ") GROUP BY audit_id";
    $stmt = $conn->prepare($sql);
    $bind[] = $types;
    foreach ($auditIds as $k => $v) { $bind[] = &$auditIds[$k]; }
    call_user_func_array(array($stmt, 'bind_param'), $bind);
    $stmt->execute();
    $res = $stmt->get_result();
    $ipMap = [];
    while ($rr = $res->fetch_assoc()) { $ipMap[$rr['audit_id']] = $rr['ips']; }
    foreach ($audits as &$a) {
        $a['ip_list'] = isset($ipMap[$a['id']]) ? $ipMap[$a['id']] : $a['ip_list'];
    }
    unset($a);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Accounts - SRC Admin</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="app-header">
                <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h2 class="header-title">Unlock Accounts</h2>
            </div>

            <div class="content">
                <div class="form-container">
                    <?php if ($success) { echo "<p class='success'>" . $success . "</p>"; } ?>
                    <?php if ($error) { echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; } ?>

                    <h3>Locked Accounts (last <?php echo $throttleWindowMinutes; ?> minutes)</h3>

                    <form method="GET" class="search-form" style="margin-bottom:12px; display:flex; gap:8px; align-items:center;">
                        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search username" style="flex:1; padding:8px;">
                        <button type="submit" class="btn">Search</button>
                    </form>

                    <?php if (empty($locked)) { ?>
                        <p>No locked accounts found.</p>
                    <?php } else { ?>
                        <div class="table-responsive">
                        <table>
                            <tr><th>Username</th><th>Failed Attempts</th><th>Last Attempt</th><th>Action</th></tr>
                            <?php foreach ($locked as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo (int)$row['fail_count']; ?></td>
                                    <td><?php echo htmlspecialchars($row['last_attempt']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline" onsubmit="return confirmUnlock(this);">
                                            <input type="hidden" name="unlock_username" value="<?php echo htmlspecialchars($row['username']); ?>">
                                            <button type="submit" class="btn">Unlock</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                        </div>
                    <?php } ?>
                    
                    <!-- Pagination -->
                    <?php if ($total > $perPage) { $totalPages = (int)ceil($total / $perPage); ?>
                        <div style="margin-top:12px; display:flex; gap:8px; align-items:center;">
                            <?php for ($p = 1; $p <= $totalPages; $p++) { ?>
                                <a href="?q=<?php echo urlencode($q); ?>&page=<?php echo $p; ?>" class="btn" style="padding:6px 10px; <?php echo $p === $page ? 'background-color:var(--primary-color);' : ''; ?>"><?php echo $p; ?></a>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <hr style="margin:20px 0;">
                    <h3>Recent Unlock Audit</h3>
                    <?php if (empty($audits)) { ?>
                        <p>No recent unlock actions.</p>
                    <?php } else { ?>
                        <form method="POST">
                        <div class="table-responsive">
                        <table>
                            <tr><th></th><th>Admin</th><th>Username</th><th>When</th><th>Admin IP</th><th>Fail Count</th><th>IPs</th></tr>
                            <?php foreach ($audits as $a) { ?>
                                <tr>
                                    <td><input type="checkbox" name="selected[]" value="<?php echo (int)$a['id']; ?>"></td>
                                    <td><?php echo htmlspecialchars($a['admin_username'] ?: $a['admin_id']); ?></td>
                                    <td><?php echo htmlspecialchars($a['username']); ?></td>
                                    <td><?php echo htmlspecialchars($a['unlocked_at']); ?></td>
                                    <td><?php echo htmlspecialchars($a['admin_ip']); ?></td>
                                    <td><?php echo (int)$a['fail_count']; ?></td>
                                    <td><?php echo htmlspecialchars($a['ip_list']); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                        </div>
                        <div style="margin-top:8px;">
                            <button type="submit" name="export_csv" class="btn">Export Selected as CSV</button>
                        </div>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'bottom-nav.php'; ?>
    <script src="script.js"></script>
    <script>
        function confirmUnlock(form) {
            var user = form.querySelector('input[name="unlock_username"]').value;
            return confirm('Are you sure you want to clear failed attempts for ' + user + '?');
        }
    </script>
</body>
</html>
