<div class="sidebar modern-sidebar">
    <div class="sidebar-header">
        <h3><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></h3>
    </div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <?php if (isset($_SESSION['admin_role']) && ($_SESSION['admin_role'] === 'superadmin' || $_SESSION['admin_role'] === 'admin')): ?>
        <li><a href="add_website.php"><i class="fas fa-plus"></i> Add Website</a></li>
        <?php endif; ?>
        <li><a href="activity_log.php"><i class="fas fa-list-alt"></i> Activity Log</a></li>
        <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin'): ?>
        <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        <?php endif; ?>
        <li><a href="unlock_account.php"><i class="fas fa-unlock"></i> Unlock Accounts</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>
<!-- Sidebar overlay background for mobile -->
<div class="sidebar-overlay-bg"></div>
