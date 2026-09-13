<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/notification-functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $action  = $_POST['action'] ?? '';

    if ($item_id > 0 && !empty($action)) {
        try {
            if ($action === 'delete') {
                // Remove associated image file if it exists
                $stmt = $pdo->prepare("SELECT image FROM items WHERE id = ?");
                $stmt->execute([$item_id]);
                $item = $stmt->fetch();
                if ($item && !empty($item['image'])) {
                    $image_path = dirname(__DIR__) . '/' . $item['image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$item_id]);
                header("Location: " . $base_url . "/admin/dashboard.php?success=deleted");
                exit();
            } else {
                $status_map = [
                    'approve'  => 'active',
                    'reject'   => 'rejected',
                    'returned' => 'returned',
                ];
                if (isset($status_map[$action])) {
                    $new_status = $status_map[$action];

                    // Fetch the item so we can notify its owner
                    $itemStmt = $pdo->prepare("SELECT user_id, title FROM items WHERE id = ?");
                    $itemStmt->execute([$item_id]);
                    $item = $itemStmt->fetch();

                    $pdo->prepare("UPDATE items SET status = ? WHERE id = ?")->execute([$new_status, $item_id]);

                    // Fire the appropriate notification
                    if ($item) {
                        $item_title = htmlspecialchars($item['title']);
                        if ($action === 'approve') {
                            create_notification(
                                $pdo, $item['user_id'],
                                'Report Approved',
                                "Your report for \"$item_title\" has been approved by the administrator.",
                                'report_approved', $item_id
                            );
                        } elseif ($action === 'reject') {
                            create_notification(
                                $pdo, $item['user_id'],
                                'Report Rejected',
                                "Your report for \"$item_title\" was rejected. Please review the report details.",
                                'report_rejected', $item_id
                            );
                        } elseif ($action === 'returned') {
                            create_notification(
                                $pdo, $item['user_id'],
                                'Item Returned',
                                "Your reported item \"$item_title\" has been marked as returned.",
                                'item_returned', $item_id
                            );
                        }
                    }

                    header("Location: " . $base_url . "/admin/dashboard.php?success=$action");
                    exit();
                }
            }
        } catch (PDOException $e) {
            header("Location: " . $base_url . "/admin/dashboard.php?error=1");
            exit();
        }
    }
}

// Fallback redirect
header("Location: " . $base_url . "/admin/dashboard.php");
exit();
?>
