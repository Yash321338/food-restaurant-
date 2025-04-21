<?php
session_start();
require_once 'config/db_connect.php';

// Registration validation function
function validateRegistration($username, $email, $password, $confirm_password) {
    $errors = [];

    // Username validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    return $errors;
}

// Handle file upload
function handleProfileImageUpload() {
    if (!isset($_FILES["profile_image"]) || $_FILES["profile_image"]["error"] != 0) {
        return null;
    }

    $allowed = [
        "jpg" => "image/jpg", 
        "jpeg" => "image/jpeg", 
        "png" => "image/png", 
        "gif" => "image/gif"
    ];

    $filename = $_FILES["profile_image"]["name"];
    $filetype = $_FILES["profile_image"]["type"];
    $filesize = $_FILES["profile_image"]["size"];

    // Validate file extension
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!array_key_exists($ext, $allowed)) {
        return ["error" => "Invalid file type. Please upload JPG, JPEG, PNG, or GIF."];
    }

    // Check file size (5MB max)
    $maxsize = 5 * 1024 * 1024;
    if ($filesize > $maxsize) {
        return ["error" => "File size is larger than 5MB."];
    }

    // Create upload directory if not exists
    $upload_dir = "uploads/profile_images/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $new_filename = uniqid() . "_" . bin2hex(random_bytes(8)) . "." . $ext;
    $destination = $upload_dir . $new_filename;

    // Move uploaded file
    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $destination)) {
        return $destination;
    }

    return ["error" => "File upload failed."];
}

// Main registration process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : "";

    // Validate registration
    $errors = validateRegistration($username, $email, $password, $confirm_password);

    // Handle profile image upload
    $profile_image = handleProfileImageUpload();
    if (is_array($profile_image) && isset($profile_image['error'])) {
        $errors[] = $profile_image['error'];
    }

    // Check for existing username or email
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $errors[] = "Username or email already exists.";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Generate email verification token
        $verification_token = bin2hex(random_bytes(32));

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

        // Prepare and execute insert statement
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, profile_image, verification_token) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $hashed_password, $full_name, $profile_image, $verification_token);

        if ($stmt->execute()) {
            // Send verification email (implement email sending logic)
            sendVerificationEmail($email, $verification_token);

            // Set success message
            $_SESSION['registration_success'] = "Registration successful! Please check your email to verify your account.";
            
            // Redirect to login page
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

// Email verification function (placeholder - implement actual email sending)
function sendVerificationEmail($email, $token) {
    $verification_link = "http://yourdomain.com/verify_email.php?token=" . $token;
    
    // Use PHP's mail() or a library like PHPMailer
    $subject = "Verify Your Email";
    $message = "Click the following link to verify your email: " . $verification_link;
    $headers = "From: noreply@yourdomain.com";

    // Uncomment and configure as needed
    // mail($email, $subject, $message, $headers);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account</title>
    <?php include 'header.php'; ?>
    
    <style>
        /* Register Page Styles */
        .register-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .register-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            min-height: 600px;
        }

        .register-image {
            flex: 1;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/register-bg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            min-height: 100%;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .register-image-content {
            position: relative;
            z-index: 1;
        }

        .register-image h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .register-image p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 80%;
            margin: 0 auto;
        }

        .register-form {
            flex: 1;
            padding: 40px;
            background: #fff;
        }

        .register-form h2 {
            color: #333;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
        }

        .form-control {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }

        .image-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 15px auto;
            display: block;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
        }

        .btn-primary {
            padding: 12px 30px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .login-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-danger {
            background-color: #fff2f2;
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background-color: #f0fff0;
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        /* Custom file input styling */
        .custom-file-input {
            position: relative;
            width: 100%;
        }

        .custom-file-label {
            background-color: #f8f9fa;
            border: 1px dashed #ddd;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-file-label:hover {
            background-color: #e9ecef;
            border-color: #007bff;
        }

        .password-requirements {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Password strength indicator */
        .password-strength {
            height: 5px;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
        }

        .password-strength-value {
            height: 100%;
            border-radius: 5px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-card {
                flex-direction: column;
            }

            .register-image {
                min-height: 200px;
            }

            .register-form {    
                padding: 30px 20px;
            }

            .register-form h2 {
                font-size: 1.8rem;
                margin-bottom: 20px;
            }

            .image-preview {
                width: 100px;
                height: 100px;
            }
        }

        /* Additional Utility Classes */
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .text-center {
            text-align: center !important;
        }

        .w-100 {
            width: 100% !important;
        }
    </style>
</head>
<body class="register-page">
    <div class="container register-container">
        <div class="register-card">
            <!-- Left side decorative image with content -->
            <div class="register-image">
                <div class="register-image-content">
                    <h2>Join Our Community</h2>
                    <p>Create an account to access exclusive features and personalize your experience.</p>
                </div>
            </div>
            
            <!-- Right side registration form -->
            <div class="register-form">
                <h2>Create Account</h2>

                <?php if (isset($errors) && !empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                            value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required
                            value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required
                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required
                            oninput="checkPasswordStrength(this.value)">
                        <div class="password-strength mt-2">
                            <div class="password-strength-value" id="password-strength-meter"></div>
                        </div>
                        <div class="password-requirements">
                            Password must be at least 8 characters with uppercase, lowercase, and number
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="mb-4">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <div class="custom-file-input">
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <img id="preview" class="image-preview" src="images/default-avatar.png" style="display: none;">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>

                    <p class="text-center mb-0">
                        Already have an account? <a href="login.php" class="login-link">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
    function previewImage(input) {
        const preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Password strength checker
    function checkPasswordStrength(password) {
        const meter = document.getElementById('password-strength-meter');
        let strength = 0;
        
        // Check length
        if (password.length >= 8) strength += 25;
        
        // Check for uppercase
        if (/[A-Z]/.test(password)) strength += 25;
        
        // Check for lowercase
        if (/[a-z]/.test(password)) strength += 25;
        
        // Check for numbers
        if (/[0-9]/.test(password)) strength += 25;
        
        // Update the strength meter
        meter.style.width = strength + '%';
        
        // Change color based on strength
        if (strength < 50) {
            meter.style.backgroundColor = '#dc3545'; // red
        } else if (strength < 75) {
            meter.style.backgroundColor = '#ffc107'; // yellow
        } else {
            meter.style.backgroundColor = '#28a745'; // green
        }
    }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>