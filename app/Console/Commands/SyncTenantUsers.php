<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;

class SyncTenantUsers extends Command
{
    protected $signature = 'tenant:sync-users {tenant_id?} {--all : Sync users for all tenants}';
    protected $description = 'Sync main database users to tenant databases';

    public function handle()
    {
        if ($this->option('all')) {
            $this->syncAllTenantUsers();
        } else {
            $tenantId = $this->argument('tenant_id');
            if (!$tenantId) {
                $tenantId = $this->ask('Digite o ID do tenant:');
            }
            $this->syncTenantUsers($tenantId);
        }
    }

    private function syncTenantUsers($tenantId)
    {
        try {
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant com ID '{$tenantId}' não encontrado.");
                return 1;
            }

            $this->info("Sincronizando usuários para o tenant: {$tenant->name}");

            // Buscar o usuário principal no banco central
            $centralUser = User::find($tenant->user_id);
            
            if (!$centralUser) {
                $this->error("Usuário principal não encontrado no banco central. ID: {$tenant->user_id}");
                return 1;
            }

            // Inicializar o contexto do tenant
            tenancy()->initialize($tenant);

            // Verificar se o usuário já existe no banco do tenant
            $existingUser = \App\Models\User::where('email', $centralUser->email)->first();
            
            if ($existingUser) {
                $this->info("✅ Usuário já existe no banco do tenant: {$centralUser->email}");
                
                // Atualizar dados se necessário
                $existingUser->update([
                    'name' => $centralUser->name,
                    'phone' => $centralUser->phone,
                    'is_active' => true,
                    'role' => 'owner',
                    'permissions' => ['*'],
                ]);
                
                $this->info("✅ Dados do usuário atualizados");
            } else {
                // Criar o usuário no banco do tenant
                $tenantUser = \App\Models\User::create([
                    'id' => $centralUser->id,
                    'name' => $centralUser->name,
                    'email' => $centralUser->email,
                    'password' => $centralUser->password,
                    'role' => 'owner',
                    'is_active' => true,
                    'phone' => $centralUser->phone,
                    'email_verified_at' => $centralUser->email_verified_at,
                    'permissions' => ['*'],
                    'created_at' => $centralUser->created_at,
                    'updated_at' => now(),
                ]);

                $this->info("✅ Usuário principal criado no banco do tenant: {$tenantUser->email}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Erro ao sincronizar usuário: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        } finally {
            // Limpar o contexto do tenant
            tenancy()->end();
        }
    }

    private function syncAllTenantUsers()
    {
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->info("Nenhum tenant encontrado.");
            return;
        }

        $this->info("Encontrados {$tenants->count()} tenants. Sincronizando usuários...");

        foreach ($tenants as $tenant) {
            $this->line("Processando tenant: {$tenant->name} (ID: {$tenant->id})");
            $this->syncTenantUsers($tenant->id);
            $this->line("---");
        }

        $this->info("🎉 Sincronização concluída!");
    }
}