<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="app-header">
    <div class="header-left">
        <button id="menu-toggle"><i class="fas fa-bars"></i></button>
        <h2 class="header-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
    </div>
    <div class="header-right">
        <div class="theme-toggle">
            <i class="fas fa-moon"></i>
            <label class="switch">
                <input type="checkbox" id="theme-switch" aria-label="Toggle dark mode">
                <span class="slider round"></span>
            </label>
            <i class="fas fa-sun"></i>
        </div>
        <?php if (isset($_SESSION['username'])): ?>
        <div class="user-menu">
            <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="logout-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</header>
