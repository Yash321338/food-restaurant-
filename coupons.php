<?php
// Start session and include database connection
session_start();
include 'config/db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all coupons
$query = "SELECT * FROM coupons ORDER BY valid_until DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coupon Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Coupon Management</h2>
        <a href="add_coupon.php" class="btn btn-primary mb-3">Add New Coupon</a>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Valid From</th>
                    <th>Valid Until</th>
                    <th>Uses</th>
                    <th>Max Uses</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($coupon = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                        <td><?php echo $coupon['discount_percent']; ?>%</td>
                        <td><?php echo date('Y-m-d H:i', strtotime($coupon['valid_from'])); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($coupon['valid_until'])); ?></td>
                        <td><?php echo $coupon['use_count']; ?></td>
                        <td><?php echo $coupon['max_uses'] ?? 'Unlimited'; ?></td>
                        <td><?php echo $coupon['is_active'] ? 'Active' : 'Inactive'; ?></td>
                        <td>
                            <a href="edit_coupon.php?id=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete_coupon.php?id=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>