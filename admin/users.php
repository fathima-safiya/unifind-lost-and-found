<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

$success = '';
$error = '';

// Handle Actions (Change Role, Deactivate/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];

    // Prevent acting on yourself to avoid locking yourself out
    if ($user_id === $_SESSION['user_id']) {
        $error = "You cannot perform this action on your own account.";
    } else {
        if ($action === 'make_admin') {
            $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$user_id]);
            $success = "User promoted to admin.";
        } elseif ($action === 'make_student') {
            $pdo->prepare("UPDATE users SET role = 'student' WHERE id = ?")->execute([$user_id]);
            $success = "User changed to student.";
        } elseif ($action === 'delete') {
            // Check if user has items
            $check = $pdo->prepare("SELECT count(*) FROM items WHERE user_id = ?");
            $check->execute([$user_id]);
            if ($check->fetchColumn() > 0) {
                $error = "Cannot delete user. They have reported items. Deactivate them instead.";
            } else {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                $success = "User deleted successfully.";
            }
        }
    }
}

// Search & Fetch Users
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM users WHERE full_name LIKE :s1 OR email LIKE :s2 OR student_id LIKE :s3 ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(['s1' => "%$search%", 's2' => "%$search%", 's3' => "%$search%"]);
$users = $stmt->fetchAll();

?>
<?php include '../includes/header.php'; ?>

<div class="layout-container">
    
    <aside class="layout-sidebar">
        <div class="item-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-blue);">Admin Panel</h3>
            <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="dashboard.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Dashboard</a>
                <a href="items.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Items</a>
                <a href="users.php" class="btn btn-primary" style="text-align: left; background-color: var(--primary-blue);">Users</a>
                <a href="categories.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Categories</a>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 1rem 0;">
                <a href="../logout.php" class="btn btn-outline" style="text-align: left; border: none; color: var(--danger); justify-content: flex-start;">Logout</a>
            </nav>
        </div>
    </aside>

    <main class="layout-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>User Management</h1>
        </div>
        


        <div class="item-card" style="padding: 1.5rem; margin-bottom: 2rem;">
            <form method="GET" style="display: flex; gap: 1rem;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, email, or student ID..." class="form-control" style="flex: 1; background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="users.php" class="btn btn-outline">Clear</a>
            </form>
        </div>

        <div class="item-card table-responsive" style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 1rem 0.5rem;">Name</th>
                        <th style="padding: 1rem 0.5rem;">Email / ID</th>
                        <th style="padding: 1rem 0.5rem;">Role</th>
                        <th style="padding: 1rem 0.5rem;">Joined</th>
                        <th style="padding: 1rem 0.5rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1rem 0.5rem; font-weight: 500;"><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td style="padding: 1rem 0.5rem; color: var(--secondary-text);">
                                <?php echo htmlspecialchars($u['email']); ?><br>
                                <small>ID: <?php echo htmlspecialchars($u['student_id'] ?? 'N/A'); ?></small>
                            </td>
                            <td style="padding: 1rem 0.5rem;">
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span style="background-color: #FEF3C7; color: #92400E; padding: 0.2rem 0.5rem; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">Admin</span>
                                <?php else: ?>
                                    <span style="background-color: var(--surface-dim); color: var(--secondary-text); padding: 0.2rem 0.5rem; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">Student</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem 0.5rem; font-size: 0.85rem; color: var(--secondary-text);">
                                <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                            </td>
                            <td style="padding: 1rem 0.5rem; text-align: right;">
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" data-confirm="Are you sure?">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <select name="action" onchange="this.form.submit()" style="padding: 0.25rem; font-size: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm);">
                                            <option value="">Action...</option>
                                            <?php if ($u['role'] === 'student'): ?>
                                                <option value="make_admin">Make Admin</option>
                                            <?php else: ?>
                                                <option value="make_student">Make Student</option>
                                            <?php endif; ?>
                                            <option value="delete">Delete User</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="text-secondary" style="font-size: 0.85rem;">(You)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
