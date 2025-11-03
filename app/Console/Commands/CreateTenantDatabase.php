<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use Stancl\Tenancy\DatabaseManagers\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenantDatabase extends Command
{
    protected $signature = 'tenant:create-database {tenant_id?} {--all : Create databases for all tenants}';
    protected $description = 'Create database for a specific tenant or all tenants';

    public function handle()
    {
        if ($this->option('all')) {
            $this->createAllTenantDatabases();
        } else {
            $tenantId = $this->argument('tenant_id');
            if (!$tenantId) {
                $tenantId = $this->ask('Digite o ID do tenant:');
            }
            $this->createTenantDatabase($tenantId);
        }
    }

    private function createTenantDatabase($tenantId)
    {
        try {
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant com ID '{$tenantId}' não encontrado.");
                return 1;
            }

            $this->info("Criando banco de dados para o tenant: {$tenant->name}");
            $this->info("Database: {$tenant->database}");

            // Obter o DatabaseManager correto baseado no tipo de banco
            $databaseType = config('database.default');
            $managerClass = config("tenancy.database.managers.{$databaseType}");
            
            if (!$managerClass) {
                throw new \Exception("Database manager não encontrado para o tipo: {$databaseType}");
            }
            
            // Usar o DatabaseManager compatível com a Job
            $databaseManager = app(\Stancl\Tenancy\Database\DatabaseManager::class);

            // Criar o banco de dados
            $createDatabaseJob = new CreateDatabase($tenant);
            $createDatabaseJob->handle($databaseManager);

            $this->info("✅ Banco de dados criado com sucesso!");

            // Executar migrations
            $this->info("Executando migrations...");
            $migrateDatabaseJob = new MigrateDatabase($tenant);
            $migrateDatabaseJob->handle();

            $this->info("✅ Migrations executadas com sucesso!");

            // Criar o usuário principal no banco do tenant
            $this->info("Criando usuário principal no banco do tenant...");
            $this->createTenantUser($tenant);

            $this->info("🎉 Tenant '{$tenant->name}' está pronto para uso!");

            return 0;

        } catch (\Exception $e) {
            $this->error("Erro ao criar banco de dados: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    private function createTenantUser($tenant)
    {
        try {
            // Buscar o usuário principal no banco central
            $centralUser = User::find($tenant->user_id);
            
            if (!$centralUser) {
                $this->warn("Usuário principal não encontrado no banco central. ID: {$tenant->user_id}");
                return;
            }

            // Inicializar o contexto do tenant
            tenancy()->initialize($tenant);

            // Verificar se o usuário já existe no banco do tenant
            $existingUser = \App\Models\User::where('email', $centralUser->email)->first();
            
            if ($existingUser) {
                $this->info("✅ Usuário já existe no banco do tenant: {$centralUser->email}");
                return;
            }

            // Criar o usuário no banco do tenant
            $tenantUser = \App\Models\User::create([
                'id' => $centralUser->id, // Manter o mesmo ID
                'name' => $centralUser->name,
                'email' => $centralUser->email,
                'password' => $centralUser->password, // Usar a mesma senha hash
                'role' => 'owner', // Definir como owner no tenant
                'is_active' => true,
                'phone' => $centralUser->phone,
                'email_verified_at' => $centralUser->email_verified_at,
                'permissions' => ['*'], // Permissões completas para o owner
                'created_at' => $centralUser->created_at,
                'updated_at' => now(),
            ]);

            $this->info("✅ Usuário principal criado no banco do tenant: {$tenantUser->email}");

        } catch (\Exception $e) {
            $this->error("Erro ao criar usuário no tenant: " . $e->getMessage());
            throw $e;
        } finally {
            // Limpar o contexto do tenant
            tenancy()->end();
        }
    }

    private function createAllTenantDatabases()
    {
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->info("Nenhum tenant encontrado.");
            return;
        }

        $this->info("Encontrados {$tenants->count()} tenants. Criando bancos de dados...");

        foreach ($tenants as $tenant) {
            $this->line("Processando tenant: {$tenant->name} (ID: {$tenant->id})");
            $this->createTenantDatabase($tenant->id);
            $this->line("---");
        }

        $this->info("🎉 Processamento concluído!");
    }
}