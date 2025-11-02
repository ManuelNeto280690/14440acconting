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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('database');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();;
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('active');
             $table->datetime('activated_at')->nullable();
            $table->datetime('suspended_at')->nullable();
            $table->json('settings')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index('domain');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
