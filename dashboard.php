<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Protect this page - only logged in users allowed
requireLogin();

$user = getCurrentUser();
$user_id = $user['id'];

// Fetch user stats securely
$stats_stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN type = 'lost' THEN 1 ELSE 0 END) as lost_items,
    SUM(CASE WHEN type = 'found' THEN 1 ELSE 0 END) as found_items,
    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_items
    FROM items WHERE user_id = :user_id");
$stats_stmt->execute(['user_id' => $user_id]);
$stats = $stats_stmt->fetch();

// Ensure null values are zero
$stats['total_reports'] = $stats['total_reports'] ?? 0;
$stats['lost_items'] = $stats['lost_items'] ?? 0;
$stats['found_items'] = $stats['found_items'] ?? 0;
$stats['returned_items'] = $stats['returned_items'] ?? 0;

// Fetch 3 most recent reports for this user
$query = "
    SELECT i.id, i.user_id, i.title, i.type, i.location, i.item_date, i.image, i.status, 
           c.name as category_name, d.name as department_name, p.name as program_name
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN departments d ON i.department_id = d.id
    LEFT JOIN programs p ON i.program_id = p.id
    WHERE i.user_id = :user_id
    ORDER BY i.created_at DESC
    LIMIT 3
";
$recents_stmt = $pdo->prepare($query);
$recents_stmt->execute(['user_id' => $user_id]);
$recent_reports = $recents_stmt->fetchAll();

?>
<?php include 'includes/header.php'; ?>

<section class="hero" style="padding: 2rem 0; text-align: left;">
    <div class="container">
        <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
        <p style="margin-bottom: 0;">Manage your reports and find your lost items securely.</p>
    </div>
</section>

<section class="container" style="padding: 3rem 1.5rem;">
    <!-- Quick Actions -->
    <div style="margin-bottom: 3rem;">
        <h2 style="margin-bottom: 1.5rem;">Quick Actions</h2>
        <div class="stats-grid">
            <a href="report-lost.php" class="item-card text-center" style="padding: 2rem 1rem; display: block; border-left: 4px solid var(--danger);">
                <h3 style="color: var(--danger); font-size: 1.25rem;">Report Lost Item</h3>
                <p class="text-secondary" style="font-size: 0.9rem;">I lost something on campus</p>
            </a>
            <a href="report-found.php" class="item-card text-center" style="padding: 2rem 1rem; display: block; border-left: 4px solid var(--success);">
                <h3 style="color: var(--success); font-size: 1.25rem;">Report Found Item</h3>
                <p class="text-secondary" style="font-size: 0.9rem;">I found someone else's item</p>
            </a>
            <a href="items.php" class="item-card text-center" style="padding: 2rem 1rem; display: block; border-left: 4px solid var(--primary-blue);">
                <h3 style="color: var(--primary-blue); font-size: 1.25rem;">Browse Items</h3>
                <p class="text-secondary" style="font-size: 0.9rem;">Search the UniFind database</p>
            </a>
            <a href="my-reports.php" class="item-card text-center" style="padding: 2rem 1rem; display: block; border-left: 4px solid var(--cyan);">
                <h3 style="color: var(--cyan); font-size: 1.25rem;">My Reports</h3>
                <p class="text-secondary" style="font-size: 0.9rem;">View your active reports</p>
            </a>
        </div>
    </div>

    <!-- Overview Stats -->
    <div style="margin-bottom: 3rem;">
        <h2 style="margin-bottom: 1.5rem;">Your Overview</h2>
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            <div class="stat-card" style="padding: 1.5rem;">
                <h3 style="font-size: 2rem;"><?php echo $stats['total_reports']; ?></h3>
                <p class="text-secondary">My Reports</p>
            </div>
            <div class="stat-card" style="padding: 1.5rem;">
                <h3 style="font-size: 2rem; color: var(--danger);"><?php echo $stats['lost_items']; ?></h3>
                <p class="text-secondary">Lost Items</p>
            </div>
            <div class="stat-card" style="padding: 1.5rem;">
                <h3 style="font-size: 2rem; color: var(--success);"><?php echo $stats['found_items']; ?></h3>
                <p class="text-secondary">Found Items</p>
            </div>
            <div class="stat-card" style="padding: 1.5rem;">
                <h3 style="font-size: 2rem; color: var(--warning);"><?php echo $stats['returned_items']; ?></h3>
                <p class="text-secondary">Returned Items</p>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <?php if (count($recent_reports) > 0): ?>
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Recent Reports</h2>
            <a href="my-reports.php" style="color: var(--primary-blue); font-weight: 600;">View All →</a>
        </div>
        <div class="items-grid">
            <?php 
            foreach ($recent_reports as $item) {
                include 'includes/report_card.php';
            }
            ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
