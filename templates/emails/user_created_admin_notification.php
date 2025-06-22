<!DOCTYPE html>
<html>
<head>
    <title>New User Account Created</title>
</head>
<body>
    <h2>New User Account Created</h2>
    <p>Hello {{admin_name}},</p>
    
    <p>A new user account has been created with the following details:</p>
    
    <ul>
        <li><strong>Name:</strong> {{new_user_name}}</li>
        <li><strong>Email:</strong> {{new_user_email}}</li>
        <li><strong>Username:</strong> {{new_user_username}}</li>
        <li><strong>Created At:</strong> {{created_at}}</li>
    </ul>
    
    <p>You can manage user accounts through the admin dashboard.</p>
    
    <p>Best regards,<br>The System Team</p>
</body>
</html>