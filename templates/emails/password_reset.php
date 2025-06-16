<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .button { 
            display: inline-block; padding: 10px 20px; background-color: #007bff; 
            color: white; text-decoration: none; border-radius: 4px; 
        }
        .footer { margin-top: 20px; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>KCAA SmartFiles - Password Reset</h2>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <p>We received a request to reset your password for your KCAA SmartFiles account.</p>
            
            <p>To reset your password, please click the button below:</p>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="<?php echo htmlspecialchars($reset_link); ?>" class="button">Reset Password</a>
            </p>
            
            <p>If you didn't request this password reset, you can safely ignore this email. 
               The reset link will expire in <?php echo htmlspecialchars($expiry_time); ?>.</p>
            
            <p>For security reasons, we recommend that you don't share this email with anyone.</p>
        </div>
        
        <div class="footer">
            <p>This email was sent by KCAA SmartFiles system. Please do not reply to this email.</p>
            <p>© <?php echo date('Y'); ?> Kenya Civil Aviation Authority. All rights reserved.</p>
        </div>
    </div>
</body>
</html>