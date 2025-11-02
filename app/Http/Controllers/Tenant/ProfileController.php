<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function show(): View
    {
        $user = Auth::guard('tenant')->user();
        
        return view('tenant.profile.show', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit(): View
    {
        $user = Auth::guard('tenant')->user();
        
        return view('tenant.profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('tenant')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Verify current password if changing password
            if ($request->filled('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return redirect()->back()
                        ->withErrors(['current_password' => 'A senha atual está incorreta.'])
                        ->withInput();
                }
            }

            // Update user data
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            Log::info('User profile updated', [
                'tenant_id' => tenant('id'),
                'user_id' => $user->id,
                'updated_fields' => array_keys($userData),
            ]);

            return redirect()->route('tenant.profile.show')
                ->with('success', 'Perfil atualizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to update user profile', [
                'tenant_id' => tenant('id'),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao atualizar perfil: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $user = Auth::guard('tenant')->user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withErrors(['password' => 'A senha está incorreta.']);
        }

        try {
            Log::info('User account deletion requested', [
                'tenant_id' => tenant('id'),
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            // Logout the user
            Auth::guard('tenant')->logout();

            // Delete the user account
            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('tenant.login')
                ->with('success', 'Conta excluída com sucesso.');

        } catch (\Exception $e) {
            Log::error('Failed to delete user account', [
                'tenant_id' => tenant('id'),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao excluir conta: ' . $e->getMessage());
        }
    }
}