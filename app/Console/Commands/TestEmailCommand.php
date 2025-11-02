<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Exception;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test {email} {--debug}';
    protected $description = 'Test email configuration and SMTP connectivity';

    public function handle()
    {
        $email = $this->argument('email');
        $debug = $this->option('debug');
        
        $this->info('🔍 Testing Email Configuration...');
        $this->newLine();
        
        // Mostrar configurações atuais
        $this->displayCurrentConfig();
        
        // Teste 1: Configurações básicas
        $this->info('📋 Step 1: Validating configuration...');
        if (!$this->validateConfig()) {
            return 1;
        }
        $this->info('✅ Configuration is valid');
        $this->newLine();
        
        // Teste 2: Conectividade SMTP
        $this->info('🌐 Step 2: Testing SMTP connectivity...');
        if (!$this->testSMTPConnection()) {
            return 1;
        }
        $this->info('✅ SMTP connection successful');
        $this->newLine();
        
        // Teste 3: Envio de e-mail simples
        $this->info('📧 Step 3: Sending test email...');
        if (!$this->sendTestEmail($email, $debug)) {
            return 1;
        }
        
        $this->info('✅ Test email sent successfully!');
        $this->info("📬 Please check the inbox for: {$email}");
        $this->info("📁 Also check spam/junk folder");
        
        return 0;
    }
    
    private function displayCurrentConfig()
    {
        $this->info('Current Email Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['MAIL_MAILER', config('mail.default')],
                ['SMTP_HOST', config('mail.mailers.smtp.host')],
                ['SMTP_PORT', config('mail.mailers.smtp.port')],
                ['SMTP_USERNAME', config('mail.mailers.smtp.username')],
                ['SMTP_ENCRYPTION', config('mail.mailers.smtp.encryption')],
                ['FROM_ADDRESS', config('mail.from.address')],
                ['FROM_NAME', config('mail.from.name')],
            ]
        );
        $this->newLine();
    }
    
    private function validateConfig()
    {
        $required = [
            'mail.mailers.smtp.host' => 'SMTP Host',
            'mail.mailers.smtp.port' => 'SMTP Port',
            'mail.mailers.smtp.username' => 'SMTP Username',
            'mail.mailers.smtp.password' => 'SMTP Password',
            'mail.from.address' => 'From Address',
        ];
        
        foreach ($required as $key => $name) {
            if (empty(config($key))) {
                $this->error("❌ Missing configuration: {$name}");
                return false;
            }
        }
        
        return true;
    }
    
    private function testSMTPConnection()
    {
        try {
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            
            $this->info("Attempting to connect to {$host}:{$port}...");
            
            // Teste de conectividade usando fsockopen
            $connection = @fsockopen($host, $port, $errno, $errstr, 10);
            
            if (!$connection) {
                $this->error("❌ Cannot connect to SMTP server: {$errstr} ({$errno})");
                $this->error("Possible issues:");
                $this->error("- SMTP server is down");
                $this->error("- Port {$port} is blocked by firewall");
                $this->error("- Incorrect host/port configuration");
                return false;
            }
            
            fclose($connection);
            return true;
            
        } catch (Exception $e) {
            $this->error("❌ SMTP connection test failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendTestEmail($email, $debug = false)
    {
        try {
            if ($debug) {
                // Habilitar debug do Swift Mailer
                Config::set('mail.mailers.smtp.stream', [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]);
            }
            
            $subject = 'Test Email - Laravel SMTP Configuration';
            $message = "
                <h2>SMTP Test Email</h2>
                <p>This is a test email sent from your Laravel application.</p>
                <p><strong>Sent at:</strong> " . now()->format('Y-m-d H:i:s') . "</p>
                <p><strong>From:</strong> " . config('mail.from.address') . "</p>
                <p><strong>SMTP Host:</strong> " . config('mail.mailers.smtp.host') . "</p>
                <p>If you received this email, your SMTP configuration is working correctly!</p>
            ";
            
            Mail::send([], [], function ($mail) use ($email, $subject, $message) {
                $mail->to($email)
                     ->subject($subject)
                     ->html($message);
            });
            
            return true;
            
        } catch (Exception $e) {
            $this->error("❌ Failed to send test email: " . $e->getMessage());
            
            if ($debug) {
                $this->error("Stack trace:");
                $this->error($e->getTraceAsString());
            }
            
            $this->error("\nPossible solutions:");
            $this->error("1. Check SMTP credentials");
            $this->error("2. Verify SMTP server settings");
            $this->error("3. Check if authentication is required");
            $this->error("4. Try different encryption method (TLS/SSL)");
            
            return false;
        }
    }
}