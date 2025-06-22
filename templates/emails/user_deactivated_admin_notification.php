<!DOCTYPE html>
<html>
<head>
    <title>User Account Deactivated</title>
</head>
<body>
    <h2>User Account Deactivated</h2>
    <p>Hello {{admin_name}},</p>
    
    <p>The following user account has been deactivated:</p>
    
    <ul>
        <li><strong>Name:</strong> {{user_name}}</li>
        <li><strong>Email:</strong> {{user_email}}</li>
        <li><strong>Deactivated At:</strong> {{deactivation_time}}</li>
    </ul>
    
    <p>You can reactivate this account through the admin dashboard if needed.</p>
    
    <p>Best regards,<br>The System Team</p>
</body>
</html>