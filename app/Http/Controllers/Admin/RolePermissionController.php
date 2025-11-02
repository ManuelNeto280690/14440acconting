<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('admin');
    }

    /**
     * Display a listing of roles and permissions
     */
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->paginate(20);
    
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role with permissions
     */
    public function create()
    {
        $permissions = Permission::active()
            ->orderBy('category')
            ->orderBy('display_name')
            ->get()
            ->groupBy('category');

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'id' => Str::uuid(),
                'name' => Str::slug($request->name, '_'),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true)
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            }

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Erro ao criar role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = Permission::active()
            ->orderBy('category')
            ->orderBy('display_name')
            ->get()
            ->groupBy('category');
    
        $rolePermissions = $role->permissions()->pluck('permissions.id')->toArray();
    
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->getKey(),
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
    
        // Prevent editing system roles
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return back()->with('error', 'Não é possível editar roles do sistema.');
        }

        try {
            DB::beginTransaction();

            $role->update([
                'name' => Str::slug($request->name, '_'),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true)
            ]);

            $role->permissions()->sync($request->permissions ?? []);

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Erro ao atualizar role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Prevent deleting system roles
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return back()->with('error', 'Não é possível excluir roles do sistema.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Não é possível excluir um role que possui usuários associados.');
        }

        try {
            DB::beginTransaction();

            $role->permissions()->detach();
            $role->delete();

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role excluído com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir role: ' . $e->getMessage());
        }
    }

    // === PERMISSION MANAGEMENT ===

    /**
     * Display permissions index
     */
    public function permissionsIndex()
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('category')
            ->orderBy('display_name')
            ->paginate(20);

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show form for creating permission
     */
    public function permissionsCreate()
    {
        $categories = [
            'dashboard' => 'Dashboard',
            'users' => 'Usuários',
            'tenants' => 'Tenants',
            'billing' => 'Faturamento',
            'reports' => 'Relatórios',
            'settings' => 'Configurações',
            'integrations' => 'Integrações',
            'general' => 'Geral'
        ];

        return view('admin.permissions.create', compact('categories'));
    }

    /**
     * Store permission
     */
    public function permissionsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        Permission::create([
            'id' => Str::uuid(),
            'name' => Str::slug($request->name, '_'),
            'display_name' => $request->display_name,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão criada com sucesso!');
    }

    /**
     * Show form for editing permission
     */
    public function permissionsEdit(Permission $permission)
    {
        $categories = [
            'dashboard' => 'Dashboard',
            'users' => 'Usuários',
            'tenants' => 'Tenants',
            'billing' => 'Faturamento',
            'reports' => 'Relatórios',
            'settings' => 'Configurações',
            'integrations' => 'Integrações',
            'general' => 'Geral'
        ];

        return view('admin.permissions.edit', compact('permission', 'categories'));
    }

    /**
     * Update permission
     */
    public function permissionsUpdate(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $permission->update([
            'name' => Str::slug($request->name, '_'),
            'display_name' => $request->display_name,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão atualizada com sucesso!');
    }

    /**
     * Delete permission
     */
    public function permissionsDestroy(Permission $permission)
    {
        if ($permission->roles()->count() > 0) {
            return back()->with('error', 'Não é possível excluir uma permissão que está associada a roles.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão excluída com sucesso!');
    }
}
