<?php
// verify_otp.php - OTP verification page with enhanced security
session_start();
require_once "config/db_connect.php";

$message = "";
$max_attempts = 3; // Maximum OTP verification attempts

// Check if email is set in session
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

// Initialize attempt counter if not set
if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check for too many attempts
    if ($_SESSION['otp_attempts'] >= $max_attempts) {
        $message = "<div class='error'>Too many failed attempts. Please request a new OTP.</div>";
        // Reset OTP in database after too many failed attempts
        $resetStmt = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE email = ?");
        $resetStmt->bind_param("s", $email);
        $resetStmt->execute();
        $resetStmt->close();
    } else {
        $otp = trim($_POST['otp']);
        
        // Strict OTP validation - must be exactly 6 digits
        if (empty($otp) || !ctype_digit($otp) || strlen($otp) != 6) {
            $message = "<div class='error'>Please enter a valid 6-digit OTP</div>";
            $_SESSION['otp_attempts']++;
        } else {
            // Verify OTP
            $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $db_otp = $row['otp'];
                $expiry = strtotime($row['otp_expiry']);
                $current = time();
                
                if ($db_otp === $otp && $current <= $expiry) {
                    // OTP is valid and not expired
                    $_SESSION['otp_verified'] = true;
                    
                    // Reset attempt counter on success
                    unset($_SESSION['otp_attempts']);
                    
                    // Log only non-sensitive information
                    error_log("OTP verification successful for user at " . date("Y-m-d H:i:s"));
                    
                    header("Location: reset_password.php");
                    exit();
                } else if ($current > $expiry) {
                    $message = "<div class='error'>OTP has expired. Please request a new one.</div>";
                    
                    // Reset expired OTP in database
                    $resetStmt = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE email = ?");
                    $resetStmt->bind_param("s", $email);
                    $resetStmt->execute();
                    $resetStmt->close();
                } else {
                    $message = "<div class='error'>Invalid OTP. Please try again. Attempts remaining: " . ($max_attempts - $_SESSION['otp_attempts'] - 1) . "</div>";
                    $_SESSION['otp_attempts']++;
                    
                    // Log attempt without revealing sensitive info
                    error_log("Failed OTP attempt from IP " . $_SERVER['REMOTE_ADDR'] . " - Attempt #" . $_SESSION['otp_attempts']);
                }
            } else {
                $message = "<div class='error'>User information not found. Please request a new OTP.</div>";
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
    <title>Verify OTP</title>
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
            text-align: center;
            letter-spacing: 5px;
            font-weight: bold;
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
        p {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .timer {
            text-align: center;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Verify OTP</h2>
            <?php if (!empty($message)) echo $message; ?>
            <p>We've sent a 6-digit code to your email</p>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required autocomplete="off">
                <div class="timer" id="otpTimer">OTP expires in: <span id="countdown">10:00</span></div>
                <button type="submit">Verify OTP</button>
            </form>
            <p>Didn't receive the code? <a href="forgot_password.php">Request again</a></p>
        </div>
    </div>
    
    <script>
        // OTP countdown timer
        function startCountdown() {
            let timeLeft = 10 * 60; // 10 minutes in seconds
            const countdownEl = document.getElementById('countdown');
            
            const timer = setInterval(function() {
                const minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                
                countdownEl.textContent = minutes + ':' + seconds;
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    countdownEl.textContent = "Expired";
                    countdownEl.style.color = "#f44336";
                }
                timeLeft--;
            }, 1000);
        }
        
        // Input validation for OTP
        const otpInput = document.querySelector('input[name="otp"]');
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 6) {
                this.value = this.value.substring(0, 6);
            }
        });
        
        // Start the countdown when the page loads
        window.onload = startCountdown;
    </script>
</body>
</html>