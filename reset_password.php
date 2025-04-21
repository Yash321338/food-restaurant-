<?php
// reset_password.php - New password setup page with enhanced security
session_start();
require_once "config/db_connect.php";

$message = "";

// Check if user has verified OTP
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Comprehensive password validation
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);
    
    // Validate password complexity
    if (strlen($password) < 8) {
        $message = "<div class='error'>Password must be at least 8 characters long</div>";
    } elseif (!$uppercase || !$lowercase || !$number || !$specialChars) {
        $message = "<div class='error'>Password must include uppercase, lowercase, number, and special character</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='error'>Passwords do not match</div>";
    } else {
        // Check if password is common or easily guessable
        $common_passwords = array('Password123!', 'Qwerty123!', 'Admin123!', 'Welcome1!', 'P@ssw0rd');
        if (in_array($password, $common_passwords)) {
            $message = "<div class='error'>Please choose a stronger password</div>";
        } else {
            // Hash password with strong algorithm and update in database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
            
            $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            
            if ($stmt->execute()) {
                // Clear session variables
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);
                
                // Log non-sensitive information
                error_log("Password reset completed for user at " . date("Y-m-d H:i:s"));
                
                $message = "<div class='success'>Password has been reset successfully! Redirecting to login...</div>";
                echo "<script>setTimeout(function() { window.location.href = 'login.php'; }, 3000);</script>";
            } else {
                $message = "<div class='error'>Error updating password. Please try again.</div>";
                error_log("Password reset error: " . $conn->error);
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .password-rules {
            font-size: 14px;
            color: #666;
            margin: 10px 0;
            padding-left: 20px;
        }
        .password-rules li {
            margin-bottom: 5px;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
            text-align: center;
        }
        .success {
            color: #4CAF50;
            margin-bottom: 15px;
            text-align: center;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .very-weak { background-color: #f44336; width: 20%; }
        .weak { background-color: #ff9800; width: 40%; }
        .medium { background-color: #ffeb3b; width: 60%; }
        .strong { background-color: #8bc34a; width: 80%; }
        .very-strong { background-color: #4CAF50; width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Reset Password</h2>
            <?php if (!empty($message)) echo $message; ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="resetForm">
                <input type="password" name="password" id="password" placeholder="Enter new password" required>
                <div class="password-strength" id="passwordStrength"></div>
                <ul class="password-rules">
                    <li id="length">At least 8 characters</li>
                    <li id="uppercase">At least one uppercase letter</li>
                    <li id="lowercase">At least one lowercase letter</li>
                    <li id="number">At least one number</li>
                    <li id="special">At least one special character</li>
                </ul>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>
    
    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const lengthRule = document.getElementById('length');
        const uppercaseRule = document.getElementById('uppercase');
        const lowercaseRule = document.getElementById('lowercase');
        const numberRule = document.getElementById('number');
        const specialRule = document.getElementById('special');
        const strengthIndicator = document.getElementById('passwordStrength');
        
        // Live password validation
        password.addEventListener('input', function() {
            const value = password.value;
            
            // Check each requirement
            const isLengthValid = value.length >= 8;
            const hasUppercase = /[A-Z]/.test(value);
            const hasLowercase = /[a-z]/.test(value);
            const hasNumber = /[0-9]/.test(value);
            const hasSpecial = /[^A-Za-z0-9]/.test(value);
            
            // Update visual indicators
            lengthRule.style.color = isLengthValid ? 'green' : '#666';
            uppercaseRule.style.color = hasUppercase ? 'green' : '#666';
            lowercaseRule.style.color = hasLowercase ? 'green' : '#666';
            numberRule.style.color = hasNumber ? 'green' : '#666';
            specialRule.style.color = hasSpecial ? 'green' : '#666';
            
            // Calculate password strength
            let strength = 0;
            if (isLengthValid) strength++;
            if (hasUppercase) strength++;
            if (hasLowercase) strength++;
            if (hasNumber) strength++;
            if (hasSpecial) strength++;
            
            // Update strength indicator
            strengthIndicator.className = 'password-strength';
            if (value.length === 0) {
                strengthIndicator.className += '';
            } else if (strength === 1) {
                strengthIndicator.className += ' very-weak';
            } else if (strength === 2) {
                strengthIndicator.className += ' weak';
            } else if (strength === 3) {
                strengthIndicator.className += ' medium';
            } else if (strength === 4) {
                strengthIndicator.className += ' strong';
            } else if (strength === 5) {
                strengthIndicator.className += ' very-strong';
            }
        });
        
        // Form submission validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const passwordValue = password.value;
            const confirmValue = confirmPassword.value;
            
            // Validate password
            if (passwordValue.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }
            
            // Check complexity
            const hasUppercase = /[A-Z]/.test(passwordValue);
            const hasLowercase = /[a-z]/.test(passwordValue);
            const hasNumber = /[0-9]/.test(passwordValue);
            const hasSpecial = /[^A-Za-z0-9]/.test(passwordValue);
            
            if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                e.preventDefault();
                alert('Password must include uppercase and lowercase letters, numbers, and special characters');
                return;
            }
            
            // Check if passwords match
            if (passwordValue !== confirmValue) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
        });
    </script>
</body>
</html>