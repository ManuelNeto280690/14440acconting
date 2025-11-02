<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        $superAdmin = User::where('email', 'superadmin@accounting.com')->first();

        if (!$superAdmin) {
            $superAdmin = User::create([
                'id' => Str::uuid(),
                'name' => 'Super Administrator',
                'email' => 'superadmin@accounting.com',
                'email_verified_at' => now(),
                'password' => Hash::make('SuperAdmin@123'),
            ]);

            // Assign super admin role
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                $superAdmin->roles()->attach($superAdminRole->id);
            }
        }

        // Create additional admin user
        $admin = User::where('email', 'admin@accounting.com')->first();
        
        if (!$admin) {
            $admin = User::create([
                'id' => Str::uuid(),
                'name' => 'Administrator',
                'email' => 'admin@accounting.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Admin@123'),
            ]);

            // Assign admin role
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $admin->roles()->attach($adminRole->id);
            }
        }
    }
}
