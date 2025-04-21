<?php
session_start();

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect if the user is not an admin
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
   
    <?php include 'admin_sidebar.php'; ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
       body {
    background-color: #f4f6f9;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.sidebar {
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    background: linear-gradient(to bottom right,rgb(56, 56, 61),rgb(34, 36, 37));
    color: white;
    padding-top: 30px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.sidebar a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    padding: 12px 20px;
    display: block;
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}

.sidebar a:hover {
    background-color: rgba(255,255,255,0.1);
    color: white;
    border-left-color:rgb(4, 4, 5);
}

.content {
    margin-left: 280px;
    padding: 30px;
    max-width: 1200px;
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.btn-primary {
    background-color: #3498db;
    border-color: #3498db;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #2980b9;
    border-color: #2980b9;
}

.btn-danger {
    background-color: #e74c3c;
    border-color: #e74c3c;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    background-color: #c0392b;
    border-color: #c0392b;
}

h1 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .content {
        margin-left: 0;
        padding: 15px;
    }
}
    </style>
</head>
<body>



<!-- Main content area -->
<div class="content">
    <h1>Welcome to the Admin Dashboard</h1>
    <p>This page is only accessible by administrators.</p>
    
    <!-- You can add more content here based on your admin features -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Latest Reports</h5>
            <p class="card-text">View the latest system reports and analytics.</p>
            <a href="reports.php" class="btn btn-primary">View Reports</a>
        </div>
    </div>
    
    <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
