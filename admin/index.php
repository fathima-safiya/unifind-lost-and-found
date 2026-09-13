<?php
// Redirect to admin dashboard
require_once '../config/database.php';
header("Location: " . $base_url . "/admin/dashboard.php");
exit();
?>
