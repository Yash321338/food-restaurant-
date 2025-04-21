<?php
$otp = '123456'; // Replace with your dynamic OTP generation logic
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 30px;
        }
        .header {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .otp-code {
            background-color: #f5f7fa;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 3px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset Request</h2>
        </div>
        
        <p>Hello,</p>
        
        <p>You recently requested to reset your password. Please use the following One-Time Password (OTP) to proceed:</p>
        
        <div class="otp-code"><?php echo htmlspecialchars($otp); ?></div>
        
        <p>This OTP will expire in 10 minutes. If you didn't request this password reset, please ignore this email or contact support if you have concerns.</p>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Your Company Name. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
