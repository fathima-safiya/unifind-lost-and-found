<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/notification-functions.php';

requireLogin();

$user_id = (int) $_SESSION['user_id'];

// Mark all as read if button pressed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all'])) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
        ->execute([$user_id]);
    header("Location: notifications.php");
    exit;
}

// Fetch all notifications for this user
try {
    $stmt = $pdo->prepare(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC"
    );
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    $notifications = [];
}

$unread_count = get_unread_notification_count($pdo, $user_id);
?>
<?php include 'includes/header.php'; ?>

<section class="container" style="max-width: 860px; padding: 3rem 1.5rem;">
    <!-- Back button -->
    <div style="margin-bottom: 2rem; margin-left: -0.5rem;">
        <a href="dashboard.php" style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%; background-color: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--white); text-decoration: none; transition: all 0.3s ease;"
           onmouseover="this.style.backgroundColor='rgba(255,255,255,0.15)'; this.style.transform='translateX(-4px)'"
           onmouseout="this.style.backgroundColor='rgba(255,255,255,0.05)'; this.style.transform='translateX(0)'"
           title="Go Back">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
        </a>
    </div>

    <!-- Page Header -->
    <div class="notif-page-header">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <h2>Notifications</h2>
            <?php if ($unread_count > 0): ?>
                <span class="notif-page-badge">
                    <?php echo $unread_count; ?> unread
                </span>
            <?php endif; ?>
        </div>
        <?php if ($unread_count > 0): ?>
            <form method="POST" style="margin: 0;">
                <button type="submit" name="mark_all" value="1" class="notif-mark-all-page-btn">
                    Mark all as read
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Notifications list -->
    <?php if (empty($notifications)): ?>
        <div class="notif-page-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <h3>No notifications yet</h3>
            <p>Your UniFind notifications will appear here.</p>
        </div>
    <?php else: ?>
        <div id="notif-page-list">
            <?php foreach ($notifications as $n):
                $meta = get_notification_meta($n['type']);
            ?>
            <div class="notif-card <?php echo $n['is_read'] ? 'notif-read' : 'notif-unread'; ?>"
                 id="notif-page-item-<?php echo (int)$n['id']; ?>"
                 data-id="<?php echo (int)$n['id']; ?>"
                 data-read="<?php echo (int)$n['is_read']; ?>">

                <div class="notif-card-icon" style="color: <?php echo $meta['color']; ?>; background: <?php echo $meta['color']; ?>22;">
                    <?php echo $meta['icon']; ?>
                </div>

                <div class="notif-card-body">
                    <p class="notif-card-title"><?php echo htmlspecialchars($n['title']); ?></p>
                    <p class="notif-card-msg"><?php echo htmlspecialchars($n['message']); ?></p>
                    <div class="notif-card-footer">
                        <span class="notif-card-time">
                            <?php echo time_elapsed_string($n['created_at']); ?>
                            &nbsp;·&nbsp;
                            <?php echo date('d M Y, g:i A', strtotime($n['created_at'])); ?>
                        </span>
                        <?php if (!empty($n['related_item_id'])): ?>
                            <a href="item-details.php?id=<?php echo (int)$n['related_item_id']; ?>"
                               class="notif-card-view-link">View Item</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$n['is_read']): ?>
                    <span class="notif-card-unread-dot" title="Unread"></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
// Mark single notification as read when clicked on this page
(function () {
    const BASE_URL = window._unifindBaseUrl || '';
    const API_URL  = BASE_URL + '/ajax/notifications_api.php';
    const list     = document.getElementById('notif-page-list');
    if (!list) return;

    list.addEventListener('click', function (e) {
        const card = e.target.closest('.notif-card.notif-unread');
        if (!card) return;

        const id = card.dataset.id;
        if (!id) return;

        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_read&notification_id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(function (data) {
            if (data.success) {
                card.classList.remove('notif-unread');
                card.classList.add('notif-read');
                const dot = card.querySelector('.notif-card-unread-dot');
                if (dot) dot.remove();
                // Update the header bell badge
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    const count = data.unread_count;
                    if (count > 0) {
                        badge.textContent = count;
                        badge.classList.remove('notif-badge-hidden');
                    } else {
                        badge.classList.add('notif-badge-hidden');
                    }
                }
            }
        })
        .catch(function () {/* silently ignore */});
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
