<?php 
require_once 'config/database.php';

// Fetch Statistics dynamically from database
$stats = [
    'total_reports' => $pdo->query("SELECT COUNT(*) FROM items WHERE status != 'rejected'")->fetchColumn(),
    'active_lost' => $pdo->query("SELECT COUNT(*) FROM items WHERE type = 'lost' AND status = 'active'")->fetchColumn(),
    'active_found' => $pdo->query("SELECT COUNT(*) FROM items WHERE type = 'found' AND status = 'active'")->fetchColumn(),
    'returned' => $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'returned'")->fetchColumn()
];

// Calculate return rate safely
$return_rate = 0;
if ($stats['total_reports'] > 0) {
    $return_rate = round(($stats['returned'] / $stats['total_reports']) * 100);
}

// Fetch 3 most recent active reports
$recent_query = "
    SELECT i.id, i.user_id, i.title, i.type, i.location, i.item_date, i.image, i.status, 
           c.name as category_name, d.name as department_name, p.name as program_name
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN departments d ON i.department_id = d.id
    LEFT JOIN programs p ON i.program_id = p.id
    WHERE i.status != 'rejected'
    ORDER BY i.created_at DESC
    LIMIT 3
";
$recent_items = $pdo->query($recent_query)->fetchAll();

include 'includes/header.php'; 
?>

<!-- Hero Section -->
<section class="hero" style="position: relative; overflow: hidden;">
    <!-- Abstract Background Shapes -->
    <div style="position: absolute; top: -50%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, var(--bright-blue) 0%, transparent 70%); opacity: 0.15; border-radius: 50%; animation: pulse 10s infinite alternate;"></div>
    <div style="position: absolute; bottom: -50%; right: -10%; width: 600px; height: 600px; background: radial-gradient(circle, var(--cyan) 0%, transparent 70%); opacity: 0.1; border-radius: 50%; animation: pulse 12s infinite alternate-reverse;"></div>

    <div class="container reveal-element" style="position: relative; z-index: 1;">
        <h1 class="animate-gradient-text" style="font-size: 5.5rem; font-weight: 900; letter-spacing: -2px; line-height: 1.1; margin-bottom: 1rem;">Lost something?<br>Find it here.</h1>
        <p style="font-size: 1.25rem; margin-bottom: 3rem; color: var(--light-blue); opacity: 0.9;">UniFind helps SLIATE – Kurunegala students and staff report, search, and recover lost belongings around campus.</p>
        
        <div class="search-box" style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);">
            <input type="text" placeholder="Search for an item (e.g., iPhone, Blue Backpack)..." class="form-control" style="border: none; padding: 1.25rem 1.5rem;">
            <button class="btn btn-primary" style="padding: 1.25rem 2.5rem; font-size: 1.1rem;">Search</button>
        </div>
        
        <div class="hero-actions" style="margin-top: 3rem;">
            <a href="report-lost.php" class="btn btn-hero-lost">I Lost Something</a>
            <a href="report-found.php" class="btn btn-hero-found">I Found Something</a>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="stats reveal-element">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <h3><span class="stat-counter" data-target="<?php echo $stats['total_reports']; ?>">0</span></h3>
                <p>Total Reports</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <h3><span class="stat-counter" data-target="<?php echo $stats['active_lost']; ?>">0</span></h3>
                <p>Active Lost Items</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3><span class="stat-counter" data-target="<?php echo $stats['active_found']; ?>">0</span></h3>
                <p>Active Found Items</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <h3><span class="stat-counter" data-target="<?php echo $return_rate; ?>">0</span>%</h3>
                <p>Return Rate</p>
            </div>
        </div>
    </div>
</section>

<!-- Recent Items -->
<section class="recent-items reveal-element">
    <div class="container">
        <div class="section-header text-center">
            <h2>Recently Reported Items</h2>
            <p class="text-secondary">Browse the latest items reported across SLIATE – Kurunegala campus.</p>
        </div>
        
        <div class="items-grid">
            <?php foreach ($recent_items as $item): ?>
                <?php include 'includes/report_card.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center" style="margin-top: 3rem;">
            <a href="items.php" class="btn btn-primary">View All Items</a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works reveal-element">
    <div class="container">
        <div class="section-header text-center">
            <h2>How UniFind Works</h2>
            <p class="text-secondary">A simple process to recover your lost belongings.</p>
        </div>
        
        <div class="steps-grid" style="position: relative;">
            <!-- Subtle connecting line between steps (desktop only) -->
            <div style="position: absolute; top: 30px; left: 15%; right: 15%; height: 2px; background: linear-gradient(90deg, transparent, var(--light-blue) 20%, var(--light-blue) 80%, transparent); z-index: 0;" class="d-none d-md-block"></div>

            <div class="step reveal-element" style="position: relative; z-index: 1; transition-delay: 0.1s;">
                <div class="step-number" style="box-shadow: 0 0 0 8px var(--white);">1</div>
                <h3>Report an Item</h3>
                <p class="text-secondary">Submit a detailed report of the item you lost or found, including location and description.</p>
            </div>
            <div class="step reveal-element" style="position: relative; z-index: 1; transition-delay: 0.3s;">
                <div class="step-number" style="box-shadow: 0 0 0 8px var(--white);">2</div>
                <h3>Search the System</h3>
                <p class="text-secondary">Browse through the database of reported items to find a potential match.</p>
            </div>
            <div class="step reveal-element" style="position: relative; z-index: 1; transition-delay: 0.5s;">
                <div class="step-number" style="box-shadow: 0 0 0 8px var(--white);">3</div>
                <h3>Reunite</h3>
                <p class="text-secondary">Contact the finder or owner securely to arrange the return of the item.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
