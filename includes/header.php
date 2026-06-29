<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect the base path relative to the current file
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path_prefix = ($current_dir === 'pages') ? '../' : '';
?>
<header>
    <div class="container nav-container">
        <a href="<?php echo $path_prefix; ?>index.php" class="logo">
            <i class="fa-solid fa-gamepad"></i> <span>DYSCOVER</span> NEXUS
        </a>
        <button class="mobile-menu-btn"><i class="fa-solid fa-bars"></i></button>
        <nav class="nav-links">
            <a href="<?php echo $path_prefix; ?>index.php">Home</a>
            <a href="<?php echo $path_prefix; ?>pages/about.php">About Game</a>
            <a href="<?php echo $path_prefix; ?>pages/product.php">Store</a>
            <a href="<?php echo $path_prefix; ?>pages/contact.php">Contact</a>
        </nav>
        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo $path_prefix; ?>pages/dashboard.php" class="btn btn-secondary" title="My Dashboard"><i class="fa-solid fa-user-astronaut"></i> Dashboard</a>
                <a href="<?php echo $path_prefix; ?>pages/logout.php" class="btn btn-primary" title="Logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php else: ?>
                <a href="<?php echo $path_prefix; ?>pages/login.php" class="btn btn-secondary">Login</a>
                <a href="<?php echo $path_prefix; ?>pages/register.php" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
            <a href="<?php echo $path_prefix; ?>pages/cart.php" class="btn btn-secondary"><i class="fa-solid fa-cart-shopping"></i></a>
        </div>
    </div>
</header>
