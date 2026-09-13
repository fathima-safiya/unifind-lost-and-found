<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

// Flash message handling
$success = '';
$error = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'approved') $success = "Report approved successfully.";
    if ($_GET['success'] == 'rejected') $success = "Report rejected.";
    if ($_GET['success'] == 'returned') $success = "Item marked as returned.";
    if ($_GET['success'] == 'deleted') $success = "Report deleted.";
}
if (isset($_GET['error'])) {
    $error = "Failed to perform action. Please try again.";
}

// Dashboard stats
$stats = [
    'users'    => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'items'    => $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn(),
    'lost'     => $pdo->query("SELECT COUNT(*) FROM items WHERE type = 'lost'")->fetchColumn(),
    'found'    => $pdo->query("SELECT COUNT(*) FROM items WHERE type = 'found'")->fetchColumn(),
    'returned' => $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'returned'")->fetchColumn(),
    'pending'  => $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'pending'")->fetchColumn()
];

// Most recent 10 reports
$stmt = $pdo->query("
    SELECT i.id, i.title, i.type, i.status, i.created_at,
           c.name AS category_name, u.full_name AS reporter
    FROM items i
    JOIN categories c ON i.category_id = c.id
    JOIN users u ON i.user_id = u.id
    ORDER BY i.created_at DESC
    LIMIT 10
");
$recent_reports = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="layout-container">

    <!-- Admin Sidebar -->
    <aside class="layout-sidebar">
        <div class="item-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-blue);">Admin Panel</h3>
            <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="btn btn-primary" style="text-align: left;">Dashboard</a>
                <a href="<?php echo $base_url; ?>/admin/items.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Items</a>
                <a href="<?php echo $base_url; ?>/admin/users.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Users</a>
                <a href="<?php echo $base_url; ?>/admin/categories.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Categories</a>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 1rem 0;">
                <a href="<?php echo $base_url; ?>/logout.php" class="btn btn-outline" style="text-align: left; border: none; color: var(--danger); justify-content: flex-start;">Logout</a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="layout-main">
        <h1 style="margin-bottom: 2rem;">Admin Dashboard</h1>

        <!-- Stats Cards -->
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 3rem;">
            <div class="stat-card">
                <h3 style="font-size: 2rem;"><?php echo $stats['users']; ?></h3>
                <p class="text-secondary">Total Users</p>
            </div>
            <div class="stat-card">
                <h3 style="font-size: 2rem; color: var(--primary-blue);"><?php echo $stats['items']; ?></h3>
                <p class="text-secondary">Total Items</p>
            </div>
            <div class="stat-card">
                <h3 style="font-size: 2rem; color: var(--danger);"><?php echo $stats['lost']; ?></h3>
                <p class="text-secondary">Lost Items</p>
            </div>
            <div class="stat-card">
                <h3 style="font-size: 2rem; color: var(--success);"><?php echo $stats['found']; ?></h3>
                <p class="text-secondary">Found Items</p>
            </div>
            <div class="stat-card">
                <h3 style="font-size: 2rem; color: var(--success);"><?php echo $stats['returned']; ?></h3>
                <p class="text-secondary">Returned Items</p>
            </div>
            <div class="stat-card">
                <h3 style="font-size: 2rem; color: var(--warning);"><?php echo $stats['pending']; ?></h3>
                <p class="text-secondary">Pending Reports</p>
            </div>
        </div>

        <!-- Recent Reports Table -->
        <div class="item-card" style="padding: 1.5rem; overflow-x: auto;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Recent Reports</h2>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.1);">
                        <th style="padding: 1rem 0.5rem;">Item</th>
                        <th style="padding: 1rem 0.5rem;">Type</th>
                        <th style="padding: 1rem 0.5rem;">Category</th>
                        <th style="padding: 1rem 0.5rem;">Reporter</th>
                        <th style="padding: 1rem 0.5rem;">Status</th>
                        <th style="padding: 1rem 0.5rem;">Date</th>
                        <th style="padding: 1rem 0.5rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_reports as $report): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 1rem 0.5rem; font-weight: 500;">
                                <?php echo htmlspecialchars($report['title']); ?>
                            </td>
                            <td style="padding: 1rem 0.5rem;">
                                <?php if ($report['type'] === 'lost'): ?>
                                    <span style="color: var(--danger); font-weight: 600; font-size: 0.85rem;">Lost</span>
                                <?php else: ?>
                                    <span style="color: var(--success); font-weight: 600; font-size: 0.85rem;">Found</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem 0.5rem; opacity: 0.7;"><?php echo htmlspecialchars($report['category_name']); ?></td>
                            <td style="padding: 1rem 0.5rem; opacity: 0.7;"><?php echo htmlspecialchars($report['reporter']); ?></td>
                            <td style="padding: 1rem 0.5rem;">
                                <?php
                                $badges = [
                                    'active'   => ['#DBEAFE', '#1E3A8A'],
                                    'pending'  => ['#FEF3C7', '#92400E'],
                                    'returned' => ['#DCFCE7', '#166534'],
                                    'rejected' => ['#FEE2E2', '#991B1B'],
                                ];
                                $badge = $badges[$report['status']] ?? ['#1E293B', '#94A3B8'];
                                ?>
                                <span style="background-color: <?php echo $badge[0]; ?>; color: <?php echo $badge[1]; ?>; padding: 0.2rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem 0.5rem; font-size: 0.85rem; opacity: 0.7;">
                                <?php echo date('M d, Y', strtotime($report['created_at'])); ?>
                            </td>
                            <td style="padding: 1rem 0.5rem; text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="<?php echo $base_url; ?>/item-details.php?id=<?php echo $report['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">View</a>
                                    <form method="POST" action="<?php echo $base_url; ?>/admin/admin-action.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $report['id']; ?>">
                                        <select name="action" onchange="this.form.submit()" style="padding: 0.25rem; font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.3); color: var(--white); border-radius: var(--radius-sm);">
                                            <option value="">Action...</option>
                                            <option value="approve">Approve</option>
                                            <option value="reject">Reject</option>
                                            <option value="returned">Mark Returned</option>
                                            <option value="delete">Delete</option>
                                        </select>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
