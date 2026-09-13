<?php
require_once 'config/database.php';
$page_title = "Frequently Asked Questions";
include 'includes/header.php';
?>
<section class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
    <div class="section-header text-center reveal-element" style="margin-bottom: 3rem;">
        <h2>Frequently Asked Questions</h2>
        <p class="text-secondary">Find answers to common questions about using UniFind at SLIATE – Kurunegala.</p>
    </div>

    <div class="item-card reveal-element" style="padding: 2rem; margin-bottom: 2rem;">
        <input type="text" id="faq-search" class="form-control" placeholder="Search frequently asked questions..." style="margin-bottom: 2rem; transition: border-color 0.3s ease;">
        
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">What is UniFind? <span class="faq-icon">+</span></div>
                <div class="faq-answer">UniFind is a centralized Lost and Found Management System for SLIATE – Kurunegala. It allows students and staff to report lost or found items, search existing reports, and help return belongings to their rightful owners.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Who can use UniFind? <span class="faq-icon">+</span></div>
                <div class="faq-answer">UniFind is intended for students and staff of SLIATE – Kurunegala across its departments and academic programs.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">How do I report a lost item? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Log in to your UniFind account, select 'Report Lost', provide the item details, location, date, description, and an optional image, then submit the report.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">How do I report an item I found? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Log in to UniFind and select 'Report Found'. Provide accurate information about the item, where you found it, when you found it, and any useful identifying details.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Can I search for an item? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Yes. Use the Browse Items page to search by item name or description. You can also filter results by Lost or Found status, department, program, category, location, and other available filters.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Can I edit my report? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Yes. You can manage your own reports from the My Reports section. Depending on the report status, you may be able to edit or remove your report.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">How do I know whether an item has been returned? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Items can have different statuses. When an item has been successfully returned, its status can be updated to 'Returned'.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">What should I do if I find an item? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Report the item through UniFind with accurate information about where and when you found it. Avoid sharing sensitive information publicly. If appropriate, hand the physical item to the relevant university authority.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Can I report an item without creating an account? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Currently, users must log in to submit and manage reports. This helps the system associate reports with the correct user and maintain accountability.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">What information should I include in a report? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Provide the item name, category, location, date, description, and any useful identifying details. Adding a clear image can also help other users recognize the item.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">What if I find a possible match? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Open the item details and use the available contact or interest functionality. Provide enough information to help verify ownership without unnecessarily sharing private information.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">What should I do if someone claims my item? <span class="faq-icon">+</span></div>
                <div class="faq-answer">Verify that the person can provide appropriate identifying information about the item before handing it over. When necessary, involve the relevant university authority.</div>
            </div>
        </div>
    </div>
    
    <div class="text-center reveal-element">
        <p class="text-secondary" style="margin-bottom: 1rem;">Still have a question?</p>
        <a href="<?php echo $base_url; ?>/contact.php" class="btn btn-primary">Contact Us</a>
    </div>
</section>

<style>
.faq-item {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    transition: all 0.3s ease;
}
.faq-item:last-child {
    border-bottom: none;
}
.faq-item:hover {
    background: rgba(255,255,255,0.02);
    border-radius: 8px;
    padding-left: 1rem;
    padding-right: 1rem;
}
.faq-question {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--white);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: color 0.2s ease;
}
.faq-question:hover {
    color: var(--primary-blue);
}
.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, margin-top 0.4s ease, opacity 0.4s ease;
    opacity: 0;
    color: var(--light-blue);
    margin-top: 0;
    line-height: 1.5;
}
.faq-item.active .faq-answer {
    max-height: 300px;
    opacity: 1;
    margin-top: 1rem;
}
.faq-icon {
    font-size: 1.5rem;
    font-weight: 300;
    transition: transform 0.3s ease;
}
.faq-item.active .faq-icon {
    transform: rotate(45deg);
    color: var(--primary-blue);
}
#faq-search:focus {
    border-color: var(--primary-blue);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const faqSearch = document.getElementById('faq-search');
    const faqItems = document.querySelectorAll('.faq-item');

    faqSearch.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        faqItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(term)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    faqItems.forEach(item => {
        const q = item.querySelector('.faq-question');
        q.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            faqItems.forEach(i => i.classList.remove('active'));
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
});
</script>
<?php include 'includes/footer.php'; ?>
