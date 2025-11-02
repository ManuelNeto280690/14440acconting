<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Mail\AdminUserCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(10);

        // Statistics
        $stats = [
            'total_users' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admin_users' => User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->count(),
            'manager_users' => User::whereHas('roles', function($q) {
                $q->where('name', 'manager');
            })->count(),
            'user_users' => User::whereHas('roles', function($q) {
                $q->where('name', 'user');
            })->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->count(),
        ];

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'stats', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $role = Role::find($request->role);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Store the original password for email
            $temporaryPassword = $request->password;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => $request->boolean('is_active', true),
                'email_verified_at' => now(),
                'role' => $role->name,
            ]);

            // Assign role
            $role = Role::findOrFail($request->role);
            $user->roles()->attach($role);

            // Log email configuration for debugging
            \Log::info('Email configuration check', [
                'mail_mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_username' => config('mail.mailers.smtp.username'),
                'mail_from_address' => config('mail.from.address'),
                'mail_from_name' => config('mail.from.name'),
            ]);

            // Send welcome email with account details (synchronously)
            try {
                \Log::info('Attempting to send admin user created email', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'role_name' => $role->name
                ]);

                // Send email synchronously - CORREÇÃO AQUI
                Mail::to($user->email)->send(new AdminUserCreated($user, $role, $temporaryPassword));
                
                \Log::info('Admin user created email sent successfully', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'role_name' => $role->name
                ]);

                $emailStatus = 'Email sent successfully';
            } catch (\Exception $emailException) {
                \Log::error('Failed to send admin user created email', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString()
                ]);
                
                $emailStatus = 'User created but email failed: ' . $emailException->getMessage();
            }

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully. ' . $emailStatus);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error creating user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles.permissions');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'is_active' => $request->boolean('is_active', true),
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Update role
            $role = Role::findOrFail($request->role);
            $user->roles()->sync([$role->id]);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deletion of the current user
            if ($user->id === auth()->id()) {
                return redirect()->back()
                    ->with('error', 'You cannot delete your own account.');
            }

            DB::beginTransaction();

            // Detach roles
            $user->roles()->detach();
            
            // Delete user
            $user->delete();

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status (active/inactive).
     */
    public function toggleStatus(User $user)
    {
        try {
            // Prevent deactivating the current user
            if ($user->id === auth()->id() && $user->is_active) {
                return redirect()->back()
                    ->with('error', 'You cannot deactivate your own account.');
            }

            $user->update([
                'is_active' => !$user->is_active
            ]);

            $status = $user->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()
                ->with('success', "User {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating user status: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user)
    {
        try {
            $newPassword = Str::random(12);
            
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            // In a real application, you would send this password via email
            // For now, we'll just show it in the success message
            return redirect()->back()
                ->with('success', "Password reset successfully. New password: {$newPassword}")
                ->with('new_password', $newPassword);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error resetting password: ' . $e->getMessage());
        }
    }
}