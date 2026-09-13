<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Fetch categories for filter dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Fetch departments for filter dropdown
$stmt = $pdo->query("SELECT id, name, code FROM departments ORDER BY name ASC");
$departments = $stmt->fetchAll();

// Fetch programs for filter dropdown
$stmt = $pdo->query("SELECT id, department_id, name, code FROM programs ORDER BY name ASC");
$programs = $stmt->fetchAll();

// Pagination setup
$items_per_page = 9;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $items_per_page;

// Build query dynamically based on filters
$where_clauses = [];
$params = [];

// 1. Status Filter (Default to active/pending if not specified)
// Note: Depending on rules, we might only show 'active' items, but let's allow filtering by status.
// If no status is selected, we might want to hide 'rejected' or 'returned' items by default to keep it clean.
if (!empty($_GET['status'])) {
    $where_clauses[] = "i.status = :status";
    $params['status'] = $_GET['status'];
} else {
    // Default: don't show rejected items
    $where_clauses[] = "i.status != 'rejected'";
}

// 2. Search Filter (Title or Description)
if (!empty($_GET['search'])) {
    $where_clauses[] = "(i.title LIKE :search OR i.description LIKE :search)";
    $params['search'] = '%' . $_GET['search'] . '%';
}

// 3. Type Filter (Lost or Found)
if (!empty($_GET['type'])) {
    $where_clauses[] = "i.type = :type";
    $params['type'] = $_GET['type'];
}

// 4. Category Filter
if (!empty($_GET['category_id'])) {
    $where_clauses[] = "i.category_id = :category_id";
    $params['category_id'] = $_GET['category_id'];
}

// 5. Location Filter
if (!empty($_GET['location'])) {
    $where_clauses[] = "i.location LIKE :location";
    $params['location'] = '%' . $_GET['location'] . '%';
}

// 6. Date Filter
if (!empty($_GET['date'])) {
    $where_clauses[] = "i.item_date = :date";
    $params['date'] = $_GET['date'];
}

// 7. Department Filter
if (!empty($_GET['department_id'])) {
    $where_clauses[] = "i.department_id = :department_id";
    $params['department_id'] = $_GET['department_id'];
}

// 8. Program Filter
if (!empty($_GET['program_id'])) {
    $where_clauses[] = "i.program_id = :program_id";
    $params['program_id'] = $_GET['program_id'];
}

// Construct WHERE string
$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM items i $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Fetch items
$query = "
    SELECT i.id, i.user_id, i.title, i.type, i.location, i.item_date, i.image, i.status, c.name as category_name
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    $where_sql
    ORDER BY i.created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($query);
// Bind params manually because LIMIT/OFFSET need integers, and execute() casts everything to strings
foreach ($params as $key => $val) {
    $stmt->bindValue(":$key", $val);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

?>
<?php include 'includes/header.php'; ?>

<section class="layout-container">
    
    <!-- Sidebar Filters -->
    <aside class="layout-sidebar">
        <div class="item-card" style="padding: 1.5rem; position: sticky; top: 100px;">
            <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Filters</h3>
            
            <form method="GET" action="items.php">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Keywords..." class="form-control">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">Type</label>
                    <select name="type" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <option value="" style="background-color: var(--dark-navy);">All</option>
                        <option value="lost" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['type']) && $_GET['type'] == 'lost') ? 'selected' : ''; ?>>Lost Items</option>
                        <option value="found" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['type']) && $_GET['type'] == 'found') ? 'selected' : ''; ?>>Found Items</option>
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">Department</label>
                    <select id="filter_dept" name="department_id" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <option value="" style="background-color: var(--dark-navy);">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['department_id']) && $_GET['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">Program</label>
                    <select id="filter_prog" name="program_id" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <option value="" style="background-color: var(--dark-navy);">All Programs</option>
                        <!-- Populated by JS -->
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">Category</label>
                    <select name="category_id" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <option value="" style="background-color: var(--dark-navy);">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>" placeholder="e.g., Library" class="form-control">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">Date</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>" class="form-control">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem;">Status</label>
                    <select name="status" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <option value="" style="background-color: var(--dark-navy);">Active & Pending</option>
                        <option value="pending" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="active" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="returned" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['status']) && $_GET['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.5rem;">Apply Filters</button>
                <a href="items.php" class="btn btn-outline" style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; display: block;">Clear Filters</a>
            </form>
        </div>
    </aside>

    <!-- Results Area -->
    <main class="layout-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Browse Items</h2>
            <p class="text-secondary"><?php echo $total_items; ?> result(s) found</p>
        </div>

        <?php if ($total_items > 0): ?>
            <div class="items-grid">
                <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <div class="item-image" style="background-image: url('<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>'); background-size: cover; background-position: center;">
                            <?php if (empty($item['image'])): ?>
                                <span>No Image Available</span>
                            <?php endif; ?>
                        </div>
                        <div class="item-content">
                            <div class="item-header">
                                <h3 class="item-title" style="font-size: 1.1rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 70%;" title="<?php echo htmlspecialchars($item['title']); ?>">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <?php if ($item['type'] === 'lost'): ?>
                                    <span class="badge badge-lost">Lost</span>
                                <?php else: ?>
                                    <span class="badge badge-found" style="background-color: #DCFCE7; color: var(--success);">Found</span>
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category_name']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                                <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($item['item_date'])); ?></p>
                                <p><strong>Status:</strong> <span style="text-transform: capitalize;"><?php echo htmlspecialchars($item['status']); ?></span></p>
                            </div>
                            <div class="item-actions">
                                <a href="item-details.php?id=<?php echo $item['id']; ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 3rem;">
                    <?php 
                    // Build base URL for pagination keeping current filters
                    $query_string = $_GET;
                    unset($query_string['page']);
                    $base_query = http_build_query($query_string);
                    $prefix = !empty($base_query) ? '&' : '';
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="items.php?<?php echo $base_query . $prefix; ?>page=<?php echo $page - 1; ?>" class="btn btn-outline" style="padding: 0.5rem 1rem;">&laquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="items.php?<?php echo $base_query . $prefix; ?>page=<?php echo $i; ?>" 
                           class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>" 
                           style="padding: 0.5rem 1rem;">
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="items.php?<?php echo $base_query . $prefix; ?>page=<?php echo $page + 1; ?>" class="btn btn-outline" style="padding: 0.5rem 1rem;">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="item-card text-center" style="padding: 4rem 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem; color: var(--border);">🔍</div>
                <h3 style="margin-bottom: 0.5rem;">No items found</h3>
                <p class="text-secondary" style="margin-bottom: 1.5rem;">We couldn't find any items matching your current filters.</p>
                <a href="items.php" class="btn btn-outline">Clear all filters</a>
            </div>
        <?php endif; ?>
    </main>

</section>

<script>
    const programsData = <?php echo json_encode($programs); ?>;
    const selectedProgramId = "<?php echo htmlspecialchars($_GET['program_id'] ?? ''); ?>";
    
    document.getElementById('filter_dept').addEventListener('change', function() {
        const deptId = this.value;
        const programSelect = document.getElementById('filter_prog');
        
        programSelect.innerHTML = '<option value="" style="background-color: var(--dark-navy);">All Programs</option>';
        
        if (deptId) {
            const filteredPrograms = programsData.filter(p => p.department_id == deptId);
            filteredPrograms.forEach(p => {
                const option = document.createElement('option');
                option.value = p.id;
                option.textContent = p.name;
                option.style.backgroundColor = 'var(--dark-navy)';
                if (p.id == selectedProgramId) {
                    option.selected = true;
                }
                programSelect.appendChild(option);
            });
        }
    });

    // Restore selected program on load
    if (document.getElementById('filter_dept').value) {
        document.getElementById('filter_dept').dispatchEvent(new Event('change'));
    }
</script>

<?php include 'includes/footer.php'; ?>
