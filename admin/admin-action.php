<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

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
                    $pdo->prepare("UPDATE items SET status = ? WHERE id = ?")->execute([$new_status, $item_id]);
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
