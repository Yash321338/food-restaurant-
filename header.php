<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    include 'config/db_connect.php';
}

// Enhanced connection error handling
if (!$conn) {
    // Log the connection error or handle it gracefully
    error_log("Database connection failed: " . mysqli_connect_error());
    $wishlist_count = 0;
    $cart_count = 0;
} else {
    // Ensure $user_id is set before using it
    $user_id = $_SESSION['user_id'] ?? 0;

    // Count wishlist items
    $wishlist_query = $conn->prepare("SELECT COUNT(*) AS wishlist_count FROM wishlist WHERE user_id = ?");
    if ($wishlist_query) {
        $wishlist_query->bind_param("i", $user_id);
        $wishlist_query->execute();
        $wishlist_result = $wishlist_query->get_result()->fetch_assoc();
        $wishlist_count = $wishlist_result['wishlist_count'];
    } else {
        error_log("Wishlist query preparation failed: " . $conn->error);
        $wishlist_count = 0;
    }

    // Count cart items
    $cart_query = $conn->prepare("SELECT COUNT(*) AS cart_count FROM carts WHERE user_id = ?");
    if ($cart_query) {
        $cart_query->bind_param("i", $user_id);
        $cart_query->execute();
        $cart_result = $cart_query->get_result()->fetch_assoc();
        $cart_count = $cart_result['cart_count'];
    } else {
        error_log("Cart query preparation failed: " . $conn->error);
        $cart_count = 0;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Name</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 76px;
        }

        .navbar {
            padding: 1rem 0;
            background: #ffffff !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            color: #333 !important;
            padding: 0.5rem 1rem !important;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            font-weight: 500;
        }

        .content {
            min-height: calc(100vh - 76px);
            padding: 2rem 0;
        }
    </style>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
               <a href="index.php"> <img src="assets/images/logo.png" alt="Food Restaurant Logo" style="height: 60px; margin-right: 10px;"></a>
                <a class="navbar-brand" href="index.php">Food Restaurant</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto"> 
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.php">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">Contact Us</a>
                        </li>
                        <li class="nav-item">
                            
                            <a class="nav-link" href="menu.php"> Food Menu</a>
                                
                            </a>
                        
                        </li>
                        <?php if(isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <form class="d-flex me-2" action="#" method="GET">
                        <div class="input-group">
                            <input class="form-control" type="search" name="q" placeholder="Search menu..." aria-label="Search">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    <div class="navbar-nav me-2">
    <a class="nav-link position-relative" href="cart.php">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?php echo $cart_count; ?>
        </span>
    </a>
    <a class="nav-link position-relative" href="wishlist.php">
        <i class="fas fa-heart"></i>
        <span id="wishlist-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?php echo $wishlist_count; ?>
        </span>
        <a class="nav-link position-relative" href="offer.php">
    <i class="fas fa-tag"></i>
      
    </span>
</a>
    </a>
</div>

                    <div class="navbar-nav">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a class="nav-link btn btn-primary me-2" href="profile.php">Profile</a>
                            <a class="nav-link btn btn-danger" href="logout.php">Logout</a>
                        <?php else: ?>
                            <a class="nav-link btn btn-outline-primary me-2" href="login.php">Login</a>
                            <a class="nav-link btn btn-primary" href="register.php">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <div class="content">


    <!-- Add Bootstrap JS and its dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
