<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Inicializar o Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Verificar se o usuário já existe
    $existingUser = User::where('email', 'admin@14440accounting.com')->first();
    
    if ($existingUser) {
        echo "Usuário com email 'admin@14440accounting.com' já existe!\n";
        echo "Nome: " . $existingUser->name . "\n";
        echo "Role: " . $existingUser->role . "\n";
        echo "Status: " . ($existingUser->is_active ? 'Ativo' : 'Inativo') . "\n";
        exit(1);
    }

    // Criar o novo super admin
    $user = User::create([
        'name' => 'Novo Super Admin',
        'email' => 'admin@14440accounting.com',
        'password' => Hash::make('Admin@2024!'),
        'role' => 'super_admin',
        'is_active' => true,
        'permissions' => User::getAvailablePermissions(),
        'email_verified_at' => now(),
    ]);

    echo "✅ Super Admin criado com sucesso!\n";
    echo "Nome: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Senha: Admin@2024!\n";
    echo "Role: " . $user->role . "\n";
    echo "Permissions: " . count($user->permissions) . " permissions\n";
    echo "Status: " . ($user->is_active ? 'Ativo' : 'Inativo') . "\n";
    echo "ID: " . $user->id . "\n";

} catch (Exception $e) {
    echo "❌ Erro ao criar super admin: " . $e->getMessage() . "\n";
    exit(1);
}