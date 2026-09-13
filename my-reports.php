<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle success message from other pages (e.g., report-found, report-lost)
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'found') {
        $success = "Your found item report was submitted successfully.";
    } elseif ($_GET['success'] === 'lost') {
        $success = "Your lost item report was submitted successfully.";
    } elseif ($_GET['success'] === 'deleted') {
        $success = "Report deleted successfully.";
    } elseif ($_GET['success'] === 'updated') {
        $success = "Report updated successfully.";
    }
}

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    
    // Verify ownership before deleting
    $check_stmt = $pdo->prepare("SELECT id, image FROM items WHERE id = :id AND user_id = :user_id");
    $check_stmt->execute(['id' => $item_id, 'user_id' => $user_id]);
    $item = $check_stmt->fetch();
    
    if ($item) {
        // Delete the image file if it exists
        if (!empty($item['image']) && file_exists(__DIR__ . '/' . $item['image'])) {
            unlink(__DIR__ . '/' . $item['image']);
        }
        
        $del_stmt = $pdo->prepare("DELETE FROM items WHERE id = :id AND user_id = :user_id");
        if ($del_stmt->execute(['id' => $item_id, 'user_id' => $user_id])) {
            header("Location: my-reports.php?success=deleted");
            exit();
        } else {
            $error = "Failed to delete the report.";
        }
    } else {
        $error = "You do not have permission to delete this report, or it does not exist.";
    }
}

// Fetch user's reports
$query = "
    SELECT i.id, i.user_id, i.title, i.type, i.location, i.item_date, i.image, i.status, 
           c.name as category_name, d.name as department_name, p.name as program_name
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN departments d ON i.department_id = d.id
    LEFT JOIN programs p ON i.program_id = p.id
    WHERE i.user_id = :user_id
    ORDER BY i.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$all_reports = $stmt->fetchAll();

$lost_reports = array_filter($all_reports, function($r) { return $r['type'] === 'lost'; });
$found_reports = array_filter($all_reports, function($r) { return $r['type'] === 'found'; });

?>
<?php include 'includes/header.php'; ?>

<section class="container" style="padding: 3rem 1.5rem;">
    <h1 style="margin-bottom: 2rem;">My Reports</h1>

    <!-- Lost Reports Section -->
    <h2 style="margin-bottom: 1.5rem; color: var(--danger); border-bottom: 2px solid var(--border); padding-bottom: 0.5rem;">Lost Items I Reported</h2>
    
    <?php if (empty($lost_reports)): ?>
        <div class="item-card text-center" style="padding: 2rem; margin-bottom: 3rem;">
            <p class="text-secondary">You haven't reported any lost items yet.</p>
            <a href="report-lost.php" class="btn btn-primary" style="margin-top: 1rem;">Report a Lost Item</a>
        </div>
    <?php else: ?>
        <div class="items-grid" style="margin-bottom: 4rem;">
            <?php foreach ($lost_reports as $item): ?>
                <?php include 'includes/report_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Found Reports Section -->
    <h2 style="margin-bottom: 1.5rem; color: var(--success); border-bottom: 2px solid var(--border); padding-bottom: 0.5rem;">Found Items I Reported</h2>
    
    <?php if (empty($found_reports)): ?>
        <div class="item-card text-center" style="padding: 2rem; margin-bottom: 3rem;">
            <p class="text-secondary">You haven't reported any found items yet.</p>
            <a href="report-found.php" class="btn btn-primary" style="background-color: var(--success); margin-top: 1rem;">Report a Found Item</a>
        </div>
    <?php else: ?>
        <div class="items-grid" style="margin-bottom: 2rem;">
            <?php foreach ($found_reports as $item): ?>
                <?php include 'includes/report_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>
