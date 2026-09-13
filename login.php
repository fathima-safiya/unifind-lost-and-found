<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect to dashboard if already logged in
requireGuest();

$error = '';
if (isset($_GET['error']) && $_GET['error'] === 'login_required') {
    $error = "You must log in to access that page.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Fetch user from database
        $stmt = $pdo->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Verify credentials
        if ($user && password_verify($password, $user['password'])) {
            // Create secure session
            session_regenerate_id(true); // Prevent Session Fixation attacks
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect to appropriate dashboard based on role
            if ($user['role'] === 'admin') {
                header("Location: $base_url/admin.php");
            } else {
                header("Location: $base_url/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="container" style="max-width: 500px; padding: 4rem 1.5rem;">
    <div class="item-card" style="padding: 2rem;">
        <h2 class="text-center" style="margin-bottom: 2rem;">Log In to UniFind</h2>
        


        <form method="POST" action="login.php" autocomplete="off">
            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                <input type="email" id="email" name="email" required class="form-control" placeholder="Enter your email" autocomplete="off">
            </div>
            
            <div style="margin-bottom: 2rem;">
                <label for="password" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" required class="form-control" autocomplete="new-password" style="padding-right: 2.5rem;">
                    <span onclick="togglePassword('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--secondary-text);">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Log In</button>
        </form>
        
        <p class="text-center text-secondary" style="margin-top: 1.5rem;">
            Don't have an account? <a href="register.php">Register here</a>
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
