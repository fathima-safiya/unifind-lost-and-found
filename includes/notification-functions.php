<?php
/**
 * Create a new system notification
 *
 * @param PDO $pdo Database connection
 * @param int $user_id The user ID receiving the notification
 * @param string $title Notification title
 * @param string $message Notification message body
 * @param string $type Notification type (report_submitted, report_approved, etc.)
 * @param int|null $related_item_id Optional related item ID
 * @return bool True on success, false on failure
 */
function create_notification($pdo, $user_id, $title, $message, $type, $related_item_id = null) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO notifications (user_id, title, message, type, related_item_id) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$user_id, $title, $message, $type, $related_item_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get unread notification count for a user
 *
 * @param PDO $pdo
 * @param int $user_id
 * @return int
 */
function get_unread_notification_count($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get recent notifications for a user (for the dropdown)
 *
 * @param PDO $pdo
 * @param int $user_id
 * @param int $limit
 * @return array
 */
function get_recent_notifications($pdo, $user_id, $limit = 5) {
    try {
        $stmt = $pdo->prepare(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Return a human-readable "time ago" string
 *
 * @param string $datetime  MySQL datetime string
 * @return string
 */
function time_elapsed_string($datetime) {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0)   return $diff->y . ' year'   . ($diff->y  > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0)   return $diff->m . ' month'  . ($diff->m  > 1 ? 's' : '') . ' ago';
    $weeks = (int) floor($diff->d / 7);
    if ($weeks > 0)     return $weeks   . ' week'   . ($weeks    > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0)   return $diff->d . ' day'    . ($diff->d  > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0)   return $diff->h . ' hour'   . ($diff->h  > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0)   return $diff->i . ' minute' . ($diff->i  > 1 ? 's' : '') . ' ago';
    return 'just now';
}

/**
 * Return a Font-Awesome-free SVG icon and accent colour for a notification type.
 *
 * @param string $type
 * @return array ['icon' => svg html, 'color' => css colour]
 */
function get_notification_meta($type) {
    $meta = [
        'report_submitted' => [
            'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
            'color' => 'var(--primary)',
        ],
        'report_approved'  => [
            'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            'color' => 'var(--success)',
        ],
        'report_rejected'  => [
            'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            'color' => 'var(--danger)',
        ],
        'report_updated'   => [
            'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
            'color' => 'var(--warning)',
        ],
        'possible_match'   => [
            'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
            'color' => '#a78bfa',
        ],
        'item_returned'    => [
            'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>',
            'color' => 'var(--success)',
        ],
    ];

    return $meta[$type] ?? [
        'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        'color' => 'var(--primary)',
    ];
}
?>
