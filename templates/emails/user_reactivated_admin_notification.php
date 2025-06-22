<!DOCTYPE html>
<html>
<head>
    <title>User Account Reactivated</title>
</head>
<body>
    <h2>User Account Reactivated</h2>
    <p>Hello {{admin_name}},</p>
    
    <p>The following user account has been reactivated:</p>
    
    <ul>
        <li><strong>Name:</strong> {{user_name}}</li>
        <li><strong>Email:</strong> {{user_email}}</li>
        <li><strong>Reactivated At:</strong> {{reactivation_time}}</li>
    </ul>
    
    <p>You can manage this account through the admin dashboard.</p>
    
    <p>Best regards,<br>The System Team</p>
</body>
</html>