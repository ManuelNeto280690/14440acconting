<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Account Created</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .credentials { background: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Account Created</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>Your admin account has been created successfully!</p>
            
            <div class="credentials">
                <h3>Login Details:</h3>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Password:</strong> {{ $temporaryPassword }}</p>
                <p><strong>Role:</strong> {{ ucfirst($role->name) }}</p>
                <p><strong>Login URL:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
            </div>
            
            <p><strong>Important:</strong> Please change your password after first login.</p>
            
            <p style="text-align: center;">
                <a href="{{ $loginUrl }}" class="btn">Login Now</a>
            </p>
            
            <p>Best regards,<br>{{ config('app.name') }} Team</p>
        </div>
    </div>
</body>
</html>