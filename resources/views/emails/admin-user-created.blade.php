@extends('emails.layout')

@section('content')
<h2>Your Admin Account Has Been Created!</h2>

<p>Hello <strong>{{ $user->name }}</strong>,</p>

<p>An administrator account has been created for you on <strong>{{ config('app.name') }}</strong>. You now have access to the administrative panel with {{ $role->name }} privileges.</p>

<h3>Your Account Details</h3>

<div class="credentials-box">
    <div class="credential-item">
        <span class="credential-label">Full Name:</span>
        <span class="credential-value">{{ $user->name }}</span>
    </div>
    <div class="credential-item">
        <span class="credential-label">Email Address:</span>
        <span class="credential-value">{{ $user->email }}</span>
    </div>
    <div class="credential-item">
        <span class="credential-label">Role:</span>
        <span class="credential-value">{{ ucfirst($role->name) }}</span>
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
    <a href="{{ $loginUrl }}" class="btn">Access Admin Panel</a>
</div>

<h3>Your Permissions</h3>

<p>As a <strong>{{ ucfirst($role->name) }}</strong>, you have access to:</p>
<ul style="margin-left: 20px; margin-bottom: 20px;">
    @if($role->name === 'admin')
        <li>Full system administration</li>
        <li>User and role management</li>
        <li>Tenant management</li>
        <li>System settings and configuration</li>
        <li>All reports and analytics</li>
    @elseif($role->name === 'manager')
        <li>User management within your scope</li>
        <li>Client and document management</li>
        <li>Invoice creation and management</li>
        <li>Reports and analytics</li>
    @else
        <li>Basic user functions</li>
        <li>Document upload and management</li>
        <li>Invoice viewing</li>
        <li>Profile management</li>
    @endif
</ul>

<h3>Getting Started</h3>

<p>After logging in, we recommend:</p>
<ol style="margin-left: 20px; margin-bottom: 20px;">
    <li><strong>Change your password</strong> - Go to your profile settings</li>
    <li><strong>Complete your profile</strong> - Add any additional information</li>
    <li><strong>Explore the dashboard</strong> - Familiarize yourself with the interface</li>
    <li><strong>Review your permissions</strong> - Understand what you can access</li>
</ol>

<p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

<p>Welcome to the team!</p>

<p>Best regards,<br>
<strong>{{ config('app.name') }} Team</strong></p>
@endsection