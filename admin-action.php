<?php
// Old admin-action.php → forward to new location
require_once 'config/database.php';
// Preserve POST data through redirect is not possible; just redirect the dashboard
header("Location: " . $base_url . "/admin/dashboard.php");
exit();
?>
