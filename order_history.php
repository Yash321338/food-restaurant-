<?php
session_start();
include 'config/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for the user
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];

while ($order = $result->fetch_assoc()) {
    $orders[] = $order;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'header.php'; ?>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Order History</h2>
    
    <?php if (count($orders) > 0): ?>
        <div class="row">
            <div class="col-12">
                <?php foreach ($orders as $order): ?>
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order #<?php echo $order['id']; ?></h5>
                            <span class="badge bg-<?php 
                                echo $order['status'] === 'completed' ? 'success' : 
                                    ($order['status'] === 'canceled' ? 'danger' : 'warning'); 
                            ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?>, 
                                        <?php echo htmlspecialchars($order['shipping_city']); ?>, 
                                        <?php echo htmlspecialchars($order['shipping_state']); ?> 
                                        <?php echo htmlspecialchars($order['shipping_zip']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <?php
                            // Fetch order items
                            $items_query = "SELECT oi.*, m.item_name 
                                          FROM order_items oi 
                                          JOIN menu_items m ON oi.item_id = m.id 
                                          WHERE oi.order_id = ?";
                            $items_stmt = $conn->prepare($items_query);
                            $items_stmt->bind_param("i", $order['id']);
                            $items_stmt->execute();
                            $items_result = $items_stmt->get_result();
                            ?>
                            
                            <h6>Order Items:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = $items_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Tax (8%):</strong></td>
                                            <td>$<?php echo number_format($order['tax_amount'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <?php if ($order['status'] === 'pending'): ?>
                                <div class="text-end">
                                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel Order</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You haven't placed any orders yet. <a href="menu.php">Start shopping</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>