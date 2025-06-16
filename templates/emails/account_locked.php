<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Locked Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .alert { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        .button { 
            display: inline-block; padding: 10px 20px; background-color: #dc3545; 
            color: white; text-decoration: none; border-radius: 4px; 
        }
        .footer { margin-top: 20px; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>KCAA SmartFiles - Account Locked</h2>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <div class="alert">
                <p><strong>Important:</strong> Your KCAA SmartFiles account has been temporarily locked.</p>
            </div>
            
            <p>This action was taken because we detected multiple failed login attempts to your account. 
               This is a security measure to protect your account from unauthorized access.</p>
            
            <p>To unlock your account, please contact your system administrator or click the button below:</p>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="<?php echo htmlspecialchars($unlock_link); ?>" class="button">Request Account Unlock</a>
            </p>
            
            <p>If you believe this was done in error, please contact the KCAA IT Help Desk immediately.</p>
        </div>
        
        <div class="footer">
            <p>This email was sent by KCAA SmartFiles system. Please do not reply to this email.</p>
            <p>© <?php echo date('Y'); ?> Kenya Civil Aviation Authority. All rights reserved.</p>
        </div>
    </div>
</body>
</html>