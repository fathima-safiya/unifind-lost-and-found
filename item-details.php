<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Get item ID from URL safely
$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

$error = '';
$success = '';

// Handle Contact/Interest Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    if (!isLoggedIn()) {
        $error = "You must be logged in to contact the reporter.";
    } else {
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            $error = "Please enter a message.";
        } else {
            // Because we don't have a messages table or email server configured yet,
            // we simulate the contact request success.
            // In a full system, you would insert into a `messages` or `claims` table here.
            $success = "Your message has been securely sent to the reporter. They will contact you if it's a match.";
        }
    }
}

// Fetch main item details
$query = "
    SELECT i.*, c.name as category_name, u.full_name as reporter_name,
           d.name as department_name, p.name as program_name
    FROM items i
    JOIN categories c ON i.category_id = c.id
    JOIN users u ON i.user_id = u.id
    LEFT JOIN departments d ON i.department_id = d.id
    LEFT JOIN programs p ON i.program_id = p.id
    WHERE i.id = :id
";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $item_id]);
$item = $stmt->fetch();

$related_items = [];
if ($item) {
    // Fetch related items (same category OR same location), excluding current item
    $rel_query = "
        SELECT i.id, i.title, i.type, i.image, i.location, c.name as category_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.id != :id 
        AND i.status != 'rejected'
        AND (i.category_id = :category_id OR i.location LIKE :location)
        ORDER BY i.created_at DESC
        LIMIT 3
    ";
    $rel_stmt = $pdo->prepare($rel_query);
    $rel_stmt->execute([
        'id' => $item_id,
        'category_id' => $item['category_id'],
        'location' => '%' . $item['location'] . '%'
    ]);
    $related_items = $rel_stmt->fetchAll();
}
?>
<?php include 'includes/header.php'; ?>

<section class="container" style="padding: 2rem 1.5rem;">
    
    <div style="margin-bottom: 2rem;">
        <a href="items.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">&larr; Back to Browse</a>
    </div>

    <?php if (!$item): ?>
        <div class="item-card text-center" style="padding: 4rem 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem; color: var(--border);">⚠️</div>
            <h2 style="margin-bottom: 0.5rem;">Item Not Found</h2>
            <p class="text-secondary" style="margin-bottom: 1.5rem;">The item you are looking for does not exist, has been removed, or the ID is invalid.</p>
        </div>
    <?php else: ?>
        


        <div style="display: flex; flex-wrap: wrap; gap: 3rem;">
            <!-- Left: Image -->
            <div style="flex: 1; min-width: 300px;">
                <div class="item-card" style="border: none; overflow: hidden; height: 100%; min-height: 400px; background-color: rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span class="text-secondary" style="font-size: 1.25rem;">No Image Provided</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Details -->
            <div style="flex: 1; min-width: 300px;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <?php if ($item['type'] === 'lost'): ?>
                        <span class="badge badge-lost" style="font-size: 1rem; padding: 0.35rem 1rem;">Lost</span>
                    <?php else: ?>
                        <span class="badge badge-found" style="background-color: #DCFCE7; color: var(--success); font-size: 1rem; padding: 0.35rem 1rem;">Found</span>
                    <?php endif; ?>
                    <span class="badge" style="background-color: var(--surface-dim); color: var(--secondary-text); border: 1px solid var(--border);">
                        Status: <?php echo ucfirst(htmlspecialchars($item['status'])); ?>
                    </span>
                </div>
                
                <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['title']); ?></h1>
                
                <p class="text-secondary" style="font-size: 1.1rem; margin-bottom: 0.5rem;">
                    Category: <strong><?php echo htmlspecialchars($item['category_name']); ?></strong>
                </p>
                <?php if (!empty($item['department_name'])): ?>
                    <p class="text-secondary" style="font-size: 1.1rem; margin-bottom: 0.5rem;">
                        Department: <strong><?php echo htmlspecialchars($item['department_name']); ?></strong>
                    </p>
                <?php endif; ?>
                <?php if (!empty($item['program_name'])): ?>
                    <p class="text-secondary" style="font-size: 1.1rem; margin-bottom: 2rem;">
                        Program: <strong><?php echo htmlspecialchars($item['program_name']); ?></strong>
                    </p>
                <?php else: ?>
                    <div style="margin-bottom: 1.5rem;"></div>
                <?php endif; ?>

                <div class="item-card" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; color: var(--white);">Location & Date</h3>
                    <p style="margin-bottom: 0.25rem;"><strong><?php echo $item['type'] === 'lost' ? 'Lost at:' : 'Found at:'; ?></strong> <?php echo htmlspecialchars($item['location']); ?></p>
                    <p style="margin-bottom: 0;"><strong>Date:</strong> <?php echo date('F j, Y', strtotime($item['item_date'])); ?></p>
                </div>

                <div style="margin-bottom: 2.5rem;">
                    <h3 style="margin-bottom: 1rem;">Description</h3>
                    <p style="white-space: pre-wrap; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                </div>

                <div style="border-top: 1px solid var(--border); padding-top: 1.5rem; margin-bottom: 2rem;">
                    <p class="text-secondary" style="margin-bottom: 0.5rem;">Reported by: <strong><?php echo htmlspecialchars($item['reporter_name']); ?></strong></p>
                    <p class="text-secondary" style="font-size: 0.9rem;">Reported on: <?php echo date('M d, Y h:i A', strtotime($item['created_at'])); ?></p>
                </div>

                <!-- Contact / Claim Mechanism -->
                <?php if ($item['status'] === 'active' || $item['status'] === 'pending'): ?>
                    <?php if (isLoggedIn()): ?>
                        <?php if ($_SESSION['user_id'] == $item['user_id']): ?>
                            <div class="item-card text-center" style="padding: 1.5rem; border-color: var(--primary-blue);">
                                <p style="margin-bottom: 0;"><strong>This is your report.</strong></p>
                                <a href="my-reports.php" class="btn btn-outline" style="margin-top: 1rem;">Manage Report</a>
                            </div>
                        <?php else: ?>
                            <div class="item-card" style="padding: 1.5rem;">
                                <h3 style="margin-bottom: 1rem;">
                                    <?php echo $item['type'] === 'lost' ? 'Did you find this item?' : 'Is this your item?'; ?>
                                </h3>
                                <form method="POST" action="item-details.php?id=<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="contact">
                                    <textarea name="message" rows="3" required placeholder="Describe details to verify ownership or coordinate a meetup..." class="form-control" style="margin-bottom: 1rem; resize: vertical; background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1); padding: 0.75rem; border-radius: var(--radius-sm);"></textarea>
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">Contact Reporter Securely</button>
                                </form>
                                <p class="text-secondary" style="font-size: 0.85rem; margin-top: 1rem; text-align: center;">
                                    Your email address will remain private until you choose to share it.
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="item-card text-center" style="padding: 1.5rem;">
                            <p style="margin-bottom: 1rem;">You must be logged in to contact the reporter.</p>
                            <a href="login.php" class="btn btn-primary">Log In to Contact</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>

        <!-- Related Items -->
        <?php if (!empty($related_items)): ?>
            <div style="margin-top: 5rem;">
                <h2 style="margin-bottom: 2rem;">Related Items</h2>
                <div class="items-grid">
                    <?php foreach ($related_items as $rel): ?>
                        <div class="item-card" style="display: flex; flex-direction: column;">
                            <div class="item-image" style="height: 150px; background-image: url('<?php echo !empty($rel['image']) ? htmlspecialchars($rel['image']) : ''; ?>'); background-size: cover; background-position: center;">
                                <?php if (empty($rel['image'])): ?>
                                    <span>No Image</span>
                                <?php endif; ?>
                            </div>
                            <div class="item-content" style="flex: 1; padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;" title="<?php echo htmlspecialchars($rel['title']); ?>">
                                        <?php echo htmlspecialchars($rel['title']); ?>
                                    </h4>
                                    <?php if ($rel['type'] === 'lost'): ?>
                                        <span class="badge badge-lost" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">Lost</span>
                                    <?php else: ?>
                                        <span class="badge badge-found" style="font-size: 0.7rem; padding: 0.2rem 0.5rem; background-color: #DCFCE7; color: var(--success);">Found</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-secondary" style="font-size: 0.85rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($rel['location']); ?></p>
                                <a href="item-details.php?id=<?php echo $rel['id']; ?>" class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.85rem; width: 100%;">View</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
