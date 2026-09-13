<?php
// Redirect old admin.php link to the proper admin dashboard
require_once 'config/database.php';
header("Location: " . $base_url . "/admin/dashboard.php");
exit();
?>
