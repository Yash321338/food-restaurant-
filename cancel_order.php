<?php
session_start();
include 'config/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if order ID is provided
if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    
    // Verify the order belongs to the user
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $order = $result->fetch_assoc();
        
        // Only allow cancellation if the order is pending
        if ($order['status'] === 'pending') {
            // Update order status to canceled
            $update_query = "UPDATE orders SET status = 'canceled' WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $order_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success_message'] = "Order #$order_id has been successfully canceled.";
            } else {
                $_SESSION['error_message'] = "Failed to cancel the order. Please try again.";
            }
        } else {
            $_SESSION['error_message'] = "This order cannot be canceled because it is already " . $order['status'] . ".";
        }
    } else {
        $_SESSION['error_message'] = "Invalid order or you don't have permission to cancel this order.";
    }
} else {
    $_SESSION['error_message'] = "Order ID not provided.";
}

header("Location: order_history.php");
exit();
?>