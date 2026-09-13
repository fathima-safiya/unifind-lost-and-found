<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load notification helpers if a user is logged in
if (isset($_SESSION['user_id']) && function_exists('get_recent_notifications') === false) {
    if (file_exists(__DIR__ . '/notification-functions.php')) {
        require_once __DIR__ . '/notification-functions.php';
    }
}

// Fetch unread count & recent notifications for the bell dropdown
$_notif_unread   = 0;
$_notif_recent   = [];
if (isset($_SESSION['user_id']) && isset($pdo) && function_exists('get_unread_notification_count')) {
    $_notif_unread  = get_unread_notification_count($pdo, $_SESSION['user_id']);
    $_notif_recent  = get_recent_notifications($pdo, $_SESSION['user_id'], 5);
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

                        <!-- ── Notification Bell ── -->
                        <div class="notif-bell-wrapper" id="notif-bell-wrapper">
                            <button class="notif-bell-btn" id="notif-bell-btn"
                                    aria-label="Notifications"
                                    aria-expanded="false"
                                    data-base-url="<?php echo $base_url; ?>">
                                <svg class="notif-bell-icon" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                </svg>
                                <?php if ($_notif_unread > 0): ?>
                                    <span class="notif-badge" id="notif-badge">
                                        <?php echo min($_notif_unread, 99); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="notif-badge notif-badge-hidden" id="notif-badge">0</span>
                                <?php endif; ?>
                            </button>

                            <!-- Dropdown -->
                            <div class="notif-dropdown" id="notif-dropdown" role="dialog" aria-label="Notifications panel">
                                <div class="notif-dropdown-header">
                                    <span>Notifications</span>
                                    <?php if ($_notif_unread > 0): ?>
                                        <button class="notif-mark-all-btn" id="notif-mark-all-btn">
                                            Mark all as read
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="notif-list" id="notif-list">
                                    <?php if (empty($_notif_recent)): ?>
                                        <div class="notif-empty">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                            </svg>
                                            <p>No notifications yet</p>
                                            <small>Your UniFind notifications will appear here.</small>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($_notif_recent as $n):
                                            $meta = get_notification_meta($n['type']);
                                        ?>
                                        <div class="notif-item <?php echo $n['is_read'] ? 'notif-read' : 'notif-unread'; ?>"
                                             id="notif-item-<?php echo $n['id']; ?>"
                                             data-id="<?php echo $n['id']; ?>"
                                             data-read="<?php echo $n['is_read']; ?>">
                                            <div class="notif-item-icon" style="color: <?php echo $meta['color']; ?>; background: <?php echo $meta['color']; ?>22;">
                                                <?php echo $meta['icon']; ?>
                                            </div>
                                            <div class="notif-item-body">
                                                <p class="notif-item-title"><?php echo htmlspecialchars($n['title']); ?></p>
                                                <p class="notif-item-msg"><?php echo htmlspecialchars($n['message']); ?></p>
                                                <span class="notif-item-time"><?php echo time_elapsed_string($n['created_at']); ?></span>
                                            </div>
                                            <?php if (!$n['is_read']): ?>
                                                <span class="notif-dot"></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php echo $base_url; ?>/notifications.php" class="notif-view-all">
                                    View All Notifications
                                </a>
                            </div>
                        </div>
                        <!-- ── /Notification Bell ── -->

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
    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
        // Pass base URL to JS
        window._unifindBaseUrl = '<?php echo $base_url; ?>';
    </script>
    <script src="<?php echo $base_url; ?>/assets/js/notifications.js" defer></script>
    <?php endif; ?>
    
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
