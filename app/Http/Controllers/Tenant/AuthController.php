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
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('tenant.auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('tenant')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            Log::info('User logged in', [
                'user_id' => Auth::guard('tenant')->id(),
                'email' => Auth::guard('tenant')->user()->email,
                'tenant_id' => tenant('id'),
                'ip' => $request->ip(),
            ]);

            return redirect()->intended(route('tenant.dashboard'))
                ->with('success', 'Welcome back!');
        }

        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'tenant_id' => tenant('id'),
            'ip' => $request->ip(),
        ]);

        return redirect()->back()
            ->withErrors(['email' => 'Invalid credentials.'])
            ->withInput($request->only('email'));
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('tenant.auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => null, // Will be verified via email
                'role' => 'user', // Default role for tenant users
            ]);

            event(new Registered($user));

            Auth::login($user);

            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'tenant_id' => tenant('id'),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('tenant.dashboard')
                ->with('success', 'Registration successful! Please verify your email address.');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Registration failed. Please try again.')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::guard('tenant')->id();
        $userEmail = Auth::guard('tenant')->user()->email ?? 'unknown';

        Auth::guard('tenant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logged out', [
            'user_id' => $userId,
            'email' => $userEmail,
            'tenant_id' => tenant('id'),
        ]);

        return redirect()->route('tenant.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPasswordForm(): View
    {
        return view('tenant.auth.forgot-password');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $status = Password::broker('tenants')->sendResetLink(
                $request->only('email')
            );

            Log::info('Password reset link sent', [
                'email' => $request->email,
                'tenant_id' => tenant('id'),
                'status' => $status,
            ]);

            if ($status === Password::RESET_LINK_SENT) {
                return redirect()->back()
                    ->with('success', 'Password reset link sent to your email!');
            }

            return redirect()->back()
                ->withErrors(['email' => __($status)]);

        } catch (\Exception $e) {
            Log::error('Failed to send password reset link', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send reset link. Please try again.');
        }
    }

    /**
     * Show the reset password form.
     */
    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('tenant.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        try {
            $status = Password::broker('tenants')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            Log::info('Password reset completed', [
                'email' => $request->email,
                'tenant_id' => tenant('id'),
                'status' => $status,
            ]);

            if ($status === Password::PASSWORD_RESET) {
                return redirect()->route('tenant.login')
                    ->with('success', 'Password reset successfully! You can now login.');
            }

            return redirect()->back()
                ->withErrors(['email' => __($status)])
                ->withInput($request->only('email'));

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Password reset failed. Please try again.')
                ->withInput($request->only('email'));
        }
    }

    /**
     * Verify email address.
     */
    public function verifyEmail(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return redirect()->route('tenant.login')
                ->with('error', 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('tenant.dashboard')
                ->with('info', 'Email already verified.');
        }

        if ($user->markEmailAsVerified()) {
            Log::info('Email verified', [
                'user_id' => $user->id,
                'email' => $user->email,
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->route('tenant.dashboard')
                ->with('success', 'Email verified successfully!');
        }

        return redirect()->route('tenant.login')
            ->with('error', 'Email verification failed.');
    }

    /**
     * Resend email verification.
     */
    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('tenant.dashboard')
                ->with('info', 'Email already verified.');
        }

        try {
            $request->user()->sendEmailVerificationNotification();

            Log::info('Email verification resent', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('success', 'Verification email sent!');

        } catch (\Exception $e) {
            Log::error('Failed to resend verification email', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send verification email.');
        }
    }

    /**
     * Show email verification notice.
     */
    public function showVerificationNotice(): View
    {
        return view('tenant.auth.verify-email');
    }
    // Alias para manter compatibilidade com a rota POST /forgot-password
    public function forgotPassword(Request $request): RedirectResponse
    {
        return $this->sendResetLinkEmail($request);
    }
}
