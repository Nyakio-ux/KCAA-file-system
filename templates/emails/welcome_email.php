<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our System</title>
</head>
<body>
    <h1>Welcome, {{name}}!</h1>
    <p>Your account has been successfully created with the following details:</p>
    
    <ul>
        <li><strong>Username:</strong> {{username}}</li>
        <li><strong>Login URL:</strong> <a href="{{login_link}}">{{login_link}}</a></li>
    </ul>
    
    <p>Please keep your login credentials secure and don't share them with anyone.</p>
    
    <p>If you have any questions or need assistance, please contact our support team at 
    <a href="mailto:{{support_email}}">{{support_email}}</a>.</p>
    
    <p>Best regards,<br>The System Team</p>
</body>
</html>