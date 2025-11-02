<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin Role
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin'
        ], [
            'id' => Str::uuid(),
            'display_name' => 'Super Administrator',
            'description' => 'Full system access with all permissions',
            'is_active' => true
        ]);

        // Create Admin Role
        $admin = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'id' => Str::uuid(),
            'display_name' => 'Administrator',
            'description' => 'Administrative access with most permissions',
            'is_active' => true
        ]);

        // Create Tenant Role
        $tenant = Role::firstOrCreate([
            'name' => 'tenant'
        ], [
            'id' => Str::uuid(),
            'display_name' => 'Tenant',
            'description' => 'Tenant account with limited administrative permissions',
            'is_active' => true
        ]);

        // Create Client Role
        $client = Role::firstOrCreate([
            'name' => 'client'
        ], [
            'id' => Str::uuid(),
            'display_name' => 'Client',
            'description' => 'Client account with basic permissions',
            'is_active' => true
        ]);

        // Assign all permissions to Super Admin
        $allPermissions = Permission::all();
        if ($allPermissions->count() > 0) {
            $superAdmin->permissions()->sync($allPermissions->pluck('id'));
        }

        // Assign specific permissions to Admin (exclude super admin permissions)
        $adminPermissions = Permission::whereNotIn('name', [
            'manage_system_settings',
            'manage_super_admin_users'
        ])->get();
        
        if ($adminPermissions->count() > 0) {
            $admin->permissions()->sync($adminPermissions->pluck('id'));
        }

        // Assign basic permissions to Tenant
        $tenantPermissions = Permission::whereIn('name', [
            'view_dashboard',
            'view_users',
            'create_users',
            'edit_users',
            'manage_user_roles',
            'view_integrations',
            'manage_integrations',
            'view_reports'
        ])->get();
        
        if ($tenantPermissions->count() > 0) {
            $tenant->permissions()->sync($tenantPermissions->pluck('id'));
        }

        // Assign minimal permissions to Client
        $clientPermissions = Permission::whereIn('name', [
            'view_dashboard'
        ])->get();
        
        if ($clientPermissions->count() > 0) {
            $client->permissions()->sync($clientPermissions->pluck('id'));
        }
    }
}