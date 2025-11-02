<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Role;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminUserCreated extends Mailable
{
    use SerializesModels;

    public $user;
    public $role;
    public $temporaryPassword;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Role $role, string $temporaryPassword)
    {
        $this->user = $user;
        $this->role = $role;
        $this->temporaryPassword = $temporaryPassword;
        
        // Usar a URL correta do admin login
        $this->loginUrl = url('/login'); // ou config('app.url') . '/login'
        
        // Log detalhado
        \Log::info('AdminUserCreated email constructor called', [
            'user_email' => $user->email,
            'user_name' => $user->name,
            'role_name' => $role->name,
            'login_url' => $this->loginUrl
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Admin Account Has Been Created - ' . config('app.name'),
            from: config('mail.from.address', 'suporte@sigp-angola.com'),
            replyTo: config('mail.from.address', 'suporte@sigp-angola.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-user-simple',
            with: [
                'user' => $this->user,
                'role' => $this->role,
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