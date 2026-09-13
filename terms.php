<?php
require_once 'config/database.php';
$page_title = "Terms of Service";
include 'includes/header.php';
?>
<section class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
    <div class="section-header text-center reveal-element" style="margin-bottom: 3rem;">
        <h2>Terms of Service</h2>
        <p class="text-secondary">Guidelines for using the UniFind Lost & Found Management System.</p>
    </div>

    <div class="item-card reveal-element" style="padding: 2rem;">
        <div class="text-secondary" style="line-height: 1.7;">
            <p style="margin-bottom: 2rem; font-style: italic;">Last Updated: <?php echo date('F j, Y'); ?></p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Acceptance of Terms</h4>
            <p style="margin-bottom: 2rem;">By using UniFind, you agree to use the system responsibly and in accordance with applicable university rules and policies.</p>
            
            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Purpose of the System</h4>
            <p style="margin-bottom: 2rem;">UniFind is provided to help students and staff report, search for, and manage lost and found items within SLIATE – Kurunegala.</p>
            
            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">User Responsibilities</h4>
            <p style="margin-bottom: 0.5rem;">Users should:</p>
            <ul style="margin-bottom: 2rem; margin-left: 1.5rem;">
                <li>Provide accurate information.</li>
                <li>Submit genuine lost or found item reports.</li>
                <li>Avoid misleading descriptions.</li>
                <li>Avoid submitting offensive or inappropriate content.</li>
                <li>Avoid impersonating another person.</li>
                <li>Respect other users' privacy.</li>
                <li>Use the system only for legitimate lost and found purposes.</li>
            </ul>
            
            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Prohibited Activities</h4>
            <p style="margin-bottom: 0.5rem;">Users must not:</p>
            <ul style="margin-bottom: 2rem; margin-left: 1.5rem;">
                <li>Submit fraudulent reports.</li>
                <li>Attempt to claim items that do not belong to them.</li>
                <li>Upload malicious files.</li>
                <li>Attempt to access another user's account.</li>
                <li>Attempt to modify or delete another user's reports.</li>
                <li>Abuse or disrupt the system.</li>
                <li>Use the platform for unrelated commercial or harmful activities.</li>
            </ul>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Item Ownership and Verification</h4>
            <p style="margin-bottom: 2rem;">Submitting a report does not establish ownership of an item. Users should verify identifying details before returning an item to another person.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">User Accounts</h4>
            <p style="margin-bottom: 2rem;">Users are responsible for maintaining the security of their login credentials and should not share their passwords with others.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Content and Reports</h4>
            <p style="margin-bottom: 2rem;">UniFind may allow authorized administrators to review, approve, reject, edit, or remove reports that violate system rules.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">System Availability</h4>
            <p style="margin-bottom: 2rem;">UniFind is provided as a university information system. Availability may be affected by maintenance, technical issues, or other circumstances.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Privacy</h4>
            <p style="margin-bottom: 2rem;">Personal information submitted through UniFind is handled according to the system's Privacy Policy.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Changes to These Terms</h4>
            <p style="margin-bottom: 2rem;">These terms may be updated when the system or its policies change.</p>

            <h4 style="color: var(--primary-blue); margin-bottom: 0.75rem; font-size: 1.2rem;">Contact</h4>
            <p style="margin-bottom: 1rem;">For questions about these terms, contact the responsible UniFind administrator or relevant university authority.</p>
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
