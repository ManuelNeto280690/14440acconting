<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;
use App\Models\Tenant;

class FixTenantDomain extends Command
{
    protected $signature = 'tenant:fix-domain {old_domain} {new_domain}';
    protected $description = 'Fix tenant domain configuration';

    public function handle()
    {
        $oldDomain = $this->argument('old_domain');
        $newDomain = $this->argument('new_domain');

        // Buscar o domínio atual
        $domain = Domain::where('domain', $oldDomain)->first();
        
        if (!$domain) {
            $this->error("Domínio '{$oldDomain}' não encontrado.");
            return 1;
        }

        // Atualizar o domínio
        $domain->update(['domain' => $newDomain]);
        
        // Atualizar também na tabela tenants se necessário
        $tenant = $domain->tenant;
        if ($tenant && $tenant->domain === $oldDomain) {
            $tenant->update(['domain' => $newDomain]);
        }

        $this->info("Domínio atualizado de '{$oldDomain}' para '{$newDomain}' com sucesso!");
        
        return 0;
    }
}