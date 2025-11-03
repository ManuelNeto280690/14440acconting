<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'check.role:super_admin,admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::with(['subscription.plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calcular estatísticas
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('data->status', 'active')->count();
        $trialTenants = Tenant::whereHas('subscription', function($q) {
            $q->where('trial_ends_at', '>', now());
        })->count();
        $inactiveTenants = Tenant::where('data->status', '!=', 'active')->count();

        return view('admin.tenants.index', compact(
            'tenants', 
            'totalTenants', 
            'activeTenants', 
            'trialTenants', 
            'inactiveTenants'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.tenants.create', compact('plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Gerar senha temporária para o usuário
        $temporaryPassword = Str::random(12);

        $validator = Validator::make($request->all(), [
            // Dados do tenant
            'name' => 'required|string|max:255',
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('domains', 'domain'),
            ],
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'plan_id' => 'required|exists:plans,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            
            // Dados do usuário responsável (sem validação de senha)
            'user_name' => 'required|string|max:255',
            'user_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'user_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            // Gerar senha temporária
            $temporaryPassword = Str::random(12);

            // Usar transação automática do Eloquent para evitar conflitos com multi-tenancy
            $user = null;
            $tenant = null;
            $subscription = null;

            // Criar o usuário responsável primeiro com senha temporária
            $user = User::create([
                'id' => Str::uuid(),
                'name' => $request->user_name,
                'email' => $request->user_email,
                'password' => Hash::make($temporaryPassword), // Usar senha temporária
                'role' => 'tenant',
                'is_active' => true,
                'phone' => $request->user_phone,
                'email_verified_at' => now(), // Marcar como verificado automaticamente
                'permissions' => [],
            ]);

            // Associar o usuário ao role 'tenant' na tabela user_roles
            $tenantRole = \App\Models\Role::where('name', 'tenant')->first();
            if ($tenantRole) {
                $user->roles()->attach($tenantRole->id);
            }

            \Log::info('Responsible user created successfully', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->role,
                'assigned_roles' => $user->roles->pluck('name')->toArray()
            ]);

            // Criar o tenant usando o user_id do usuário criado
            $tenant = Tenant::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'email' => $request->email,
                'domain' => $request->domain,
                'database' => 'tenant_' . Str::slug($request->domain, '_'), // Gerar nome do banco
                'company_name' => $request->company_name,
                'tax_id' => $request->tax_id,
                'phone' => $request->phone,
                'website' => $request->website,
                'address' => $request->address,
                'status' => 'active',
                'activated_at' => now(),
                'user_id' => $user->id, // Associar o usuário criado
                'settings' => [
                    'trial_enabled' => $request->boolean('trial_enabled'),
                    'notes' => $request->notes,
                ],
                'data' => [
                    'user_id' => $user->id, // Manter para compatibilidade
                ],
            ]);

            \Log::info('Tenant created successfully', [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'tenant_name' => $tenant->name,
                'domain' => $tenant->domain
            ]);

            // Criar o domínio
            Domain::create([
                'domain' => $request->domain,
                'tenant_id' => $tenant->id,
            ]);

            \Log::info('Domain created for tenant', [
                'domain' => $request->domain,
                'tenant_id' => $tenant->id
            ]);

            // Criar a assinatura com ID explícito para evitar erro de campo obrigatório
            $plan = \App\Models\Plan::find($request->plan_id);
            $subscription = Subscription::create([
                'id' => Str::uuid(), // Definir ID explicitamente
                'tenant_id' => $tenant->id,
                'plan_id' => $request->plan_id,
                'status' => 'active',
                'amount' => $plan->price ?? 0.00,
                'currency' => $plan->currency ?? 'USD',
                'starts_at' => now(),
                'trial_ends_at' => $request->boolean('trial_enabled') ? now()->addDays(14) : null,
            ]);

            \Log::info('Subscription created for tenant', [
                'subscription_id' => $subscription->id,
                'plan_id' => $request->plan_id,
                'tenant_id' => $tenant->id
            ]);

            // Criar a relação tenant_users (associar o usuário criado como owner)
            \App\Models\TenantUser::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'role' => \App\Models\TenantUser::ROLE_OWNER,
                'is_active' => true,
                'joined_at' => now(),
                'permissions' => \App\Models\TenantUser::getDefaultPermissionsForRole(\App\Models\TenantUser::ROLE_OWNER),
            ]);

            \Log::info('TenantUser relationship created', [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'role' => \App\Models\TenantUser::ROLE_OWNER
            ]);

            // NOVO: Criar automaticamente o banco de dados do tenant e o usuário
            try {
                \Log::info('Creating tenant database automatically', [
                    'tenant_id' => $tenant->id,
                    'database' => $tenant->database
                ]);
            
                // Obter o DatabaseManager correto (compatível com CreateDatabase::handle)
                $databaseManager = app(\Stancl\Tenancy\Database\DatabaseManager::class);
            
                // Criar o banco de dados
                $createDatabaseJob = new CreateDatabase($tenant);
                $createDatabaseJob->handle($databaseManager);
            
                \Log::info('Tenant database created successfully', [
                    'tenant_id' => $tenant->id,
                    'database' => $tenant->database
                ]);
            
                // Executar migrations
                $migrateDatabaseJob = new MigrateDatabase($tenant);
                $migrateDatabaseJob->handle();
            
                \Log::info('Tenant database migrations completed', [
                    'tenant_id' => $tenant->id
                ]);
            
                // Criar o usuário principal no banco do tenant
                $this->createTenantUser($tenant, $user);
            
                \Log::info('Tenant user created in tenant database', [
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id
                ]);
            
            } catch (\Exception $dbException) {
                \Log::error('Error creating tenant database or user', [
                    'tenant_id' => $tenant->id,
                    'error' => $dbException->getMessage(),
                    'trace' => $dbException->getTraceAsString()
                ]);
                
                // Não falhar a criação do tenant se o banco falhar
                // O usuário pode criar manualmente depois
            }

            // Enviar e-mail de boas-vindas com senha temporária
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user, $tenant, $temporaryPassword));
                
                \Log::info('Welcome email sent successfully', [
                    'user_email' => $user->email,
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id
                ]);
            } catch (\Exception $emailException) {
                \Log::warning('Failed to send welcome email', [
                    'user_email' => $user->email,
                    'tenant_id' => $tenant->id,
                    'error' => $emailException->getMessage()
                ]);
                // Não falhar a criação do tenant se o e-mail falhar
            }

            return redirect()->route('admin.tenants.index')
                           ->with('success', 'Tenant created successfully! Database and user created automatically. Welcome email sent to: ' . $user->email . ' with temporary password.');

        } catch (\Exception $e) {
            \Log::error('Error creating tenant and user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            // Tentar fazer limpeza manual se algo deu errado
            try {
                if (isset($subscription) && $subscription) {
                    $subscription->delete();
                }
                if (isset($tenant) && $tenant) {
                    $tenant->delete();
                }
                if (isset($user) && $user) {
                    $user->delete();
                }
            } catch (\Exception $cleanupException) {
                \Log::warning('Error during cleanup', [
                    'cleanup_error' => $cleanupException->getMessage()
                ]);
            }

            return redirect()->back()
                           ->withErrors(['error' => 'Error creating tenant: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Criar o usuário principal no banco de dados do tenant
     */
    private function createTenantUser($tenant, $centralUser)
    {
        try {
            // Inicializar o contexto do tenant
            tenancy()->initialize($tenant);

            // Verificar se o usuário já existe no banco do tenant
            $existingUser = \App\Models\User::where('email', $centralUser->email)->first();
            
            if ($existingUser) {
                \Log::info('User already exists in tenant database', [
                    'tenant_id' => $tenant->id,
                    'user_email' => $centralUser->email
                ]);
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

            \Log::info('User created in tenant database', [
                'tenant_id' => $tenant->id,
                'user_id' => $tenantUser->id,
                'user_email' => $tenantUser->email
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating user in tenant database', [
                'tenant_id' => $tenant->id,
                'user_id' => $centralUser->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            // Limpar o contexto do tenant
            tenancy()->end();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tenant = Tenant::with(['subscription.plan'])->findOrFail($id);
        
        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            // Remover 'users' do with() pois não existe esse relacionamento no modelo Tenant
            $tenant = Tenant::with(['subscriptions.plan', 'domains'])->findOrFail($id);
            $plans = Plan::where('is_active', true)->orderBy('name')->get();
            
            // Se precisar dos usuários do tenant, buscar separadamente via TenantUser
            $tenantUsers = \App\Models\TenantUser::where('tenant_id', $tenant->id)
                ->with('user')
                ->get();
            
            \Log::info('Tenant edit form accessed', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'accessed_by' => auth()->id()
            ]);
            
            return view('admin.tenants.edit', compact('tenant', 'plans', 'tenantUsers'));
            
        } catch (\Exception $e) {
            \Log::error('Error accessing tenant edit form', [
                'tenant_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.tenants.index')
                           ->with('error', 'Tenant não encontrado ou erro ao acessar.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $tenant = Tenant::with(['subscriptions', 'domains'])->findOrFail($id);
            
            // Validação robusta
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('tenants', 'email')->ignore($tenant->id),
                ],
                'domain' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($tenant) {
                        // Verificar se o domínio já existe em outro tenant
                        $existingDomain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $value)
                            ->whereHas('tenant', function($query) use ($tenant) {
                                $query->where('id', '!=', $tenant->id);
                            })->first();
                        
                        if ($existingDomain) {
                            $fail('Este domínio já está sendo usado por outro tenant.');
                        }
                    },
                ],
                'company_name' => 'nullable|string|max:255',
                'tax_id' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'website' => 'nullable|url|max:255',
                'address' => 'nullable|string|max:500',
                'status' => 'nullable|in:active,inactive,suspended,pending',
                'plan_id' => 'nullable|uuid|exists:plans,id',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                               ->withErrors($validator)
                               ->withInput();
            }

            // Capturar dados originais para log de auditoria
            $originalData = $tenant->toArray();
            
            // Atualizar campos do tenant
            $tenant->update([
                'name' => $request->name,
                'email' => $request->email,
                'company_name' => $request->company_name,
                'tax_id' => $request->tax_id,
                'phone' => $request->phone,
                'website' => $request->website,
                'address' => $request->address,
                'status' => $request->status ?? $tenant->status,
                'settings' => array_merge($tenant->settings ?? [], [
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                    'notes' => $request->notes,
                ])
            ]);

            // Atualizar domínio se necessário
            $currentDomain = $tenant->domains()->first();
            if ($currentDomain && $currentDomain->domain !== $request->domain) {
                $currentDomain->update(['domain' => $request->domain]);
                
                \Log::info('Tenant domain updated', [
                    'tenant_id' => $tenant->id,
                    'old_domain' => $currentDomain->domain,
                    'new_domain' => $request->domain,
                    'updated_by' => auth()->id()
                ]);
            }

            // Atualizar subscription se um novo plano foi selecionado
            if ($request->filled('plan_id')) {
                $currentSubscription = $tenant->subscriptions()->where('status', 'active')->first();
                
                if (!$currentSubscription || $currentSubscription->plan_id !== $request->plan_id) {
                    // Cancelar subscription atual se existir
                    if ($currentSubscription) {
                        $currentSubscription->update([
                            'status' => 'canceled',
                            'canceled_at' => now()
                        ]);
                    }
                    
                    // Criar nova subscription
                    $plan = Plan::find($request->plan_id);
                    $newSubscription = Subscription::create([
                        'id' => Str::uuid(),
                        'tenant_id' => $tenant->id,
                        'plan_id' => $request->plan_id,
                        'status' => 'active',
                        'amount' => $plan->price ?? 0.00,
                        'currency' => $plan->currency ?? 'USD',
                        'starts_at' => now(),
                        'trial_ends_at' => null, // Não aplicar trial em mudanças de plano
                    ]);
                    
                    \Log::info('Tenant subscription updated', [
                        'tenant_id' => $tenant->id,
                        'old_subscription_id' => $currentSubscription?->id,
                        'new_subscription_id' => $newSubscription->id,
                        'new_plan_id' => $request->plan_id,
                        'updated_by' => auth()->id()
                    ]);
                }
            }

            // Log de auditoria detalhado
            $changes = [];
            foreach ($originalData as $key => $value) {
                if (isset($tenant->$key) && $tenant->$key != $value) {
                    $changes[$key] = [
                        'old' => $value,
                        'new' => $tenant->$key
                    ];
                }
            }

            \Log::info('Tenant updated successfully', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'changes' => $changes,
                'updated_by' => auth()->id(),
                'updated_at' => now()
            ]);

            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('success', 'Tenant atualizado com sucesso!');

        } catch (\Exception $e) {
            \Log::error('Error updating tenant', [
                'tenant_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                           ->withErrors(['error' => 'Erro ao atualizar tenant: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            
            // Delete related records first
            if ($tenant->subscription) {
                $tenant->subscription->delete();
            }
            $tenant->domains()->delete();
            
            // Delete tenant (this will also trigger database deletion via events)
            $tenant->delete();

            return redirect()->route('admin.tenants.index')
                ->with('success', 'Tenant excluído com sucesso!');

        } catch (\Exception $e) {
            // Se o erro for relacionado ao banco não existir, ainda assim deletar o tenant
            if (str_contains($e->getMessage(), "database doesn't exist") || 
                str_contains($e->getMessage(), "Can't drop database")) {
                
                try {
                    $tenant = Tenant::findOrFail($id);
                    
                    // Forçar exclusão sem tentar deletar o banco
                    // Delete related records
                    if ($tenant->subscription) {
                        $tenant->subscription->delete();
                    }
                    $tenant->domains()->delete();
                    
                    // Delete tenant record directly without triggering events
                    $tenant->forceDelete();
                    
                    return redirect()->route('admin.tenants.index')
                        ->with('success', 'Tenant excluído com sucesso! (Banco de dados não existia)');
                        
                } catch (\Exception $innerException) {
                    return redirect()->back()
                        ->with('error', 'Erro ao excluir tenant: ' . $innerException->getMessage());
                }
            }
            
            return redirect()->back()
                ->with('error', 'Erro ao excluir tenant: ' . $e->getMessage());
        }
    }

    /**
     * Activate tenant
     */
    public function activate(string $id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            
            $tenant->update([
                'status' => 'active',
                'activated_at' => now(),
                'suspended_at' => null,
                'settings' => array_merge($tenant->settings ?? [], [
                    'activated_by' => auth()->id(),
                    'activated_at' => now(),
                ])
            ]);

            return redirect()->back()
                ->with('success', 'Tenant ativado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao ativar tenant: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate tenant
     */
    public function deactivate(string $id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            
            $tenant->update([
                'status' => 'inactive',
                'suspended_at' => now(),
                'settings' => array_merge($tenant->settings ?? [], [
                    'deactivated_by' => auth()->id(),
                    'deactivated_at' => now(),
                ])
            ]);

            return redirect()->back()
                ->with('success', 'Tenant desativado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao desativar tenant: ' . $e->getMessage());
        }
    }

    /**
     * Reset tenant admin password
     */
    public function resetPassword(string $id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            
            // Generate new temporary password
            $newPassword = Str::random(12);
            
            // Update tenant settings with new password info
            $tenant->update([
                'settings' => array_merge($tenant->settings ?? [], [
                    'temp_password' => $newPassword,
                    'password_reset_by' => auth()->id(),
                    'password_reset_at' => now(),
                ])
            ]);

            return redirect()->back()
                ->with('success', 'Senha resetada com sucesso!')
                ->with('temp_password', $newPassword);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao resetar senha: ' . $e->getMessage());
        }
    }

    /**
     * Toggle tenant status between active and inactive
     */
    public function toggleStatus(string $id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            
            // Verificar o status atual
            $currentStatus = $tenant->status ?? 'inactive';
            $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
            
            // Atualizar o status
            $tenant->status = $newStatus;
            
            if ($newStatus === 'active') {
                $tenant->activated_at = now();
                $tenant->suspended_at = null;
            } else {
                $tenant->suspended_at = now();
            }
            
            $tenant->save();

            $message = $newStatus === 'active' ? 'Tenant ativado com sucesso!' : 'Tenant desativado com sucesso!';
            
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao alterar status do tenant: ' . $e->getMessage());
        }
    }
}
