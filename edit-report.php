<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$redirect_url = $is_admin ? 'admin/items.php' : 'my-reports.php';

$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Fetch the existing item to edit, verifying ownership (or allow if admin)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id");
    $stmt->execute(['id' => $item_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $item_id, 'user_id' => $user_id]);
}
$item = $stmt->fetch();

if (!$item) {
    // If the item doesn't exist or isn't owned by the user, redirect them away
    header("Location: $redirect_url");
    exit();
}

// Fetch categories
$cat_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

// Fetch departments
$dept_stmt = $pdo->query("SELECT id, name, code FROM departments ORDER BY name ASC");
$departments = $dept_stmt->fetchAll();

// Fetch programs
$prog_stmt = $pdo->query("SELECT id, department_id, name, code FROM programs ORDER BY name ASC");
$programs = $prog_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $department_id = $_POST['department_id'] ?? '';
    $program_id = $_POST['program_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? $item['status']; // Allow changing status (e.g., pending -> active -> returned)

    if (empty($title) || empty($category_id) || empty($department_id) || empty($program_id) || empty($description) || empty($location)) {
        $error = "Please fill in all required fields.";
    } else {
        // Validate category
        $valid_category = false;
        foreach ($categories as $cat) {
            if ($cat['id'] == $category_id) {
                $valid_category = true;
                break;
            }
        }
        
        if (!$valid_category) {
            $error = "Invalid category selected.";
        } else {
            // Handle image upload (Optional on edit)
            $image_path = $item['image']; // Keep old image by default
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_name = $_FILES['image']['name'];
                $file_size = $_FILES['image']['size'];
                
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                $file_mime = mime_content_type($file_tmp);
                $is_real_image = getimagesize($file_tmp); // Deeper check to prevent malicious file uploads
                
                if (!in_array($file_mime, $allowed_types) || $is_real_image === false) {
                    $error = "Invalid file type. Only valid JPG, PNG, and WEBP images are allowed.";
                } elseif ($file_size > 5 * 1024 * 1024) {
                    $error = "Image size must be less than 5MB.";
                } else {
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $new_filename = uniqid('edit_', true) . '.' . $ext;
                    $upload_dir = __DIR__ . '/uploads/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    
                    if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                        // Delete old image if it exists
                        if (!empty($item['image']) && file_exists(__DIR__ . '/' . $item['image'])) {
                            unlink(__DIR__ . '/' . $item['image']);
                        }
                        $image_path = 'uploads/' . $new_filename;
                    } else {
                        $error = "Failed to upload new image.";
                    }
                }
            }

            if (empty($error)) {
                if ($is_admin) {
                    $upd_stmt = $pdo->prepare("
                        UPDATE items 
                        SET category_id = :category_id, department_id = :department_id, program_id = :program_id, title = :title, description = :description, 
                            location = :location, image = :image, status = :status, updated_at = NOW()
                        WHERE id = :id
                    ");
                    $updated = $upd_stmt->execute([
                        'category_id' => $category_id,
                        'department_id' => $department_id,
                        'program_id' => $program_id,
                        'title' => $title,
                        'description' => $description,
                        'location' => $location,
                        'image' => $image_path,
                        'status' => $status,
                        'id' => $item_id
                    ]);
                } else {
                    $upd_stmt = $pdo->prepare("
                        UPDATE items 
                        SET category_id = :category_id, department_id = :department_id, program_id = :program_id, title = :title, description = :description, 
                            location = :location, image = :image, status = :status, updated_at = NOW()
                        WHERE id = :id AND user_id = :user_id
                    ");
                    $updated = $upd_stmt->execute([
                        'category_id' => $category_id,
                        'department_id' => $department_id,
                        'program_id' => $program_id,
                        'title' => $title,
                        'description' => $description,
                        'location' => $location,
                        'image' => $image_path,
                        'status' => $status,
                        'id' => $item_id,
                        'user_id' => $user_id
                    ]);
                }
                
                if ($updated) {
                    // Redirect back to dashboard
                    header("Location: $redirect_url?success=updated");
                    exit();
                } else {
                    $error = "Failed to update report.";
                }
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="container" style="max-width: 800px; padding: 3rem 1.5rem;">
    <div class="item-card" style="padding: 2.5rem; border-top: 4px solid <?php echo $item['type'] === 'lost' ? 'var(--danger)' : 'var(--success)'; ?>;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="margin: 0;">Edit <?php echo ucfirst($item['type']); ?> Report</h2>
            <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="btn btn-outline" style="padding: 0.5rem 1rem;">Back</a>
        </div>
        
        <form method="POST" action="edit-report.php?id=<?php echo $item_id; ?>" enctype="multipart/form-data">
            <div style="margin-bottom: 1.5rem;">
                <label for="title" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Item Name *</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? $item['title']); ?>" class="form-control">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="department_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Department *</label>
                    <select id="department_id" name="department_id" required class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <option value="" style="background-color: var(--dark-navy);">-- Select Department --</option>
                        <?php 
                        $current_dept = $_POST['department_id'] ?? $item['department_id'];
                        foreach ($departments as $dept): 
                        ?>
                            <option value="<?php echo $dept['id']; ?>" style="background-color: var(--dark-navy);" <?php echo ($current_dept == $dept['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="program_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Program *</label>
                    <select id="program_id" name="program_id" required class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <option value="" style="background-color: var(--dark-navy);">-- Select Program --</option>
                        <!-- Options populated via JS -->
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="category_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Category *</label>
                    <select id="category_id" name="category_id" required class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <?php 
                        $current_cat = $_POST['category_id'] ?? $item['category_id'];
                        foreach ($categories as $cat): 
                        ?>
                            <option value="<?php echo $cat['id']; ?>" style="background-color: var(--dark-navy);" <?php echo ($current_cat == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="location" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Location *</label>
                    <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($_POST['location'] ?? $item['location']); ?>" class="form-control">
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="description" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Detailed Description *</label>
                <textarea id="description" name="description" rows="5" required class="form-control" style="resize: vertical;"><?php echo htmlspecialchars($_POST['description'] ?? $item['description']); ?></textarea>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Current Image</label>
                <?php if (!empty($item['image'])): ?>
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Current Image" style="max-width: 200px; border-radius: var(--radius-sm); margin-bottom: 0.5rem; display: block;">
                <?php else: ?>
                    <p class="text-secondary" style="margin-bottom: 0.5rem;">No image uploaded currently.</p>
                <?php endif; ?>
                
                <label for="image" style="display: block; font-weight: 600; margin-bottom: 0.5rem; margin-top: 1rem;">Replace Image (Optional)</label>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/webp" class="form-control">
            </div>

            <div style="margin-bottom: 2.5rem;">
                <label for="status" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Update Status</label>
                <select id="status" name="status" class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                    <?php 
                    $current_status = $_POST['status'] ?? $item['status'];
                    $statuses = ['pending', 'active', 'returned'];
                    foreach ($statuses as $stat): 
                    ?>
                        <option value="<?php echo $stat; ?>" style="background-color: var(--dark-navy);" <?php echo ($current_status === $stat) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($stat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-secondary">If the item has been recovered, please mark it as "Returned".</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; background-color: var(--primary-blue); border-color: var(--primary-blue);">Save Changes</button>
        </form>
    </div>
</section>

<script>
    const programsData = <?php echo json_encode($programs); ?>;
    const selectedProgramId = "<?php echo htmlspecialchars($_POST['program_id'] ?? $item['program_id']); ?>";
    
    document.getElementById('department_id').addEventListener('change', function() {
        const deptId = this.value;
        const programSelect = document.getElementById('program_id');
        
        // Clear current options
        programSelect.innerHTML = '<option value="" style="background-color: var(--dark-navy);">-- Select Program --</option>';
        
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

    // Trigger change event on page load to restore selected program
    if (document.getElementById('department_id').value) {
        document.getElementById('department_id').dispatchEvent(new Event('change'));
    }
</script>

<?php include 'includes/footer.php'; ?>
