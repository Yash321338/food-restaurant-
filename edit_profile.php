<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db_connect.php';


$user_id = $_SESSION['user_id'];
$msg = "";
$msgClass = "";

// Fetch current user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the orders and order_items tables exist
$checkOrdersTable = $conn->query("SHOW TABLES LIKE 'orders'");
$checkOrderItemsTable = $conn->query("SHOW TABLES LIKE 'order_items'");
$ordersExist = $checkOrdersTable->num_rows > 0;
$orderItemsExist = $checkOrderItemsTable->num_rows > 0;

// Fetch order history if tables exist
$orders_result = null;
if ($ordersExist && $orderItemsExist) {
    // Simple query first to check if there are any orders
    $check_orders = $conn->query("SELECT id FROM orders WHERE user_id = $user_id LIMIT 1");
    
    if ($check_orders && $check_orders->num_rows > 0) {
        // If there are orders, fetch with full details
        $order_sql = "SELECT o.*, 
                     (SELECT SUM(oi.quantity * oi.price) FROM order_items oi WHERE oi.order_id = o.id) as total_amount 
                     FROM orders o 
                     WHERE o.user_id = ? 
                     ORDER BY o.order_date DESC";
        $order_stmt = $conn->prepare($order_sql);
        
        if ($order_stmt) {
            $order_stmt->bind_param("i", $user_id);
            $order_stmt->execute();
            $orders_result = $order_stmt->get_result();
        } else {
            $msg = "Error preparing order query: " . $conn->error;
            $msgClass = "alert-danger";
        }
    }
}

// Handle order cancellation
if (isset($_GET['cancel_order']) && is_numeric($_GET['cancel_order'])) {
    $order_id = $_GET['cancel_order'];
    
    // Check if order belongs to this user
    $check_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if ($check_stmt) {
        $check_stmt->bind_param("ii", $order_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $order = $check_result->fetch_assoc();
            
            // Only allow cancellation for orders with status 'pending' or 'processing'
            if ($order['status'] == 'pending' || $order['status'] == 'processing') {
                $update_sql = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                
                if ($update_stmt) {
                    $update_stmt->bind_param("i", $order_id);
                    
                    if ($update_stmt->execute()) {
                        $msg = "Order #" . $order_id . " has been cancelled successfully";
                        $msgClass = "alert-success";
                        
                        // Refresh order list
                        if ($order_stmt) {
                            $order_stmt->execute();
                            $orders_result = $order_stmt->get_result();
                        }
                    } else {
                        $msg = "Error cancelling order: " . $conn->error;
                        $msgClass = "alert-danger";
                    }
                } else {
                    $msg = "Error preparing cancellation query: " . $conn->error;
                    $msgClass = "alert-danger";
                }
            } else {
                $msg = "This order cannot be cancelled because it is already " . $order['status'];
                $msgClass = "alert-warning";
            }
        } else {
            $msg = "Invalid order";
            $msgClass = "alert-danger";
        }
    } else {
        $msg = "Error checking order ownership: " . $conn->error;
        $msgClass = "alert-danger";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($username)) {
        $msg = "Username is required";
        $msgClass = "alert-danger";
    } elseif (empty($email)) {
        $msg = "Email is required";
        $msgClass = "alert-danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format";
        $msgClass = "alert-danger";
    } else {
        // Handle image upload
        $profile_image = $user['profile_image']; // Keep existing image by default
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png"];
            $filename = $_FILES["profile_image"]["name"];
            $filetype = $_FILES["profile_image"]["type"];
            $filesize = $_FILES["profile_image"]["size"];
            
            // Verify file extension
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!array_key_exists($ext, $allowed)) {
                $msg = "Please select a valid format (JPG, JPEG, PNG)";
                $msgClass = "alert-danger";
            } else {
                // Verify file size - 5MB maximum
                $maxsize = 5 * 1024 * 1024;
                if ($filesize > $maxsize) {
                    $msg = "File size is larger than 5MB";
                    $msgClass = "alert-danger";
                } else {
                    // Create upload directory if it doesn't exist
                    $upload_dir = "uploads/profile_images/";
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Generate unique filename
                    $new_filename = uniqid() . '.' . $ext;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $destination)) {
                        // Delete old image if exists
                        if ($user['profile_image'] && file_exists($user['profile_image'])) {
                            unlink($user['profile_image']);
                        }
                        $profile_image = $destination;
                    }
                }
            }
        }
        
        // Update user information
        if (empty($msg)) {
            if (!empty($new_password)) {
                // If password is being updated
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET username=?, email=?, password=?, profile_image=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssi", $username, $email, $hashed_password, $profile_image, $user_id);
                } else {
                    $msg = "Passwords do not match";
                    $msgClass = "alert-danger";
                }
            } else {
                // If password is not being updated
                $sql = "UPDATE users SET username=?, email=?, profile_image=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $username, $email, $profile_image, $user_id);
            }
            
            if (empty($msg) && $stmt->execute()) {
                $msg = "Profile updated successfully";
                $msgClass = "alert-success";
                // Refresh user data
                $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
                $user = $result->fetch_assoc();
            } else {
                $msg = "Error updating profile: " . $conn->error;
                $msgClass = "alert-danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <?php include 'header.php'; ?>
    <style>
    /* Updated Image Grid Styles */
    .image-grid {
        margin: 0 -8px;
    }

    .profile-image-card {
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        max-width: 120px; /* Set maximum width */
        margin: 0 auto; /* Center the card */
    }

    .profile-image-card.selected {
        border-color: #007bff;
    }

    .image-wrapper {
        position: relative;
        padding-bottom: 100%; /* 1:1 Aspect Ratio */
        width: 100px; /* Fixed width */
        height: 100px; /* Fixed height */
        background-color: #f8f9fa;
    }

    .image-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100px; /* Fixed width */
        height: 100px; /* Fixed height */
        object-fit: cover;
    }

    /* Preview Image Size */
    #profile-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .upload-card .image-wrapper {
        border: 2px dashed #dee2e6;
        width: 100px; /* Fixed width */
        height: 100px; /* Fixed height */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .upload-placeholder {
        text-align: center;
        color: #6c757d;
    }

    .upload-placeholder i {
        font-size: 1.5rem; /* Smaller icon */
        margin-bottom: 0.3rem;
    }

    .upload-placeholder span {
        display: block;
        font-size: 0.8rem; /* Smaller text */
    }

    /* Order history styles */
    .order-history-section {
        margin-top: 2rem;
    }

    .order-card {
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .order-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .order-status {
        font-weight: bold;
        text-transform: capitalize;
    }

    .order-status-pending {
        color: #ffc107;
    }

    .order-status-processing {
        color: #17a2b8;
    }

    .order-status-delivered {
        color: #28a745;
    }

    .order-status-cancelled {
        color: #dc3545;
    }

    .tab-content {
        padding-top: 1.5rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .image-wrapper,
        .image-wrapper img {
            width: 80px; /* Even smaller on mobile */
            height: 80px;
        }
        
        .upload-placeholder i {
            font-size: 1.2rem;
        }
        
        .upload-placeholder span {
            font-size: 0.7rem;
        }
        
        #profile-preview {
            width: 80px;
            height: 80px;
        }
    }
    </style>
</head>
<body>
    <div class="container profile-container">
        <?php if($msg != ""): ?>
            <div class="alert <?php echo $msgClass; ?> alert-dismissible fade show" role="alert">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Edit Profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">Order History</button>
            </li>
        </ul>

        <div class="tab-content" id="profileTabsContent">
            <!-- Profile Edit Tab -->
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Edit Profile</h2>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="<?php echo !empty($user['profile_image']) ? $user['profile_image'] : 'images/default-profile.jpg'; ?>" 
                                     class="profile-image" id="profile-preview" alt="Profile Image">
                                <div class="mt-2">
                                    <label for="profile_image" class="btn btn-outline-primary btn-sm">
                                        Change Profile Picture
                                    </label>
                                    <input type="file" class="d-none" id="profile_image" name="profile_image" 
                                           accept="image/*" onchange="previewImage(this)">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Old Password</label>
                                <input type="password" class="form-control" id="old_password" name="old_password">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="profile.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order History Tab -->
            <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Order History</h2>
                        
                        <?php if ($ordersExist && $orderItemsExist): ?>
                            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                                <?php while ($order = $orders_result->fetch_assoc()): ?>
                                    <div class="card order-card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Order #<?php echo $order['id']; ?></strong>
                                                <span class="ms-3 text-muted"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></span>
                                            </div>
                                            <span class="order-status order-status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Delivery Address:</strong><br>
                                                    <?php echo nl2br(htmlspecialchars($order['delivery_address'] ?? 'No address provided')); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'] ?? 0, 2); ?></p>
                                                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method'] ?? 'Unknown'); ?></p>
                                                </div>
                                            </div>
                                            
                                            <?php if ($order['status'] == 'pending' || $order['status'] == 'processing'): ?>
                                                <div class="text-end mt-2">
                                                    <a href="?cancel_order=<?php echo $order['id']; ?>" 
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Are you sure you want to cancel this order?')">
                                                        Cancel Order
                                                    </a>
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">View Details</a>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-end mt-2">
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">View Details</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center my-4">
                                    <p>You haven't placed any orders yet.</p>
                                    <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <p class="mb-0">Order history feature is currently unavailable. Please try again later.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>