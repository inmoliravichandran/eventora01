<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userRole = $isLoggedIn ? ($_SESSION['role'] ?? 'user') : '';
?>
<header class="global-header">
    <div class="logo" onclick="window.location.href='index.php'" style="cursor: pointer; font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #fbbf24, #f59e0b); -webkit-background-clip: text; background-clip: text; color: transparent;">Eventora</div>
    <nav class="nav-links">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="services.php"><i class="fas fa-calendar-alt"></i> Services</a>
        <a href="about.php"><i class="fas fa-compass"></i> How it works</a>
        
        <?php if ($isLoggedIn): ?>
            <?php if ($userRole === 'admin'): ?>
                <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
            <?php endif; ?>
            <a href="profile.php"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($userName); ?></a>
            <a href="#" onclick="logout(); return false;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
        <?php endif; ?>
        
        <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
        <a href="cart.php" id="cartNavLink"><i class="fas fa-shopping-cart"></i> Cart</a>
    </nav>
</header>
