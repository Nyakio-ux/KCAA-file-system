<!DOCTYPE html>
<html>
<head>
    <title>Invitation to Join Our System</title>
</head>
<body>
    <h2>You're Invited!</h2>
    
    <p>You've been invited to join our system. Please click the link below to accept the invitation 
    and create your account:</p>
    
    <p><a href="{{invite_link}}">Accept Invitation</a></p>
    
    {% if message %}
    <p><strong>Message from inviter:</strong><br>
    {{message}}</p>
    {% endif %}
    
    <p>This invitation will expire in {{expiration_days}} days.</p>
    
    <p>If you didn't expect this invitation, you can safely ignore this email.</p>
    
    <p>Best regards,<br>The System Team</p>
</body>
</html>