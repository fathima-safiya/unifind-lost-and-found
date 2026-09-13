<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniFind - SLIATE – Kurunegala Lost and Found</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <a href="<?php echo $base_url; ?>/index.php" class="navbar-brand">
                UniFind <span>SLIATE – Kurunegala</span>
            </a>
            
            <button class="mobile-menu-btn" onclick="document.getElementById('nav-wrapper').classList.toggle('active')">
                ☰
            </button>
            
            <div class="nav-wrapper" id="nav-wrapper">
                <nav class="nav-links">
                    <a href="<?php echo $base_url; ?>/index.php">Home</a>
                    <a href="<?php echo $base_url; ?>/items.php">Browse Items</a>
                    <a href="<?php echo $base_url; ?>/report-lost.php">Report Lost</a>
                    <a href="<?php echo $base_url; ?>/report-found.php">Report Found</a>
                </nav>
                <div class="nav-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="btn btn-outline" style="border-color: var(--warning); color: var(--warning);">Admin Dashboard</a>
                        <?php else: ?>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="btn btn-outline">Dashboard</a>
                        <?php endif; ?>
                        <a href="<?php echo $base_url; ?>/logout.php" class="btn btn-primary" style="background-color: var(--danger); border-color: var(--danger);">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>/login.php" class="btn btn-outline">Login</a>
                        <a href="<?php echo $base_url; ?>/register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Global Toast Container -->
    <div id="toast-container"></div>

    <!-- Global Confirm Modal -->
    <div id="confirm-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Confirm Action</div>
            <div class="modal-body">
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline btn-cancel">Cancel</button>
                <button type="button" class="btn btn-primary btn-confirm" style="background: var(--danger); border-color: var(--danger);">Yes, Delete</button>
            </div>
        </div>
    </div>

    <main>
