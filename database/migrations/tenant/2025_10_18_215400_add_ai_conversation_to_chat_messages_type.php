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
        // Para MySQL, precisamos alterar o enum adicionando o novo valor
        DB::statement("ALTER TABLE chat_messages MODIFY COLUMN type ENUM('text', 'image', 'document', 'audio', 'system', 'ai_conversation') DEFAULT 'text'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover o valor 'ai_conversation' do enum
        DB::statement("ALTER TABLE chat_messages MODIFY COLUMN type ENUM('text', 'image', 'document', 'audio', 'system') DEFAULT 'text'");
    }
};