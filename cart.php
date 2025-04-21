<?php
session_start();
include 'config/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Add to Cart
if (isset($_GET['add_to_cart'])) {
    $item_id = (int)$_GET['add_to_cart'];

    // Check if item already exists in the cart
    $query = "SELECT * FROM carts WHERE user_id = ? AND item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_item = $result->fetch_assoc();

    if ($cart_item) {
        // If item exists, increase quantity
        $new_quantity = $cart_item['quantity'] + 1;
        $update_query = "UPDATE carts SET quantity = ? WHERE user_id = ? AND item_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iii", $new_quantity, $user_id, $item_id);
        $update_stmt->execute();
    } else {
        // Insert new item
        $insert_query = "INSERT INTO carts (user_id, item_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $quantity = 1;
        $insert_stmt->bind_param("iii", $user_id, $item_id, $quantity);
        $insert_stmt->execute();
    }
    header("Location: cart.php");
    exit();
}

// Update Quantity
if (isset($_GET['update_quantity'])) {
    $item_id = (int)$_GET['update_quantity'];
    $new_quantity = (int)$_GET['quantity'];

    if ($new_quantity > 0) {
        $update_query = "UPDATE carts SET quantity = ? WHERE user_id = ? AND item_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iii", $new_quantity, $user_id, $item_id);
        $update_stmt->execute();
    }
    header("Location: cart.php");
    exit();
}

// Remove Item
if (isset($_GET['remove_item'])) {
    $item_id = (int)$_GET['remove_item'];
    $delete_query = "DELETE FROM carts WHERE user_id = ? AND item_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $user_id, $item_id);
    $delete_stmt->execute();
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'header.php'; ?>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Shopping Cart</h2>
    
    <div class="row">
        <div class="col-md-8">
            <?php
           $query = "SELECT c.*, m.item_name, m.price, m.item_image 
           FROM carts c 
           JOIN menu_items m ON c.item_id = m.id 
           WHERE c.user_id = ?";
 
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($item = $result->fetch_assoc()):
            ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                        <div class="col-md-3">
                        <img src="<?php echo 'uploads/menu_items/' . htmlspecialchars($item['item_image']); ?>" alt="Product Image" class="img-fluid rounded">
                        </div>
                            <div class="col-md-7">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                                <p class="text-danger fw-bold">$<?php echo number_format($item['price'], 2); ?></p>
                                <div class="d-flex align-items-center">
                                    <a href="?update_quantity=<?php echo $item['item_id']; ?>&quantity=<?php echo $item['quantity'] - 1; ?>" class="btn btn-outline-secondary btn-sm me-2">-</a>
                                    <span><?php echo $item['quantity']; ?></span>
                                    <a href="?update_quantity=<?php echo $item['item_id']; ?>&quantity=<?php echo $item['quantity'] + 1; ?>" class="btn btn-outline-secondary btn-sm ms-2">+</a>
                                    <a href="?remove_item=<?php echo $item['item_id']; ?>" class="btn btn-danger btn-sm ms-3">Remove</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="alert alert-info">Your cart is empty.</div>
            <?php endif; ?>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Order Summary</h5>
                    <hr>
                    <?php
                    $subtotal = 0;
                    mysqli_data_seek($result, 0); // Reset result pointer
                    while ($item = $result->fetch_assoc()) {
                        $subtotal += $item['price'] * $item['quantity'];
                    }
                    $tax = $subtotal * 0.08; // 8% Tax
                    $total = $subtotal + $tax;
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (8%):</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong>$<?php echo number_format($total, 2); ?></strong>
                    </div>
                   
                   
                    <a href="checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
