<?php
require_once 'config/database.php';
$page_title = "Contact UniFind";

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif (strlen($message) > 1000) {
        $error = "Message must be less than 1000 characters.";
    } else {
        // Backend future feature placeholder
        $success = "Message successfully queued for sending. (Backend delivery not yet implemented).";
    }
}

include 'includes/header.php';
?>
<section class="container" style="max-width: 1000px; margin: 0 auto; padding: 2rem 1rem;">
    <div class="section-header text-center reveal-element" style="margin-bottom: 3rem;">
        <h2>Contact UniFind</h2>
        <p class="text-secondary">Need help with a report or have a question about the system?</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;" class="reveal-element">
        
        <div class="item-card" style="padding: 2rem; display: flex; flex-direction: column;">
            <h3 style="margin-bottom: 1rem; color: var(--primary-blue);">Get in Touch</h3>
            <p class="text-secondary" style="margin-bottom: 2rem; line-height: 1.6;">For questions about lost and found reports, account issues, or using the UniFind system, contact the UniFind support team.</p>
            
            <div style="margin-top: auto;">
                <div style="margin-bottom: 1.5rem;">
                    <strong style="color: var(--white); display: block; margin-bottom: 0.25rem;">University</strong>
                    <span class="text-secondary">SLIATE – Kurunegala</span>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <strong style="color: var(--white); display: block; margin-bottom: 0.25rem;">System</strong>
                    <span class="text-secondary">UniFind Lost & Found Management System</span>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <strong style="color: var(--white); display: block; margin-bottom: 0.25rem;">Support</strong>
                    <span class="text-secondary">UniFind System Administrator</span>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <strong style="color: var(--white); display: block; margin-bottom: 0.25rem;">Email</strong>
                    <a href="mailto:unifind.support@demo.example" style="color: var(--light-blue); text-decoration: none;">[unifind.support@demo.example]</a>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <strong style="color: var(--white); display: block; margin-bottom: 0.25rem;">Phone</strong>
                    <span class="text-secondary">+94 77 123 4567</span>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <strong style="color: var(--white); display: block; margin-bottom: 0.25rem;">Support Hours</strong>
                    <span class="text-secondary">Monday – Friday<br>8:30 AM – 4:30 PM</span>
                </div>
                <div>
                    <strong style="color: var(--white); display: block; margin-bottom: 0.25rem;">Location</strong>
                    <span class="text-secondary">SLIATE – Kurunegala Campus</span>
                </div>
            </div>
        </div>

        <div class="item-card" style="padding: 2rem;">
            <form method="POST" action="contact.php" id="contactForm">
                <div style="margin-bottom: 1.5rem;">
                    <label for="name" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Full Name</label>
                    <input type="text" id="name" name="name" required class="form-control" style="transition: border-color 0.3s ease;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                    <input type="email" id="email" name="email" required class="form-control" style="transition: border-color 0.3s ease;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="subject" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Subject</label>
                    <input type="text" id="subject" name="subject" required class="form-control" style="transition: border-color 0.3s ease;">
                </div>
                <div style="margin-bottom: 2rem;">
                    <label for="message" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Message</label>
                    <textarea id="message" name="message" required class="form-control" rows="5" maxlength="1000" style="transition: border-color 0.3s ease;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Send Message</button>
            </form>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('#contactForm .form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.style.borderColor = 'var(--primary-blue)';
        });
        input.addEventListener('blur', () => {
            input.style.borderColor = 'var(--border)';
        });
    });
});
</script>
<?php include 'includes/footer.php'; ?>
