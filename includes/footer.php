    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>UniFind</h3>
                    <p style="color: var(--secondary-text); line-height: 1.6; margin-top: 1rem;">SLIATE – Kurunegala's official lost and found system. Helping students and staff recover their belongings.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo $base_url; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo $base_url; ?>/items.php">Browse Items</a></li>
                        <li><a href="<?php echo $base_url; ?>/report-lost.php">Report Lost Item</a></li>
                        <li><a href="<?php echo $base_url; ?>/report-found.php">Report Found Item</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="<?php echo $base_url; ?>/faq.php">FAQ</a></li>
                        <li><a href="<?php echo $base_url; ?>/contact.php">Contact Us</a></li>
                        <li><a href="<?php echo $base_url; ?>/terms.php">Terms of Service</a></li>
                        <li><a href="<?php echo $base_url; ?>/privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Info</h4>
                    <p><span style="color: var(--cyan); margin-right: 8px;">📧</span> unifind.support@demo.example</p>
                    <p><span style="color: var(--cyan); margin-right: 8px;">📞</span> +94 77 123 4567</p>
                    <p><span style="color: var(--cyan); margin-right: 8px;">📍</span> SLIATE – Kurunegala Campus</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> SLIATE – Kurunegala. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="<?php echo $base_url; ?>/assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if (isset($success) && !empty($success)): ?>
                showToast("<?php echo addslashes(htmlspecialchars($success)); ?>", "success");
            <?php endif; ?>
            <?php if (isset($error) && !empty($error)): ?>
                showToast("<?php echo addslashes(htmlspecialchars($error)); ?>", "error");
            <?php endif; ?>
        });
    </script>
</body>
</html>
