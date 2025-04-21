<?php
session_start();
include 'config/db_connect.php';

// Check if orders table exists, if not create it
$checkTable = $conn->query("SHOW TABLES LIKE 'orders'");
if ($checkTable->num_rows == 0) {
    $createTableSQL = "CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        tax_amount DECIMAL(10,2) NOT NULL,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        discount_code VARCHAR(50),
        status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
        shipping_address TEXT NOT NULL,
        shipping_city VARCHAR(100) NOT NULL,
        shipping_state VARCHAR(100) NOT NULL,
        shipping_zip VARCHAR(20) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        razorpay_payment_id VARCHAR(255),
        razorpay_order_id VARCHAR(255),
        razorpay_signature VARCHAR(255),
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if (!$conn->query($createTableSQL)) {
        die("Error creating orders table: " . $conn->error);
    }
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;
$coupon_applied = false;
$discount_amount = 0;
$discount_code = '';

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
if ($user_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch cart items for order summary
$query = "SELECT c.*, m.item_name, m.price 
        FROM carts c 
        JOIN menu_items m ON c.item_id = m.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate totals
$subtotal = 0;
$items = [];
while ($item = $result->fetch_assoc()) {
    $subtotal += $item['price'] * $item['quantity'];
    $items[] = $item;
}

// Handle coupon removal
if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['coupon']);
    $coupon_applied = false;
    $discount_amount = 0;
    $discount_code = '';
    
    // Recalculate totals
    $discounted_subtotal = $subtotal - $discount_amount;
    $tax = $discounted_subtotal * 0.08;
    $total = $discounted_subtotal + $tax;
    
    // Redirect to avoid form resubmission
    header("Location: checkout.php");
    exit();
}

// Add this function to validate coupons
function validateCoupon($conn, $code, $total) {
    $sql = "SELECT * FROM coupons WHERE code = ? AND status = 'active' 
            AND valid_from <= CURRENT_DATE AND valid_until >= CURRENT_DATE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['valid' => false, 'message' => 'Invalid or expired coupon code'];
    }
    
    $coupon = $result->fetch_assoc();
    
    // Check minimum purchase
    if ($total < $coupon['min_purchase']) {
        return [
            'valid' => false, 
            'message' => 'Minimum purchase amount of $' . $coupon['min_purchase'] . ' required'
        ];
    }
    
    // Check usage limit
    if ($coupon['usage_limit'] && $coupon['times_used'] >= $coupon['usage_limit']) {
        return ['valid' => false, 'message' => 'Coupon usage limit reached'];
    }
    
    // Calculate discount
    $discount = 0;
    if ($coupon['discount_type'] == 'percentage') {
        $discount = $total * ($coupon['discount_value'] / 100);
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    } else {
        $discount = $coupon['discount_value'];
    }
    
    return [
        'valid' => true,
        'discount' => $discount,
        'coupon' => $coupon
    ];
}

// Add this to your checkout form processing
if (isset($_POST['apply_coupon'])) {
    $coupon_code = trim($_POST['coupon_code']);
    $cart_total = $subtotal;  // Using the already calculated subtotal
    $coupon_result = validateCoupon($conn, $coupon_code, $cart_total);
    
    if ($coupon_result['valid']) {
        $_SESSION['coupon'] = [
            'code' => $coupon_code,
            'discount' => $coupon_result['discount']
        ];
        $success_message = "Coupon applied successfully!";
    } else {
        $error_message = $coupon_result['message'];
    }
}

// Restore coupon from session if it exists
if (!$coupon_applied && isset($_SESSION['coupon'])) {
    $coupon_applied = true;
    $discount_code = $_SESSION['coupon']['code'];
    $discount_amount = $_SESSION['coupon']['discount'];
}

// Apply discount to subtotal
$discounted_subtotal = $subtotal - $discount_amount;
$tax = $discounted_subtotal * 0.08; // 8% Tax
$total = $discounted_subtotal + $tax;

// Process Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate form inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $payment_method = 'razorpay'; // Force Razorpay payment method
    
    // Enhanced validation
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = "Name must be between 2 and 100 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    } elseif (strlen($address) < 5 || strlen($address) > 200) {
        $errors[] = "Address must be between 5 and 200 characters";
    }
    
    if (empty($city)) {
        $errors[] = "City is required";
    } elseif (!preg_match("/^[a-zA-Z\s\-']+$/", $city)) {
        $errors[] = "City name contains invalid characters";
    }
    
    if (empty($state)) {
        $errors[] = "State is required";
    } elseif (!preg_match("/^[a-zA-Z\s\-']+$/", $state)) {
        $errors[] = "State name contains invalid characters";
    }
    
    if (empty($zip)) {
        $errors[] = "ZIP code is required";
    } elseif (!preg_match("/^\d{5}(-\d{4})?$/", $zip)) {
        $errors[] = "Please enter a valid ZIP code (e.g. 12345 or 12345-6789)";
    }
    
    // If validation passes, create a Razorpay order
    if (empty($errors)) {
        require_once 'razorpay-php/Razorpay.php';
        
        // Replace with your Razorpay API credentials
        $api_key = 'YOUR_RAZORPAY_KEY_ID';
        $api_secret = 'YOUR_RAZORPAY_KEY_SECRET';
        
        $razorpay = new Razorpay\Api\Api($api_key, $api_secret);
        
        try {
            // Create a Razorpay order
            $orderData = [
                'receipt'         => 'order_rcptid_' . time(),
                'amount'          => $total * 100, // Razorpay expects amount in paise
                'currency'        => 'INR',
                'payment_capture' => 1 // Auto-capture payment
            ];
            
            $razorpayOrder = $razorpay->order->create($orderData);
            $razorpayOrderId = $razorpayOrder['id'];
            
            // Store order details in session for verification after payment
            $_SESSION['razorpay_order'] = [
                'order_id' => $razorpayOrderId,
                'user_id' => $user_id,
                'total' => $total,
                'tax' => $tax,
                'discount_amount' => $discount_amount,
                'discount_code' => $discount_code,
                'name' => $name,
                'email' => $email,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
                'items' => $items
            ];
            
            // Redirect to Razorpay payment page
            $response = [
                'key' => $api_key,
                'amount' => $orderData['amount'],
                'name' => "Your Restaurant Name",
                'description' => "Order Payment",
                'image' => "https://your-restaurant.com/logo.png",
                'prefill' => [
                    'name' => $name,
                    'email' => $email,
                    'contact' => $user['phone'] ?? ''
                ],
                'notes' => [
                    'address' => $address,
                    'merchant_order_id' => $razorpayOrderId
                ],
                'theme' => [
                    'color' => "#F37254"
                ],
                'order_id' => $razorpayOrderId
            ];
            
            // This will be handled in the JavaScript part
            $_SESSION['razorpay_response'] = $response;
            
        } catch (Exception $e) {
            $errors[] = "Error creating Razorpay order: " . $e->getMessage();
        }
    }
}

// Handle Razorpay payment verification callback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    $payment_id = $_POST['razorpay_payment_id'];
    $razorpay_order_id = $_POST['razorpay_order_id'];
    $razorpay_signature = $_POST['razorpay_signature'];
    
    // Verify the payment signature
    $generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $payment_id, $api_secret);
    
    if ($generated_signature === $razorpay_signature) {
        // Payment is successful, create the order in database
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get order details from session
            $order_details = $_SESSION['razorpay_order'];
            
            // Insert order into database with Razorpay information
            $order_query = "INSERT INTO orders (user_id, total_amount, tax_amount, discount_amount, discount_code, status, 
                        shipping_address, shipping_city, shipping_state, shipping_zip, payment_method, 
                        razorpay_payment_id, razorpay_order_id, razorpay_signature, order_date) 
                        VALUES (?, ?, ?, ?, ?, 'completed', ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $order_stmt = $conn->prepare($order_query);
            
            if ($order_stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $order_stmt->bind_param("idddssssssssss", 
                $order_details['user_id'], 
                $order_details['total'], 
                $order_details['tax'], 
                $order_details['discount_amount'], 
                $order_details['discount_code'], 
                $order_details['address'], 
                $order_details['city'], 
                $order_details['state'], 
                $order_details['zip'], 
                'razorpay',
                $payment_id,
                $razorpay_order_id,
                $razorpay_signature
            );
            $result = $order_stmt->execute();
            
            if (!$result) {
                throw new Exception("Failed to create order: " . $order_stmt->error);
            }
            
            $order_id = $conn->insert_id;
            
            // Insert order items
            $item_query = "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_query);
            
            if ($item_stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            foreach ($order_details['items'] as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['item_id'], $item['quantity'], $item['price']);
                $result = $item_stmt->execute();
                
                if (!$result) {
                    throw new Exception("Failed to add items to order: " . $item_stmt->error);
                }
            }
            
            // Clear cart and coupon from session
            $clear_query = "DELETE FROM carts WHERE user_id = ?";
            $clear_stmt = $conn->prepare($clear_query);
            if ($clear_stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $clear_stmt->bind_param("i", $order_details['user_id']);
            $clear_stmt->execute();
            
            // Clear session data
            unset($_SESSION['coupon']);
            unset($_SESSION['razorpay_order']);
            unset($_SESSION['razorpay_response']);
            
            // Commit transaction
            $conn->commit();
            
            // Set success flag
            $success = true;
            
            // Redirect to success page
            header("Location: order_success.php?order_id=" . $order_id);
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    } else {
        $errors[] = "Payment verification failed";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <?php include 'header.php'; ?>
</head>
<body>

<div class="container mt-5">
    <?php if ($success): ?>
        <div class="alert alert-success">
            <h4>Thank you for your order!</h4>
            <p>Your order has been successfully placed. An order confirmation has been sent to your email.</p>
            <a href="order_history.php" class="btn btn-primary mt-3">View Order History</a>
        </div>
    <?php else: ?>
        <h2 class="text-center mb-4">Checkout</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (count($items) > 0): ?>
            <form method="POST" action="" id="checkout-form" novalidate>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Shipping Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                            value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" 
                                            required 
                                            minlength="2" 
                                            maxlength="100">
                                        <div class="invalid-feedback">
                                            Please enter a valid name (2-100 characters).
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                            value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                            required
                                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                        required
                                        minlength="5"
                                        maxlength="200">
                                    <div class="invalid-feedback">
                                        Please enter a valid address (5-200 characters).
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                            required
                                            pattern="[a-zA-Z\s\-']+">
                                        <div class="invalid-feedback">
                                            Please enter a valid city name.
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                            required
                                            pattern="[a-zA-Z\s\-']+">
                                        <div class="invalid-feedback">
                                            Please enter a valid state name.
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="zip" class="form-label">ZIP Code</label>
                                        <input type="text" class="form-control" id="zip" name="zip" 
                                            required
                                            pattern="^\d{5}(-\d{4})?$">
                                        <div class="invalid-feedback">
                                            Please enter a valid ZIP code (e.g., 12345 or 12345-6789).
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Coupon Code</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($coupon_applied): ?>
                                    <div class="alert alert-success mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Coupon applied: <?php echo htmlspecialchars(strtoupper($discount_code)); ?></strong>
                                                <p class="mb-0">20% discount applied</p>
                                            </div>
                                            <form method="post" action="">
                                                <button type="submit" name="remove_coupon" class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="coupon-section mb-3">
                                        <h4>Have a Coupon?</h4>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="text" name="coupon_code" 
                                                   class="form-control" placeholder="Enter coupon code">
                                            <button type="submit" name="apply_coupon" 
                                                    class="btn btn-secondary">Apply Coupon</button>
                                        </form>
                                        <?php if (isset($error_message)): ?>
                                            <div class="alert alert-danger mt-2"><?php echo $error_message; ?></div>
                                        <?php endif; ?>
                                        <?php if (isset($success_message)): ?>
                                            <div class="alert alert-success mt-2"><?php echo $success_message; ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($items as $item): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><?php echo htmlspecialchars($item['item_name']); ?> x <?php echo $item['quantity']; ?></span>
                                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                
                                <?php if ($coupon_applied): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount (20%):</span>
                                    <span>-$<?php echo number_format($discount_amount, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discounted Subtotal:</span>
                                    <span>$<?php echo number_format($discounted_subtotal, 2); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Tax (8%):</span>
                                    <span>$<?php echo number_format($tax, 2); ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-4">
                                    <strong>Total:</strong>
                                    <strong>₹<?php echo number_format($total * 75, 2); ?></strong> <!-- Convert to INR for Razorpay -->
                                </div>
                                <button type="submit" name="place_order" class="btn btn-primary w-100">Proceed to Payment</button>
                                <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2">Back to Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="menu.php">Continue shopping</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Form validation and Razorpay integration
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    
    // Handle form submission
    form.addEventListener('submit', function(event) {
        // Only proceed if it's the place order button
        if (event.submitter && event.submitter.name === 'place_order') {
            event.preventDefault();
            
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            // Remove existing validation classes
            requiredFields.forEach(field => {
                field.classList.remove('is-invalid');
            });
            
            // Check each required field
            requiredFields.forEach(field => {
                if (field.value.trim() === '') {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field.pattern && !new RegExp(field.pattern).test(field.value)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            if (isValid) {
                // Submit form via AJAX to create Razorpay order
                fetch('checkout.php', {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        // Show error message
                        alert(data.error);
                    } else {
                        // Open Razorpay payment modal
                        const options = {
                            key: data.key,
                            amount: data.amount,
                            currency: 'INR',
                            name: data.name,
                            description: data.description,
                            image: data.image,
                            order_id: data.order_id,
                            handler: function(response) {
                                // Handle payment success
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = 'checkout.php';
                                
                                const paymentId = document.createElement('input');
                                paymentId.type = 'hidden';
                                paymentId.name = 'razorpay_payment_id';
                                paymentId.value = response.razorpay_payment_id;
                                form.appendChild(paymentId);
                                
                                const orderId = document.createElement('input');
                                orderId.type = 'hidden';
                                orderId.name = 'razorpay_order_id';
                                orderId.value = response.razorpay_order_id;
                                form.appendChild(orderId);
                                
                                const signature = document.createElement('input');
                                signature.type = 'hidden';
                                signature.name = 'razorpay_signature';
                                signature.value = response.razorpay_signature;
                                form.appendChild(signature);
                                
                                document.body.appendChild(form);
                                form.submit();
                            },
                            prefill: data.prefill,
                            notes: data.notes,
                            theme: data.theme
                        };
                        
                        const rzp = new Razorpay(options);
                        rzp.open();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            } else {
                // Scroll to the first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
        }
    });
});
</script>

</body>
</html>