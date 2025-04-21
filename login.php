<?php
session_start();
require_once 'config/db_connect.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$login_identifier = $password = "";
$error = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_identifier = trim($_POST['login_identifier']);
    $password = $_POST['password'];
    
    if (empty($login_identifier) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        // Check if the identifier is an email
        $is_email = filter_var($login_identifier, FILTER_VALIDATE_EMAIL);
        
        // Prepare SQL statement based on identifier type
        if ($is_email) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        }
        
        $stmt->bind_param("s", $login_identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Check if account is verified
                if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                    $error = "Please verify your email before logging in.";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    
                    // Redirect based on user type
                    if ($user['is_admin'] == 1) {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: profile.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = $is_email ? "Email not found" : "Username not found";
        }
        $stmt->close();
    }
}
?>
  
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restaurant Name</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF4B2B;
            --secondary-color: #FF416C;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --border-radius: 15px;
            --box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .login-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .login-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            display: flex;
            backdrop-filter: blur(10px);
        }

        .login-image {
            flex: 1;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('images/restaurant-bg.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 2rem;
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, var(--primary-color) 0%, transparent 100%);
            opacity: 0.6;
        }

        .login-image-content {
            position: relative;
            z-index: 1;
        }

        .login-image h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .login-image p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .login-form {
            flex: 1;
            padding: 3rem;
            background: white;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header h2 {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-floating > .form-control {
            padding: 1rem 0.75rem;
            height: 3.5rem;
            border: 2px solid #eee;
            border-radius: 10px;
        }

        .form-floating > label {
            padding: 1rem 0.75rem;
        }

        .btn-login {
            height: 3.5rem;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 10px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #eee;
        }

        .divider::before { left: 0; }
        .divider::after { right: 0; }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .social-btn {
            width: 3.5rem;
            height: 3.5rem;
            border: 2px solid #eee;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .register-link {
            text-align: center;
            color: #666;
            font-size: 0.95rem;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
            border: none;
            background: rgba(255, 75, 43, 0.1);
            color: var(--primary-color);
        }

        .form-floating .input-group {
            display: flex;
            align-items: center;
        }

        .form-floating .input-group-text {
            background: transparent;
            border: 2px solid #eee;
            border-right: none;
            padding: 1rem;
            color: #666;
            border-radius: 10px 0 0 10px;
        }

        .form-floating .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        @media (max-width: 991px) {
            .login-image {
                display: none;
            }
            
            .login-card {
                max-width: 500px;
                margin: 0 auto;
            }
            
            .login-form {
                padding: 2rem;
            }
        }

        @media (max-width: 576px) {
            .login-section {
                padding: 1rem;
            }
            
            .login-form {
                padding: 1.5rem;
            }
            
            .login-header h2 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<section class="login-section">
    <div class="container login-container">
        <div class="login-card">
            <div class="login-image">
                <div class="login-image-content">
                    <h2>Welcome Back!</h2>
                    <p>Sign in to access your account and continue your experience.</p>
                </div>
            </div>

            <div class="login-form">
                <div class="login-header">
                    <h2>Sign In</h2>
                    <p>Please enter your credentials to continue</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="login_identifier" class="form-label">Email or Username</label>
                        <input type="text" class="form-control" id="login_identifier" name="login_identifier" 
                               placeholder="Enter your email or username" required 
                               value="<?php echo isset($login_identifier) ? htmlspecialchars($login_identifier) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required>
                        
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">Remember me</label>
                    </div>

            
                <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </form>
            <div class="mb-3 d-flex justify-content-between">
    <label for="password" class="form-label">Password</label>
    <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
</div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

