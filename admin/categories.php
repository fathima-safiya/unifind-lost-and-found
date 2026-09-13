<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

$success = '';
$error = '';

// Handle Actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
            $success = "Category added.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        if (!empty($name) && $id > 0) {
            $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
            $success = "Category updated.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        // Check if items exist
        $check = $pdo->prepare("SELECT count(*) FROM items WHERE category_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            $error = "Cannot delete category because it is in use by existing reports.";
        } else {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            $success = "Category deleted.";
        }
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

?>
<?php include '../includes/header.php'; ?>

<div class="layout-container">
    
    <aside class="layout-sidebar">
        <div class="item-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-blue);">Admin Panel</h3>
            <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="dashboard.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Dashboard</a>
                <a href="items.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Items</a>
                <a href="users.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Users</a>
                <a href="categories.php" class="btn btn-primary" style="text-align: left; background-color: var(--primary-blue);">Categories</a>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 1rem 0;">
                <a href="../logout.php" class="btn btn-outline" style="text-align: left; border: none; color: var(--danger); justify-content: flex-start;">Logout</a>
            </nav>
        </div>
    </aside>

    <main class="layout-main">
        <h1 style="margin-bottom: 2rem;">Category Management</h1>
        


        <!-- Add Category Form -->
        <div class="item-card" style="padding: 1.5rem; margin-bottom: 2rem; border-top: 4px solid var(--primary-blue);">
            <h3 style="margin-bottom: 1rem;">Add New Category</h3>
            <form method="POST" style="display: flex; gap: 1rem;">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" required placeholder="E.g. Electronics, Clothing..." class="form-control" style="flex: 1;">
                <button type="submit" class="btn btn-primary">Add Category</button>
            </form>
        </div>

        <div class="item-card table-responsive" style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 1rem 0.5rem;">Category Name</th>
                        <th style="padding: 1rem 0.5rem;">Created</th>
                        <th style="padding: 1rem 0.5rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1rem 0.5rem; font-weight: 500;">
                                <form method="POST" style="display: flex; gap: 0.5rem;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($c['name']); ?>" required class="form-control" style="padding: 0.4rem;">
                                    <button type="submit" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;">Save</button>
                                </form>
                            </td>
                            <td style="padding: 1rem 0.5rem; font-size: 0.85rem; color: var(--secondary-text);">
                                <?php echo date('M d, Y', strtotime($c['created_at'])); ?>
                            </td>
                            <td style="padding: 1rem 0.5rem; text-align: right;">
                                <form method="POST" style="display:inline;" data-confirm="Delete this category? This cannot be undone.">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; color: var(--danger); border-color: var(--danger);">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
