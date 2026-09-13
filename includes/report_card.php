<div class="item-card" style="display: flex; flex-direction: column; height: 100%;">
    <div class="item-image" style="height: 200px; background-image: url('<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : ''; ?>'); background-size: cover; background-position: center; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;">
        <?php if (empty($item['image'])): ?>
            <span class="text-secondary">No Image Available</span>
        <?php endif; ?>
    </div>
    
    <div class="item-content" style="flex: 1; display: flex; flex-direction: column; padding: 1.5rem;">
        <div class="item-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
            <h3 class="item-title" style="margin: 0; font-size: 1.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($item['title']); ?>">
                <?php echo htmlspecialchars($item['title']); ?>
            </h3>
            <span class="badge badge-<?php echo htmlspecialchars($item['type']); ?>"><?php echo ucfirst(htmlspecialchars($item['type'])); ?></span>
        </div>
        
        <div class="item-details" style="flex: 1; margin-bottom: 1.5rem;">
            <p style="margin-bottom: 0.5rem; color: var(--secondary-text);"><strong>Category:</strong> <?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></p>
            <p style="margin-bottom: 0.5rem; color: var(--secondary-text);"><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
            <p style="margin-bottom: 0.5rem; color: var(--secondary-text);"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($item['item_date'])); ?></p>
            
            <?php if ($item['status'] !== 'active'): ?>
                <p style="margin-bottom: 0.5rem; color: var(--secondary-text);"><strong>Status:</strong> 
                    <span style="color: <?php echo $item['status'] === 'returned' ? 'var(--warning)' : 'inherit'; ?>;">
                        <?php echo ucfirst(htmlspecialchars($item['status'])); ?>
                    </span>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="item-actions" style="margin-top: auto; display: flex; flex-direction: column; gap: 0.5rem;">
            <a href="item-details.php?id=<?php echo $item['id']; ?>" class="btn btn-outline" style="width: 100%; text-align: center; display: block;">View Details</a>
            
            <?php 
            $current_user_id = $_SESSION['user_id'] ?? null;
            $item_owner_id = $item['user_id'] ?? null;
            $is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
            if ($current_user_id && $item_owner_id && $current_user_id == $item_owner_id || $is_admin): 
            ?>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <a href="edit-report.php?id=<?php echo $item['id']; ?>" style="
                    flex: 1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 0.5rem 0.75rem;
                    font-size: 0.85rem;
                    font-weight: 600;
                    border: 1px solid rgba(255,255,255,0.2);
                    border-radius: var(--radius-sm);
                    color: var(--white);
                    text-decoration: none;
                    background: rgba(255,255,255,0.05);
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(255,255,255,0.12)'; this.style.borderColor='rgba(255,255,255,0.4)';"
                   onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='rgba(255,255,255,0.2)';">
                    ✏️ Edit
                </a>
                <form method="POST" action="my-reports.php" data-confirm="Are you sure you want to delete this report? This cannot be undone." style="flex: 1; display: flex;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <button type="submit" style="
                        flex: 1;
                        padding: 0.5rem 0.75rem;
                        font-size: 0.85rem;
                        font-weight: 600;
                        border: 1px solid rgba(239, 68, 68, 0.4);
                        border-radius: var(--radius-sm);
                        color: var(--danger);
                        background: rgba(239, 68, 68, 0.08);
                        cursor: pointer;
                        transition: all 0.2s ease;
                    " onmouseover="this.style.background='rgba(239,68,68,0.2)'; this.style.borderColor='var(--danger)';"
                       onmouseout="this.style.background='rgba(239,68,68,0.08)'; this.style.borderColor='rgba(239,68,68,0.4)';">
                        🗑️ Delete
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
