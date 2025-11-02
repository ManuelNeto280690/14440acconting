<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->uuid('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->longText('message');
            $table->enum('type', ['text', 'image', 'document', 'audio', 'system'])->default('text');
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->enum('status', ['pending', 'processing', 'processed', 'failed', 'delivered', 'read'])->default('pending');
            $table->json('metadata')->nullable();
            $table->uuid('response_to')->nullable(); // Mudança: de foreignId para uuid
            $table->timestamp('processed_at')->nullable();
            $table->float('ai_confidence')->nullable();
            $table->string('intent')->nullable();
            $table->json('entities')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Definir chave estrangeira auto-referencial explicitamente
            $table->foreign('response_to')->references('id')->on('chat_messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
