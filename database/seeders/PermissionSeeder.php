<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['id' => Str::uuid(), 'name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'description' => 'Access to main dashboard', 'category' => 'dashboard'],
            ['id' => Str::uuid(), 'name' => 'view_analytics', 'display_name' => 'View Analytics', 'description' => 'Access to analytics and reports', 'category' => 'dashboard'],

            // Users Management
            ['id' => Str::uuid(), 'name' => 'view_users', 'display_name' => 'View Users', 'description' => 'View user listings', 'category' => 'users'],
            ['id' => Str::uuid(), 'name' => 'create_users', 'display_name' => 'Create Users', 'description' => 'Create new users', 'category' => 'users'],
            ['id' => Str::uuid(), 'name' => 'edit_users', 'display_name' => 'Edit Users', 'description' => 'Edit existing users', 'category' => 'users'],
            ['id' => Str::uuid(), 'name' => 'delete_users', 'display_name' => 'Delete Users', 'description' => 'Delete users', 'category' => 'users'],
            ['id' => Str::uuid(), 'name' => 'manage_user_roles', 'display_name' => 'Manage User Roles', 'description' => 'Assign roles to users', 'category' => 'users'],

            // Tenants Management
            ['id' => Str::uuid(), 'name' => 'view_tenants', 'display_name' => 'View Tenants', 'description' => 'View tenant listings', 'category' => 'tenants'],
            ['id' => Str::uuid(), 'name' => 'create_tenants', 'display_name' => 'Create Tenants', 'description' => 'Create new tenants', 'category' => 'tenants'],
            ['id' => Str::uuid(), 'name' => 'edit_tenants', 'display_name' => 'Edit Tenants', 'description' => 'Edit existing tenants', 'category' => 'tenants'],
            ['id' => Str::uuid(), 'name' => 'delete_tenants', 'display_name' => 'Delete Tenants', 'description' => 'Delete tenants', 'category' => 'tenants'],

            // Billing Management
            ['id' => Str::uuid(), 'name' => 'view_billing', 'display_name' => 'View Billing', 'description' => 'View billing information', 'category' => 'billing'],
            ['id' => Str::uuid(), 'name' => 'manage_billing', 'display_name' => 'Manage Billing', 'description' => 'Manage billing and subscriptions', 'category' => 'billing'],
            ['id' => Str::uuid(), 'name' => 'view_plans', 'display_name' => 'View Plans', 'description' => 'View subscription plans', 'category' => 'billing'],
            ['id' => Str::uuid(), 'name' => 'manage_plans', 'display_name' => 'Manage Plans', 'description' => 'Create and edit subscription plans', 'category' => 'billing'],

            // Reports
            ['id' => Str::uuid(), 'name' => 'view_reports', 'display_name' => 'View Reports', 'description' => 'Access to reports section', 'category' => 'reports'],
            ['id' => Str::uuid(), 'name' => 'export_data', 'display_name' => 'Export Data', 'description' => 'Export data and reports', 'category' => 'reports'],

            // Settings
            ['id' => Str::uuid(), 'name' => 'view_settings', 'display_name' => 'View Settings', 'description' => 'View system settings', 'category' => 'settings'],
            ['id' => Str::uuid(), 'name' => 'manage_settings', 'display_name' => 'Manage Settings', 'description' => 'Modify system settings', 'category' => 'settings'],
            ['id' => Str::uuid(), 'name' => 'manage_roles', 'display_name' => 'Manage Roles', 'description' => 'Create and edit roles and permissions', 'category' => 'settings'],

            // Integrations
            ['id' => Str::uuid(), 'name' => 'view_integrations', 'display_name' => 'View Integrations', 'description' => 'View integration settings', 'category' => 'integrations'],
            ['id' => Str::uuid(), 'name' => 'manage_integrations', 'display_name' => 'Manage Integrations', 'description' => 'Configure integrations', 'category' => 'integrations'],

            // General
            ['id' => Str::uuid(), 'name' => 'import_data', 'display_name' => 'Import Data', 'description' => 'Import data into system', 'category' => 'general'],
            ['id' => Str::uuid(), 'name' => 'backup_system', 'display_name' => 'Backup System', 'description' => 'Create system backups', 'category' => 'general'],

            // Tenant specific permissions
            ['id' => Str::uuid(), 'name' => 'manage_tenant_users', 'display_name' => 'Manage Tenant Users', 'description' => 'Manage users within tenant', 'category' => 'tenant'],
            ['id' => Str::uuid(), 'name' => 'manage_clients', 'display_name' => 'Manage Clients', 'description' => 'Manage tenant clients', 'category' => 'tenant'],
            ['id' => Str::uuid(), 'name' => 'manage_documents', 'display_name' => 'Manage Documents', 'description' => 'Manage tenant documents', 'category' => 'tenant'],

            // Client specific permissions
            ['id' => Str::uuid(), 'name' => 'view_own_documents', 'display_name' => 'View Own Documents', 'description' => 'View own documents', 'category' => 'client'],
            ['id' => Str::uuid(), 'name' => 'upload_documents', 'display_name' => 'Upload Documents', 'description' => 'Upload documents', 'category' => 'client'],
            ['id' => Str::uuid(), 'name' => 'view_own_invoices', 'display_name' => 'View Own Invoices', 'description' => 'View own invoices', 'category' => 'client'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}