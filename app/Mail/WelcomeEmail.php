<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tenant;
    public $temporaryPassword;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Tenant $tenant, string $temporaryPassword)
    {
        $this->user = $user;
        $this->tenant = $tenant;
        $this->temporaryPassword = $temporaryPassword;
        $this->loginUrl = route('tenant.login', ['tenant' => $tenant->id]);
        
        // Log detalhado
        \Log::info('WelcomeEmail constructor called', [
            'user_email' => $user->email,
            'tenant_id' => $tenant->id,
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'from_address' => config('mail.from.address')
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' - Your Account is Ready!',
            from: config('mail.from.address', 'noreply@14440accounting.com'),
            replyTo: config('mail.from.address', 'noreply@14440accounting.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'user' => $this->user,
                'tenant' => $this->tenant,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => $this->loginUrl,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}