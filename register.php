<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect to dashboard if already logged in
requireGuest();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields except Student ID are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check for duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Check for duplicate student ID (if provided)
            $duplicate_student = false;
            if (!empty($student_id)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE student_id = :student_id");
                $stmt->execute(['student_id' => $student_id]);
                if ($stmt->rowCount() > 0) {
                    $error = "This Student ID is already registered.";
                    $duplicate_student = true;
                }
            }

            if (!$duplicate_student && empty($error)) {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Determine role (default to student, though if no student ID maybe staff? Keep simple for now: student)
                $role = 'student'; 
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, student_id, password, role) VALUES (:full_name, :email, :student_id, :password, :role)");
                $inserted = $stmt->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'student_id' => empty($student_id) ? null : $student_id,
                    'password' => $hashed_password,
                    'role' => $role
                ]);

                if ($inserted) {
                    $success = "Registration successful! You can now log in.";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="container" style="max-width: 600px; padding: 4rem 1.5rem;">
    <div class="item-card" style="padding: 2rem;">
        <h2 class="text-center" style="margin-bottom: 2rem;">Register for UniFind</h2>
        
        <?php if (!$success): ?>
        <form method="POST" action="register.php" autocomplete="off">
            <div style="margin-bottom: 1.5rem;">
                <label for="full_name" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Full Name</label>
                <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" class="form-control">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" class="form-control" autocomplete="off">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="student_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Student ID (Optional)</label>
                <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" class="form-control">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <label for="password" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Password</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" required class="form-control" autocomplete="new-password" style="padding-right: 2.5rem;">
                        <span onclick="togglePassword('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--secondary-text);">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                </div>
                <div>
                    <label for="confirm_password" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Confirm Password</label>
                    <div style="position: relative;">
                        <input type="password" id="confirm_password" name="confirm_password" required class="form-control" autocomplete="new-password" style="padding-right: 2.5rem;">
                        <span onclick="togglePassword('confirm_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--secondary-text);">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
        </form>
        <?php endif; ?>
        
        <p class="text-center text-secondary" style="margin-top: 1.5rem;">
            Already have an account? <a href="login.php">Log in here</a>
        </p>
    </div>
</section>

<script>
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        iconElement.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
    } else {
        input.type = 'password';
        iconElement.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
