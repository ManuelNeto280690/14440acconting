<?php

require_once 'vendor/autoload.php';

function testSMTPConnection($host, $port, $timeout = 10) {
    echo "Testing connection to {$host}:{$port}...\n";
    
    $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
    
    if (!$connection) {
        echo "❌ Connection failed: {$errstr} ({$errno})\n";
        return false;
    }
    
    echo "✅ Connection successful!\n";
    
    // Ler resposta inicial do servidor
    $response = fgets($connection, 512);
    echo "Server response: " . trim($response) . "\n";
    
    fclose($connection);
    return true;
}

function testSMTPAuth($host, $port, $username, $password) {
    echo "\nTesting SMTP authentication...\n";
    
    try {
        $transport = (new Swift_SmtpTransport($host, $port, 'tls'))
            ->setUsername($username)
            ->setPassword($password);
        
        $mailer = new Swift_Mailer($transport);
        
        // Tentar iniciar o transporte
        $transport->start();
        echo "✅ SMTP authentication successful!\n";
        
        $transport->stop();
        return true;
        
    } catch (Exception $e) {
        echo "❌ SMTP authentication failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Configurações do seu .env
$host = 'app.1440consult.com';
$port = 587;
$username = 'noreply@app.1440consult.com';
$password = 'Marshal2015?';

echo "=== SMTP Connection Test ===\n\n";

// Teste 1: Conectividade básica
testSMTPConnection($host, $port);

// Teste 2: Autenticação SMTP
if (class_exists('Swift_SmtpTransport')) {
    testSMTPAuth($host, $port, $username, $password);
} else {
    echo "\nSwiftMailer not available for authentication test.\n";
}

echo "\n=== Test Complete ===\n";