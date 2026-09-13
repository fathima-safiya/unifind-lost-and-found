<?php
/**
 * notifications_api.php
 * AJAX endpoint – handles mark-as-read and unread-count requests.
 * Only authenticated users can access their own notifications.
 */
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$action  = $_POST['action'] ?? '';

if ($action === 'mark_read') {
    // Mark a single notification as read
    $notification_id = isset($_POST['notification_id']) ? (int) $_POST['notification_id'] : 0;
    if ($notification_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
        exit;
    }
    // Security: only update notifications that belong to this user
    $stmt = $pdo->prepare(
        "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$notification_id, $user_id]);

    $unread = get_unread_count($pdo, $user_id);
    echo json_encode(['success' => true, 'unread_count' => $unread]);

} elseif ($action === 'mark_all_read') {
    // Mark ALL unread notifications for this user as read
    $stmt = $pdo->prepare(
        "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0"
    );
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'unread_count' => 0]);

} elseif ($action === 'get_count') {
    // Return current unread count only
    $unread = get_unread_count($pdo, $user_id);
    echo json_encode(['success' => true, 'unread_count' => $unread]);

} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

// ─── helper ──────────────────────────────────────────────────────────────────
function get_unread_count($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int) $stmt->fetchColumn();
}
?>
