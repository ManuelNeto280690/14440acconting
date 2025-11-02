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
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('user_id');
            $table->json('permissions')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('permissions');
            $table->timestamp('invited_at')->nullable()->after('is_active');
            $table->timestamp('joined_at')->nullable()->after('invited_at');
            $table->timestamp('last_activity_at')->nullable()->after('joined_at');
            $table->string('invitation_token')->nullable()->after('last_activity_at');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token');
            $table->json('settings')->nullable()->after('invitation_expires_at');
            $table->softDeletes()->after('settings');

            // Adicionar índices para melhor performance
            $table->index(['tenant_id', 'role']);
            $table->index('is_active');
            $table->unique(['tenant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'role']);
            $table->dropIndex(['is_active']);
            $table->dropUnique(['tenant_id', 'user_id']);
            
            $table->dropSoftDeletes();
            $table->dropColumn([
                'role',
                'permissions',
                'is_active',
                'invited_at',
                'joined_at',
                'last_activity_at',
                'invitation_token',
                'invitation_expires_at',
                'settings'
            ]);
        });
    }
};