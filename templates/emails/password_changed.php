<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Changed Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { margin-top: 20px; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>KCAA SmartFiles - Password Changed</h2>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <p>This is a notification that the password for your KCAA SmartFiles account was successfully changed.</p>
            
            <p>If you made this change, no further action is required.</p>
            
            <p>If you did not change your password, please reset your password immediately or contact support.</p>
        </div>
        
        <div class="footer">
            <p>This email was sent by KCAA SmartFiles system. Please do not reply to this email.</p>
            <p>© <?php echo date('Y'); ?> Kenya Civil Aviation Authority. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
