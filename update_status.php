<?php
session_start();

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve values from the form submission
    $order_id   = isset($_POST['order_id']) ? $_POST['order_id'] : '';
    $new_status = isset($_POST['status']) ? $_POST['status'] : '';

    // Normally, you would update the order status in the database here.
    // For demonstration purposes, we simply simulate a successful update.
    $message = "Order #{$order_id} status updated to <strong>{$new_status}</strong> (simulated).";
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Update Order Status</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-4">
            <div class="alert alert-success"><?php echo $message; ?></div>
            <a href="manage_orders.php" class="btn btn-primary">Back to Orders</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// If the form has not been submitted, display the update status form.
// The order ID should be passed in the URL as a GET parameter (e.g., update_status.php?order_id=1001)
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

//if (empty($order_id)) {
  //  echo "<div class='container mt-4'><div class='alert alert-danger'>Order ID is missing.</div></div>";
    //exit();
//}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Update Order Status</h1>
        <form method="POST" action="update_status.php">
            <!-- Hidden field to pass the order ID -->
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            <div class="mb-3">
                <label for="status" class="form-label">Select New Status:</label>
                <select class="form-select" name="status" id="status">
                    <option value="New Order">New Order</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Ready for Pickup">Ready for Pickup</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Update Status</button>
            <a href="manage_orders.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
