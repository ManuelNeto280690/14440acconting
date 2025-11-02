<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para MySQL, precisamos alterar o enum
        DB::statement("ALTER TABLE clients MODIFY COLUMN status ENUM('active', 'inactive', 'archived') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primeiro, atualizar todos os registros 'archived' para 'inactive'
        DB::table('clients')->where('status', 'archived')->update(['status' => 'inactive']);
        
        // Depois, reverter o enum para os valores originais
        DB::statement("ALTER TABLE clients MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    }
};