<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Ensure user is logged in
requireLogin();

$error = '';
$success = '';

// Fetch categories for the dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Fetch departments
$stmt = $pdo->query("SELECT id, name, code FROM departments ORDER BY name ASC");
$departments = $stmt->fetchAll();

// Fetch programs
$stmt = $pdo->query("SELECT id, department_id, name, code FROM programs ORDER BY name ASC");
$programs = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $department_id = $_POST['department_id'] ?? '';
    $program_id = $_POST['program_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date_lost = $_POST['date_lost'] ?? '';
    $time_lost = $_POST['time_lost'] ?? ''; // Currently unused in DB, could be appended to date or description
    $additional_info = trim($_POST['additional_info'] ?? '');
    
    // Combine date and time (although item_date is just DATE in DB, we'll store date. Time can be added to description)
    $item_date = $date_lost;
    if (!empty($time_lost)) {
        $description .= "\n\nTime Lost: " . $time_lost;
    }
    if (!empty($additional_info)) {
        $description .= "\n\nAdditional Info: " . $additional_info;
    }

    if (empty($title) || empty($category_id) || empty($department_id) || empty($program_id) || empty($description) || empty($location) || empty($date_lost)) {
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
            // Handle image upload
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_name = $_FILES['image']['name'];
                $file_size = $_FILES['image']['size'];
                
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                $file_mime = mime_content_type($file_tmp);
                $is_real_image = getimagesize($file_tmp); // Deeper check to prevent malicious file uploads
                
                if (!in_array($file_mime, $allowed_types) || $is_real_image === false) {
                    $error = "Invalid file type. Only valid JPG, PNG, and WEBP images are allowed.";
                } elseif ($file_size > 5 * 1024 * 1024) { // 5MB max
                    $error = "Image size must be less than 5MB.";
                } else {
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    // Generate safe unique filename
                    $new_filename = uniqid('lost_', true) . '.' . $ext;
                    $upload_dir = __DIR__ . '/uploads/';
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $destination = $upload_dir . $new_filename;
                    if (move_uploaded_file($file_tmp, $destination)) {
                        $image_path = 'uploads/' . $new_filename;
                    } else {
                        $error = "Failed to upload image.";
                    }
                }
            }

            // Insert into database if no errors
            if (empty($error)) {
                $stmt = $pdo->prepare("
                    INSERT INTO items (user_id, category_id, department_id, program_id, type, title, description, location, item_date, image, status) 
                    VALUES (:user_id, :category_id, :department_id, :program_id, 'lost', :title, :description, :location, :item_date, :image, 'pending')
                ");
                
                $inserted = $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'category_id' => $category_id,
                    'department_id' => $department_id,
                    'program_id' => $program_id,
                    'title' => $title,
                    'description' => $description,
                    'location' => $location,
                    'item_date' => $item_date,
                    'image' => $image_path
                ]);
                
                if ($inserted) {
                    $success = "Your lost item report has been submitted successfully and is pending approval.";
                    // Clear POST data so it doesn't repopulate the form
                    $_POST = array();
                } else {
                    $error = "Failed to submit report. Please try again.";
                }
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="container" style="max-width: 800px; padding: 3rem 1.5rem;">
    <div style="margin-bottom: 2rem; margin-left: -0.5rem;">
        <a href="dashboard.php" style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%; background-color: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--white); text-decoration: none; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.15)'; this.style.transform='translateX(-4px)'" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.05)'; this.style.transform='translateX(0)'" title="Go Back">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </a>
    </div>
    <div class="item-card" style="padding: 2.5rem; border-top: 4px solid var(--danger);">
        <h2 style="margin-bottom: 0.5rem; color: var(--danger);">Report a Lost Item</h2>
        <p class="text-secondary" style="margin-bottom: 2rem;">Please provide detailed information about the item you lost on campus. The more details you provide, the easier it will be to identify it.</p>
        
        <?php if ($success): ?>
            <div class="text-center" style="margin-top: 2rem;">
                <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
                <a href="report-lost.php" class="btn btn-outline" style="margin-left: 1rem;">Report Another Item</a>
            </div>
        <?php else: ?>
        
        <form method="POST" action="report-lost.php" enctype="multipart/form-data">
            <div style="margin-bottom: 1.5rem;">
                <label for="title" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Item Name *</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" placeholder="e.g., Blue Hydro Flask" class="form-control">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="department_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Department *</label>
                    <select id="department_id" name="department_id" required class="form-control" style="background-color: rgba(0,0,0,0.2); color: var(--white); border: 1px solid rgba(255,255,255,0.1);">
                        <option value="" style="background-color: var(--dark-navy);">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" style="background-color: var(--dark-navy);" <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
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
                        <option value="" style="background-color: var(--dark-navy);">-- Select a Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" style="background-color: var(--dark-navy);" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="location" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Last Seen Location *</label>
                    <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" placeholder="e.g., Main Library, 2nd Floor" class="form-control">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="date_lost" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Date Lost *</label>
                    <input type="date" id="date_lost" name="date_lost" required value="<?php echo htmlspecialchars($_POST['date_lost'] ?? ''); ?>" class="form-control">
                </div>
                <div>
                    <label for="time_lost" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Time Lost (Approximate)</label>
                    <input type="time" id="time_lost" name="time_lost" value="<?php echo htmlspecialchars($_POST['time_lost'] ?? ''); ?>" class="form-control">
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="description" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Detailed Description *</label>
                <textarea id="description" name="description" rows="4" required placeholder="Provide distinguishing features, colors, brands, etc." class="form-control" style="resize: vertical;"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="image" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Upload Image (Optional)</label>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/webp" class="form-control">
                <small class="text-secondary" style="display: block; margin-top: 0.25rem;">Allowed formats: JPG, PNG, WEBP. Max size: 5MB.</small>
            </div>

            <div style="margin-bottom: 2.5rem;">
                <label for="additional_info" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Additional Information (Optional)</label>
                <input type="text" id="additional_info" name="additional_info" value="<?php echo htmlspecialchars($_POST['additional_info'] ?? ''); ?>" placeholder="Any other helpful details" class="form-control">
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; background-color: var(--danger); border-color: var(--danger);">Submit Lost Report</button>
                <a href="dashboard.php" style="flex: 1; text-align: center; display: flex; align-items: center; justify-content: center; background-color: var(--surface-dim); border: 1px solid var(--border); color: var(--white); text-decoration: none; border-radius: 8px; padding: 12px; font-weight: 600;">Cancel</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</section>

<script>
    const programsData = <?php echo json_encode($programs); ?>;
    const selectedProgramId = "<?php echo htmlspecialchars($_POST['program_id'] ?? ''); ?>";
    
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

    // Trigger change event on page load to restore selected program if validation failed
    if (document.getElementById('department_id').value) {
        document.getElementById('department_id').dispatchEvent(new Event('change'));
    }
</script>
<?php include 'includes/footer.php'; ?>
