<?php
session_start();
include 'config/db_connect.php';

// Get all categories for filter
$categories_sql = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_sql);

// Handle filters
$where_clause = "";
$sort_clause = "";

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_id = mysqli_real_escape_string($conn, $_GET['category']);
    $where_clause = "WHERE category_id = '$category_id'";
}

// New filter for offers
if (isset($_GET['offer']) && $_GET['offer'] == '1') {
    $where_clause = empty($where_clause) ? "WHERE is_offer = 1" : $where_clause . " AND is_offer = 1";
}

if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_low':
            $sort_clause = "ORDER BY price ASC";
            break;
        case 'price_high':
            $sort_clause = "ORDER BY price DESC";
            break;
        case 'discount':
            $sort_clause = "ORDER BY discount_percentage DESC"; // Sort by discount percentage
            break;
        default:
            $sort_clause = "";
    }
}

// Main query with filters
$sql = "SELECT m.*, c.name as category_name 
        FROM menu_items m 
        LEFT JOIN categories c ON m.category_id = c.id 
        $where_clause 
        $sort_clause";

// Handle Add to Cart and Add to Wishlist actions
if (isset($_GET['cart'])) {
    $item_id = (int)$_GET['cart'];
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

    // Check if the item is already in the cart
    $checkQuery = $conn->prepare("SELECT * FROM carts WHERE user_id = ? AND item_id = ?");
    $checkQuery->bind_param("ii", $user_id, $item_id);
    $checkQuery->execute();
    $checkQuery->store_result();

    if ($checkQuery->num_rows > 0) {
        // If item exists, increase quantity
        $updateQuery = $conn->prepare("UPDATE carts SET quantity = quantity + 1 WHERE user_id = ? AND item_id = ?");
        $updateQuery->bind_param("ii", $user_id, $item_id);
        $updateQuery->execute();
    } else {
        // Insert new item into carts
        $insertQuery = $conn->prepare("INSERT INTO carts (user_id, item_id, quantity) VALUES (?, ?, 1)");
        $insertQuery->bind_param("ii", $user_id, $item_id);
        $insertQuery->execute();
    }
    echo "<script>alert('Added to cart!');</script>";
}
if (isset($_GET['wishlist'])) {
    $item_id = (int)$_GET['wishlist'];
    $user_id = $_SESSION['user_id']; // Ensure session contains user_id

    // Check if item is already in wishlist
    $checkQuery = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND item_id = ?");
    if ($checkQuery) {
        $checkQuery->bind_param("ii", $user_id, $item_id);
        $checkQuery->execute();
        $checkQuery->store_result();

        if ($checkQuery->num_rows == 0) {
            // Insert into wishlist
            $insertQuery = $conn->prepare("INSERT INTO wishlist (user_id, item_id) VALUES (?, ?)");
            if ($insertQuery) {
                $insertQuery->bind_param("ii", $user_id, $item_id);
                $insertQuery->execute();
                echo "<script>alert('Added to wishlist!');</script>";
            } else {
                echo "<script>alert('Failed to prepare insert query');</script>";
            }
        } else {
            echo "<script>alert('Item is already in your wishlist!');</script>";
        }
    } else {
        echo "<script>alert('Failed to prepare check query');</script>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'header.php'; ?>
   
    <style>
    body {
        background-color: #f4f4f4;
        font-family: 'Poppins', sans-serif;
    }

    .menu-item {
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 12px;
        overflow: hidden;
        border: none;
        background: white;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        position: relative; /* For offer badge */
    }

    .menu-item:hover {
        transform: translateY(-5px);
        box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.15);
    }

    .menu-img {
        height: 220px;
        object-fit: cover;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .card-body {
        padding: 20px;
        text-align: center;
    }

    .card-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
    }

    .price {
        font-size: 1.3rem;
        font-weight: bold;
        color: #ff5722;
        margin: 10px 0;
    }

    .original-price {
        text-decoration: line-through;
        color: #999;
        font-size: 1.1rem;
        margin-right: 8px;
    }

    .discount-price {
        color: #ff5722;
        font-weight: bold;
    }

    .discount-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #ff5722;
        color: white;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 20px;
        z-index: 10;
        font-size: 0.9rem;
    }

    .btn {
        border-radius: 8px;
        font-size: 14px;
        padding: 8px 12px;
    }

    .btn-success {
        background-color: #28a745;
        border: none;
    }

    .btn-warning {
        background-color: #ff9800;
        border: none;
        color: white;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .btn-warning:hover {
        background-color: #e68900;
    }

    .sidebar {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    .filter-section {
        margin-bottom: 20px;
    }

    .filter-section h5 {
        margin-bottom: 15px;
        color: #333;
        font-weight: bold;
    }

    .category-btn {
        margin: 5px;
        font-size: 14px;
    }

    .active-filter {
        background-color: #0d6efd;
        color: white !important;
    }

    .btn-outline-primary {
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }

    .alert-info {
        text-align: center;
        padding: 15px;
        font-size: 1rem;
        font-weight: bold;
    }

    /* Special offer banner styles */
    .offer-banner {
        background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
        color: white;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 12px;
        text-align: center;
    }

    .offer-banner h3 {
        margin: 0;
        font-weight: bold;
    }

    .offer-banner p {
        margin: 10px 0 0 0;
    }
</style>

</head>
<body>
<div class="container mt-4">
    <!-- Offer Banner (Only show on offers page) -->
    <?php if (isset($_GET['offer']) && $_GET['offer'] == '1'): ?>
    <div class="offer-banner">
        <h3>Special Offers</h3>
        <p>Enjoy our limited-time special offers with great discounts!</p>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Sidebar with filters -->
        <div class="col-md-3">
            <div class="sidebar">
                <div class="filter-section">
                    <h5>Sort by Price</h5>
                    <div class="d-grid gap-2">
                        <a href="?sort=price_low<?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?><?php echo isset($_GET['offer']) ? '&offer=1' : ''; ?>" 
                           class="btn btn-outline-primary <?php echo ($_GET['sort'] ?? '') == 'price_low' ? 'active-filter' : ''; ?>">
                            Low to High
                        </a>
                        <a href="?sort=price_high<?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?><?php echo isset($_GET['offer']) ? '&offer=1' : ''; ?>" 
                           class="btn btn-outline-primary <?php echo ($_GET['sort'] ?? '') == 'price_high' ? 'active-filter' : ''; ?>">
                            High to Low
                        </a>
                        <a href="?sort=discount<?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?><?php echo isset($_GET['offer']) ? '&offer=1' : ''; ?>" 
                           class="btn btn-outline-primary <?php echo ($_GET['sort'] ?? '') == 'discount' ? 'active-filter' : ''; ?>">
                            Best Discount
                        </a>
                    </div>
                </div>

                <div class="filter-section">
                    <h5>Categories</h5>
                    <div class="d-grid gap-2">
                        <a href="?<?php echo isset($_GET['sort']) ? 'sort='.$_GET['sort'] : ''; ?><?php echo isset($_GET['offer']) ? '&offer=1' : ''; ?>" 
                           class="btn btn-outline-primary <?php echo (!isset($_GET['category']) && !isset($_GET['offer'])) ? 'active-filter' : ''; ?>">
                            All Categories
                        </a>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <a href="?category=<?php echo $category['id']; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?><?php echo isset($_GET['offer']) ? '&offer=1' : ''; ?>" 
                               class="btn btn-outline-primary <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'active-filter' : ''; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="filter-section">
                    <h5>Special Offers</h5>
                    <div class="d-grid gap-2">
                        <a href="<?php echo isset($_GET['category']) ? '?category='.$_GET['category'] : '?'; ?><?php echo isset($_GET['sort']) ? '&sort='.$_GET['sort'] : ''; ?>&offer=1" 
                           class="btn btn-outline-danger <?php echo isset($_GET['offer']) ? 'active-filter' : ''; ?>">
                            View Offers
                        </a>
                    </div>
                </div>

                <div class="filter-section">
                    <a href="menu.php" class="btn btn-secondary w-100">Clear All Filters</a>
                </div>
            </div>
        </div>

        <!-- Menu items grid -->
        <div class="col-md-9">
            <div class="row">
                <?php
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card menu-item">
                                <?php if (isset($row['is_offer']) && $row['is_offer'] == 1 && isset($row['discount_percentage'])): ?>
                                    <div class="discount-badge"><?php echo $row['discount_percentage']; ?>% OFF</div>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($row['item_image']); ?>" class="card-img-top menu-img" alt="Menu Item">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['item_name']); ?></h5>
                                    <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($row['category_name']); ?></small></p>
                                    
                                    <?php if (isset($row['is_offer']) && $row['is_offer'] == 1 && isset($row['original_price'])): ?>
                                        <p class="price">
                                            <span class="original-price">$<?php echo number_format($row['original_price'], 2); ?></span>
                                            <span class="discount-price">$<?php echo number_format($row['price'], 2); ?></span>
                                        </p>
                                    <?php else: ?>
                                        <p class="price">$<?php echo number_format($row['price'], 2); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between">
                                        <!-- Add to Cart Button -->
                                        <a href="?cart=<?php echo $row['id']; ?>" class="btn btn-success">Add to Cart</a>
                                        <!-- Add to Wishlist Button -->
                                        <a href="?wishlist=<?php echo $row['id']; ?>" class="btn btn-warning">Add to Wishlist</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="alert alert-info">No menu items available.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>