@extends('emails.layout')

@section('content')
<h2>Welcome to Your New Account!</h2>

<p>Hello <strong>{{ $user->name }}</strong>,</p>

<p>Congratulations! Your account has been successfully created on our platform. We're excited to have you as part of our community.</p>

<h3>Your Account Details</h3>

<div class="credentials-box">
    <div class="credential-item">
        <span class="credential-label">Tenant Name:</span>
        <span class="credential-value">{{ $tenant->name }}</span>
    </div>
    <div class="credential-item">
        <span class="credential-label">Email Address:</span>
        <span class="credential-value">{{ $user->email }}</span>
    </div>
    <div class="credential-item">
        <span class="credential-label">Temporary Password:</span>
        <span class="credential-value">{{ $temporaryPassword }}</span>
    </div>
    <div class="credential-item">
        <span class="credential-label">Login URL:</span>
        <span class="credential-value">{{ $loginUrl }}</span>
    </div>
</div>

<div class="alert alert-warning">
    <strong>Important Security Notice:</strong><br>
    For your security, please change your password immediately after your first login. The temporary password provided above should only be used for your initial access.
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $loginUrl }}" class="btn">Access Your Account</a>
</div>

<h3>What's Next?</h3>

<p>Once you log in, you'll be able to:</p>
<ul style="margin-left: 20px; margin-bottom: 20px;">
    <li>Set up your profile and preferences</li>
    <li>Configure your accounting integrations</li>
    <li>Upload and manage documents</li>
    <li>Create and send invoices</li>
    <li>Access our AI-powered features</li>
</ul>

<div class="alert alert-info">
    <strong>Need Help?</strong><br>
    If you have any questions or need assistance getting started, our support team is here to help. Don't hesitate to reach out!
</div>

<p>Thank you for choosing {{ config('app.name') }}. We look forward to helping you streamline your accounting processes.</p>

<p>Best regards,<br>
<strong>The {{ config('app.name') }} Team</strong></p>
@endsection