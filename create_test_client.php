<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Client;

try {
    $client = Client::create([
        'name' => 'Test Client',
        'email' => 'test@client.com',
        'password' => bcrypt('password123'),
        'phone' => '123456789',
        'status' => 'active'
    ]);
    
    echo "Client created successfully with ID: " . $client->id . "\n";
    echo "Name: " . $client->name . "\n";
    echo "Email: " . $client->email . "\n";
    
} catch (Exception $e) {
    echo "Error creating client: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}