<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminUserCreated;
use App\Models\User;
use App\Models\Role;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:test {email}';

    /**
     * The console command description.
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing email configuration...');
        
        // Check mail configuration
        $this->info('Mail Driver: ' . config('mail.default'));
        $this->info('SMTP Host: ' . config('mail.mailers.smtp.host'));
        $this->info('SMTP Port: ' . config('mail.mailers.smtp.port'));
        $this->info('From Address: ' . config('mail.from.address'));
        
        try {
            // Create a test user and role
            $testUser = new User([
                'name' => 'Test User',
                'email' => $email,
            ]);
            
            $testRole = new Role([
                'name' => 'admin',
            ]);
            
            $this->info('Sending test email to: ' . $email);
            
            Mail::to($email)->send(new AdminUserCreated($testUser, $testRole, 'TestPassword123'));
            
            $this->info('✅ Email sent successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Email failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}