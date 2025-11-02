<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

// Inicializar o Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "🔄 Executando migrations...\n";
    
    // Executar migrations
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    
    if ($exitCode === 0) {
        echo "✅ Migrations executadas com sucesso!\n";
        echo Artisan::output();
    } else {
        echo "❌ Erro ao executar migrations!\n";
        echo Artisan::output();
    }
    
    // Verificar status das migrations
    echo "\n📋 Status das migrations:\n";
    Artisan::call('migrate:status');
    echo Artisan::output();
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}