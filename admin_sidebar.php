<?php
// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}
?>

<!-- Sidebar -->
<div class="sidebar">
    <h2 class="text-center text-white">Admin Panel</h2>
    <ul class="list-unstyled">
        <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li><a href="manage_users.php"><i class="fas fa-users me-2"></i>Manage Users</a></li>
        <li><a href="manage_menu.php"><i class="fas fa-utensils me-2"></i>Manage Menu</a></li>
        <li><a href="manage_menu_category.php"><i class="fas fa-utensils me-2"></i>Manage Category</a></li>

         <li class="nav-item">
            <a class="nav-link" href="admin_coupons.php">
                <i class="fas fa-tags"></i>
                <span>Manage Coupons</span>
            </a>
        </li>
        <li><a href="reports.php"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
        <li><a href="admin_offers.php"><i class="fas fa-tags me-2"></i>Offer</a></li>
        <li><a href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
        <li><a href="admin_profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
      
    </ul>
</div>      

<style>
.sidebar {
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    background-color: #343a40;
    color: white;
    padding-top: 20px;
    z-index: 1000;
}

.sidebar a {
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    display: block;
    transition: all 0.3s ease;
}

.sidebar a:hover {
    background-color: #495057;
    padding-left: 25px;
}

.sidebar h2 {
    padding-bottom: 20px;
    border-bottom: 1px solid #495057;
    margin-bottom: 20px;
}

.content {
    margin-left: 260px;
    padding: 20px;
}
</style>
