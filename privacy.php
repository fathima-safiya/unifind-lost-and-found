<?php
require_once 'config/database.php';
$page_title = "Privacy Policy";
include 'includes/header.php';
?>
<section class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
    <div class="section-header text-center reveal-element" style="margin-bottom: 3rem;">
        <h2>Privacy Policy</h2>
        <p class="text-secondary">How UniFind handles information provided by users.</p>
    </div>

    <div class="item-card reveal-element" style="padding: 2rem;">
        <div class="text-secondary" style="line-height: 1.7;">
            <p style="margin-bottom: 2rem; font-style: italic;">Last Updated: <?php echo date('F j, Y'); ?></p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Information We Collect</h4>
            <p style="margin-bottom: 0.5rem;">Depending on system functionality, UniFind may collect:</p>
            <ul style="margin-bottom: 2rem; margin-left: 1.5rem;">
                <li>Full name</li>
                <li>Email address</li>
                <li>Student ID</li>
                <li>Department</li>
                <li>Program</li>
                <li>Account role</li>
                <li>Lost/found item information</li>
                <li>Item descriptions</li>
                <li>Location information</li>
                <li>Dates associated with reports</li>
                <li>Images uploaded with reports</li>
            </ul>
            
            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Why We Collect This Information</h4>
            <p style="margin-bottom: 2rem;">Information is used to operate the Lost and Found System, associate reports with users, support searching and filtering, manage reports, and help facilitate the recovery of lost items.</p>
            
            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">How Item Information Is Used</h4>
            <p style="margin-bottom: 2rem;">Information submitted in a lost or found report may be visible to authorized users where necessary for identifying and recovering an item.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Personal Information</h4>
            <p style="margin-bottom: 2rem;">UniFind should not be used to publish unnecessary sensitive personal information. Users should avoid including passwords, financial information, private identification details, or other unnecessary sensitive information in item descriptions.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Account Security</h4>
            <p style="margin-bottom: 2rem;">Passwords should be securely stored using appropriate password hashing techniques. Users should keep their login credentials confidential.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Data Access</h4>
            <p style="margin-bottom: 2rem;">Authorized administrators may access information necessary to manage the system, moderate reports, manage users, and maintain system functionality.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Data Retention</h4>
            <p style="margin-bottom: 2rem;">Reports and account information may be retained for as long as necessary for the operation and administration of the system, subject to applicable university requirements.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Third-Party Services</h4>
            <p style="margin-bottom: 2rem;">UniFind does not automatically share user information with third-party services unless such services are intentionally integrated into the system.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Cookies and Sessions</h4>
            <p style="margin-bottom: 2rem;">The system may use PHP sessions and necessary browser mechanisms to maintain authentication and provide core functionality.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">User Responsibility</h4>
            <p style="margin-bottom: 2rem;">Users should provide accurate information and avoid submitting unnecessary personal or sensitive information.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Policy Updates</h4>
            <p style="margin-bottom: 2rem;">This Privacy Policy may be updated as the UniFind system develops.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Contact</h4>
            <p style="margin-bottom: 1rem;">For privacy-related questions, contact the responsible UniFind administrator or relevant university authority.</p>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = 1;
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    document.querySelectorAll('.item-card h4').forEach(el => {
        el.style.opacity = 0;
        el.style.transform = 'translateY(10px)';
        el.style.transition = 'all 0.5s ease-out';
        observer.observe(el);
    });
});
</script>
<?php include 'includes/footer.php'; ?>
