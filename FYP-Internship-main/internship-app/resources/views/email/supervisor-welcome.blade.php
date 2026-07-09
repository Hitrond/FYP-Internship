<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { color: #4f46e5; }
        .content { background: #fff; padding: 20px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .credentials { background: #f1f5f9; padding: 15px; border-radius: 6px; margin: 20px 0; font-family: monospace; font-size: 16px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome to InternTrack!</h2>
        </div>
        <div class="content">
            <p>Dear {{ $supervisor->name }},</p>
            <p>You have been registered as the Industrial Supervisor for <strong>{{ $studentName }}</strong>.</p>
            <p>To evaluate the student's weekly logbooks and final performance, please log in to the portal using the credentials below:</p>
            
            <div class="credentials">
                <strong>Login URL:</strong> {{ url('/login') }}<br><br>
                <strong>Email:</strong> {{ $supervisor->email }}<br>
                <strong>Password:</strong> {{ $rawPassword }}
            </div>

            <p><em>For security reasons, we strongly recommend changing this password from your Profile page after your first login.</em></p>
            
            <center>
                <a href="{{ url('/login') }}" class="btn">Log In Now</a>
            </center>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>