<?php
session_start();
include "config/db_connect.php";

// Create coupons table
$createTableSQL = "CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_purchase DECIMAL(10,2) DEFAULT 0,
    max_discount DECIMAL(10,2),
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    usage_limit INT DEFAULT NULL,
    times_used INT DEFAULT 0,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createTableSQL)) {
    die("Error creating coupons table: " . $conn->error);
}

// Function to create a coupon
function createCoupon($conn, $data) {
    $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_purchase, 
            max_discount, valid_from, valid_until, usage_limit, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdddssss", 
        $data['code'],
        $data['discount_type'],
        $data['discount_value'],
        $data['min_purchase'],
        $data['max_discount'],
        $data['valid_from'],
        $data['valid_until'],
        $data['usage_limit'],
        $data['status']
    );
    
    return $stmt->execute();
}

// Function to get all coupons
function getAllCoupons($conn) {
    $sql = "SELECT * FROM coupons ORDER BY created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_coupon'])) {
        $couponData = [
            'code' => strtoupper($_POST['code']),
            'discount_type' => $_POST['discount_type'],
            'discount_value' => $_POST['discount_value'],
            'min_purchase' => $_POST['min_purchase'],
            'max_discount' => $_POST['max_discount'],
            'valid_from' => $_POST['valid_from'],
            'valid_until' => $_POST['valid_until'],
            'usage_limit' => $_POST['usage_limit'],
            'status' => 'active'
        ];
        
        if (createCoupon($conn, $couponData)) {
            $success_message = "Coupon created successfully!";
        } else {
            $error_message = "Error creating coupon.";
        }
    }
}

$coupons = getAllCoupons($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupon Management</title>
    <?php include 'admin_sidebar.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .coupon-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .coupon-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #dc3545;
        }
        .status-expired {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="content">
        <h2>Coupon Management</h2>
        
        <!-- Coupon Creation Form -->
        <div class="content">
            <h3>Create New Coupon</h3>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Coupon Code</label>
                        <input type="text" name="code" class="form-control" required 
                               pattern="[A-Za-z0-9]+" title="Only letters and numbers allowed">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Discount Type</label>
                        <select name="discount_type" class="form-control" required>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Discount Value</label>
                        <input type="number" name="discount_value" class="form-control" 
                               step="0.01" min="0" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Minimum Purchase</label>
                        <input type="number" name="min_purchase" class="form-control" 
                               step="0.01" min="0" value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Maximum Discount</label>
                        <input type="number" name="max_discount" class="form-control" 
                               step="0.01" min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Usage Limit</label>
                        <input type="number" name="usage_limit" class="form-control" 
                               min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Valid From</label>
                        <input type="date" name="valid_from" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Valid Until</label>
                        <input type="date" name="valid_until" class="form-control" required>
                    </div>
                </div>
                <button type="submit" name="create_coupon" class="btn btn-primary">
                    Create Coupon
                </button>
            </form>
        </div>

        <!-- Coupons List -->
        <div class="coupon-list">
            <h3>Active Coupons</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Valid Until</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                        <td>
                            <?php 
                            echo $coupon['discount_type'] == 'percentage' 
                                ? htmlspecialchars($coupon['discount_value']) . '%'
                                : '$' . htmlspecialchars($coupon['discount_value']);
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($coupon['valid_until']); ?></td>
                        <td>
                            <?php 
                            echo htmlspecialchars($coupon['times_used']);
                            if ($coupon['usage_limit']) {
                                echo ' / ' . htmlspecialchars($coupon['usage_limit']);
                            }
                            ?>
                        </td>
                        <td>
                            <span class="status-<?php echo $coupon['status']; ?>">
                                <?php echo ucfirst(htmlspecialchars($coupon['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning">Edit</button>
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 