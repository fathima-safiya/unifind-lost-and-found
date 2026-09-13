<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

$success = '';
$error = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'updated') {
        $success = "Item updated successfully.";
    }
}

// Handle Actions (Approve, Reject, Returned, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($id > 0 && !empty($action)) {
        if ($action === 'delete') {
            $stmt = $pdo->prepare("SELECT image FROM items WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $item = $stmt->fetch();
            if ($item && !empty($item['image']) && file_exists(__DIR__ . '/../' . $item['image'])) {
                unlink(__DIR__ . '/../' . $item['image']);
            }
            $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$id]);
            $success = "Item deleted successfully.";
        } else {
            $status = '';
            if ($action === 'approve') $status = 'active';
            if ($action === 'reject') $status = 'rejected';
            if ($action === 'returned') $status = 'returned';
            
            if (!empty($status)) {
                $pdo->prepare("UPDATE items SET status = ? WHERE id = ?")->execute([$status, $id]);
                $success = "Item status updated to $status.";
            }
        }
    }
}

// Fetch Items with search/filter
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "i.title LIKE :search";
    $params['search'] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['type'])) {
    $where[] = "i.type = :type";
    $params['type'] = $_GET['type'];
}
if (!empty($_GET['status'])) {
    $where[] = "i.status = :status";
    $params['status'] = $_GET['status'];
}
if (!empty($_GET['department_id'])) {
    $where[] = "i.department_id = :department_id";
    $params['department_id'] = $_GET['department_id'];
}
if (!empty($_GET['program_id'])) {
    $where[] = "i.program_id = :program_id";
    $params['program_id'] = $_GET['program_id'];
}

$where_sql = "";
if (count($where) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

// Fetch departments for filter dropdown
$stmt = $pdo->query("SELECT id, name, code FROM departments ORDER BY name ASC");
$departments = $stmt->fetchAll();

// Fetch programs for filter dropdown
$stmt = $pdo->query("SELECT id, department_id, name, code FROM programs ORDER BY name ASC");
$programs = $stmt->fetchAll();

$query = "
    SELECT i.*, c.name as category_name, u.full_name as reporter
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN users u ON i.user_id = u.id
    $where_sql
    ORDER BY i.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

?>
<?php include '../includes/header.php'; ?>

<div class="layout-container">
    
    <aside class="layout-sidebar">
        <div class="item-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-blue);">Admin Panel</h3>
            <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="dashboard.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Dashboard</a>
                <a href="items.php" class="btn btn-primary" style="text-align: left; background-color: var(--primary-blue);">Items</a>
                <a href="users.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Users</a>
                <a href="categories.php" class="btn btn-outline" style="text-align: left; border: none; justify-content: flex-start;">Categories</a>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 1rem 0;">
                <a href="../logout.php" class="btn btn-outline" style="text-align: left; border: none; color: var(--danger); justify-content: flex-start;">Logout</a>
            </nav>
        </div>
    </aside>

    <main class="layout-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Item Management</h1>
        </div>
        


        <div class="item-card" style="padding: 1.5rem; margin-bottom: 2rem;">
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search title..." class="form-control" style="flex: 1; min-width: 200px; background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                
                <select name="type" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                    <option value="" style="background-color: var(--dark-navy);">All Types</option>
                    <option value="lost" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['type']) && $_GET['type'] == 'lost') ? 'selected' : ''; ?>>Lost</option>
                    <option value="found" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['type']) && $_GET['type'] == 'found') ? 'selected' : ''; ?>>Found</option>
                </select>

                <select id="filter_dept" name="department_id" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                    <option value="" style="background-color: var(--dark-navy);">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['department_id']) && $_GET['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select id="filter_prog" name="program_id" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                    <option value="" style="background-color: var(--dark-navy);">All Programs</option>
                    <!-- Populated by JS -->
                </select>

                <select name="status" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                    <option value="" style="background-color: var(--dark-navy);">All Statuses</option>
                    <option value="pending" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="returned" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['status']) && $_GET['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                    <option value="rejected" style="background-color: var(--dark-navy);" <?php echo (isset($_GET['status']) && $_GET['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                </select>

                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="items.php" class="btn btn-outline">Clear</a>
            </form>
        </div>

        <div class="item-card table-responsive" style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 1rem 0.5rem;">Item</th>
                        <th style="padding: 1rem 0.5rem;">Type</th>
                        <th style="padding: 1rem 0.5rem;">Reporter</th>
                        <th style="padding: 1rem 0.5rem;">Status</th>
                        <th style="padding: 1rem 0.5rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1rem 0.5rem; font-weight: 500;">
                                <?php echo htmlspecialchars($i['title']); ?><br>
                                <span class="text-secondary" style="font-size: 0.8rem; font-weight: 400;"><?php echo htmlspecialchars($i['category_name']); ?></span>
                            </td>
                            <td style="padding: 1rem 0.5rem;">
                                <?php if ($i['type'] === 'lost'): ?>
                                    <span style="color: var(--danger); font-weight: 600; font-size: 0.85rem;">Lost</span>
                                <?php else: ?>
                                    <span style="color: var(--success); font-weight: 600; font-size: 0.85rem;">Found</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem 0.5rem; color: var(--secondary-text);"><?php echo htmlspecialchars($i['reporter']); ?></td>
                            <td style="padding: 1rem 0.5rem;">
                                <?php 
                                    $bg = 'var(--surface-dim)'; $col = 'var(--secondary-text)';
                                    if ($i['status'] == 'active') { $bg = '#DBEAFE'; $col = '#1E3A8A'; }
                                    if ($i['status'] == 'pending') { $bg = '#FEF3C7'; $col = '#92400E'; }
                                    if ($i['status'] == 'returned') { $bg = '#DCFCE7'; $col = '#166534'; }
                                    if ($i['status'] == 'rejected') { $bg = '#FEE2E2'; $col = '#991B1B'; }
                                ?>
                                <span style="background-color: <?php echo $bg; ?>; color: <?php echo $col; ?>; padding: 0.2rem 0.5rem; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">
                                    <?php echo ucfirst($i['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem 0.5rem; text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="../item-details.php?id=<?php echo $i['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">View</a>
                                    <a href="../edit-report.php?id=<?php echo $i['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: var(--primary-blue); border-color: var(--primary-blue);">Edit</a>
                                    
                                    <form method="POST" style="display:inline;" data-confirm="Execute this action?">
                                        <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                        <select name="action" onchange="this.form.submit()" style="padding: 0.25rem; font-size: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm);">
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

<?php include '../includes/footer.php'; ?>
