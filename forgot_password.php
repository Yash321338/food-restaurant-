<?php
// forgot_password.php - Password reset request page with enhanced security
session_start();
include "config/db_connect.php";
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Enhanced email validation
    $email_regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match($email_regex, $email)) {
        $message = "<div class='error'>Please enter a valid email address</div>";
    } else {
        // Check common disposable email domains
        $disposable_domains = array(
            'temp-mail.org', 'mailinator.com', 'guerrillamail.com', 'tempmail.com',
            'fakeinbox.com', 'sharklasers.com', 'trashmail.com', 'yopmail.com'
        );
        
        $email_domain = substr(strrchr($email, "@"), 1);
        $is_disposable = false;
        
        foreach ($disposable_domains as $domain) {
            if (stripos($email_domain, $domain) !== false) {
                $is_disposable = true;
                break;
            }
        }
        
        if ($is_disposable) {
            $message = "<div class='error'>Please use a permanent email address</div>";
        } else {
            // Check if email exists in database
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // IMPORTANT: Always show the same message regardless of whether email exists
            // This prevents account enumeration
            $message = "<div class='success'>If your email exists in our system, you will receive a password reset OTP shortly.</div>";
            
            if ($result->num_rows > 0) {
                // Generate a random 6-digit OTP
                $otp = sprintf("%06d", mt_rand(100000, 999999));
                $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
                
                // Update user record with OTP and expiry
                $updateStmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
                $updateStmt->bind_param("sss", $otp, $expiry, $email);
                
                if ($updateStmt->execute()) {
                    // Store email in session
                    $_SESSION['reset_email'] = $email;
                    
                    // Send email with OTP
                    $mail = new PHPMailer(true);
                    
                    try {
                        // Server settings - load from config file instead of hardcoding
                        $config = include('config/mail_config.php');
                        
                        $mail->isSMTP();
                        $mail->Host = $config['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $config['username'];
                        $mail->Password = $config['password'];
                        $mail->SMTPSecure = $config['encryption'];
                        $mail->Port = $config['port'];
                        
                        // Recipients
                        $mail->setFrom($config['from_email'], $config['from_name']);
                        $mail->addAddress($email);
                        
                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset OTP';
                        $mail->Body = "
                            <html>
                            <body style='font-family: Arial, sans-serif;'>
                                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                                    <h2 style='color: #333;'>Password Reset Request</h2>
                                    <p>Your One-Time Password (OTP) for password reset is:</p>
                                    <h3 style='background-color: #f5f5f5; padding: 10px; text-align: center; font-size: 24px;'>{$otp}</h3>
                                    <p>This OTP will expire in 10 minutes.</p>
                                    <p>If you didn't request this password reset, please ignore this email.</p>
                                </div>
                            </body>
                            </html>
                        ";
                        
                        $mail->send();
                        
                        // Log only non-sensitive information
                        error_log("Password reset request for email: " . sha1($email) . " at " . date("Y-m-d H:i:s"));
                        
                        header("Location: verify_otp.php");
                        exit();
                    } catch (Exception $e) {
                        // Do not expose error details to user
                        error_log("Email sending error: " . $mail->ErrorInfo);
                        // Keep the same message to prevent information disclosure
                    }
                } else {
                    // Don't change the message - keep it generic
                    error_log("Database update error for password reset: " . $conn->error);
                }
                $updateStmt->close();
            } else {
                // For non-existent emails, just log a generic entry
                error_log("Password reset attempted for non-existent email at " . date("Y-m-d H:i:s"));
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
    <title>Forgot Password</title>
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
        .email-requirements {
            font-size: 12px;
            color: #666;
            margin: 5px 0 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Forgot Password</h2>
            <?php if (!empty($message)) echo $message; ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="forgotForm">
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
                <div class="email-requirements">Please enter a valid email address (e.g., user@example.com)</div>
                <button type="submit">Send Reset OTP</button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>
    
    <script>
        // Client-side email validation
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
            }
            
            // Check for common domains (optional)
            const disallowedDomains = ['temp-mail.org', 'mailinator.com', 'guerrillamail.com', 'yopmail.com'];
            const emailDomain = email.split('@')[1];
            
            for (let domain of disallowedDomains) {
                if (emailDomain.includes(domain)) {
                    e.preventDefault();
                    alert('Please use a permanent email address');
                    break;
                }
            }
        });
    </script>
</body>
</html>