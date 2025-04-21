<?php
session_start();
include 'config/db_connect.php';
// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
    <?php include 'admin_sidebar.php'; ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f1c40f;
            --info-color: #2980b9;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .content {
            padding: 2rem;
            margin-left: 250px; /* Adjust based on sidebar width */
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }

        .metric-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .list-group-item {
            border-color: rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .stat-box {
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            background-color: white;
            margin: 0.5rem;
        }

        .stat-box h3 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .card {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-4">Reports Dashboard</h1>
        <div class="metric-badge">
            <i class="fas fa-clock me-2"></i>
            <?php echo date('F j, Y'); ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Sales Overview Card -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Sales Overview</h5>
                    <span class="badge bg-light text-dark">Real-time</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Today's Sales</span>
                        <strong class="text-success">$1,234.56</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Weekly Sales</span>
                        <strong class="text-primary">$8,765.43</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Monthly Sales</span>
                        <strong class="text-warning">$32,456.78</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Items Card -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Popular Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold">Margherita Pizza</span>
                                <div class="text-muted small">Italian Cuisine</div>
                            </div>
                            <span class="badge bg-primary rounded-pill">145</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold">Masala Sandwich</span>
                                <div class="text-muted small">Indian Snack</div>
                            </div>
                            <span class="badge bg-primary rounded-pill">98</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold">Classic Burger</span>
                                <div class="text-muted small">American Fast Food</div>
                            </div>
                            <span class="badge bg-primary rounded-pill">76</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Statistics Card -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Order Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 class="text-primary">256</h3>
                                <p class="text-muted mb-0">Orders Today</p>
                                <small class="text-success"><i class="fas fa-arrow-up"></i> 12% from yesterday</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 class="text-success">92%</h3>
                                <p class="text-muted mb-0">Completion Rate</p>
                                <small class="text-warning"><i class="fas fa-clock"></i> 8 pending</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 class="text-warning">24 min</h3>
                                <p class="text-muted mb-0">Avg. Prep Time</p>
                                <small class="text-danger"><i class="fas fa-exclamation-circle"></i> +3min from target</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 class="text-info">4.8/5</h3>
                                <p class="text-muted mb-0">Customer Rating</p>
                                <small class="text-primary">1,234 ratings</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>