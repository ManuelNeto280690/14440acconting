<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\BillingController as AdminBillingController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Tenant\AuthController as TenantAuthController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\BillingController as TenantBillingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Default login redirect - redireciona para a área apropriada
Route::get('/login', function () {
    // Verifica se é um subdomínio de tenant
    $host = request()->getHost();
    $subdomain = explode('.', $host)[0];
    
    // Se for localhost ou domínio principal, redireciona para admin
    if ($host === 'localhost' || $host === '127.0.0.1' || !str_contains($host, '.')) {
        return redirect()->route('admin.login');
    }
    
    // Se for subdomínio, redireciona para tenant login
    return redirect()->route('tenant.login');
})->name('login');

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (not authenticated)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
        Route::get('/register', [AdminAuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register']);
        Route::get('/forgot-password', [AdminAuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('password.email');
        Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->name('password.update');
    });

    // Authenticated admin routes
    Route::middleware(['auth:admin', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Tenant Management
        Route::resource('tenants', AdminTenantController::class);
        Route::post('tenants/{tenant}/activate', [AdminTenantController::class, 'activate'])->name('tenants.activate');
        Route::post('tenants/{tenant}/deactivate', [AdminTenantController::class, 'deactivate'])->name('tenants.deactivate');
        Route::post('tenants/{tenant}/toggle-status', [AdminTenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
        Route::post('tenants/{tenant}/reset-password', [AdminTenantController::class, 'resetPassword'])->name('tenants.reset-password');
        
        // Plan Management
        Route::resource('plans', AdminPlanController::class);
        Route::post('plans/{plan}/activate', [AdminPlanController::class, 'activate'])->name('plans.activate');
        Route::post('plans/{plan}/deactivate', [AdminPlanController::class, 'deactivate'])->name('plans.deactivate');
        
        // Billing Management
        Route::get('billing', [AdminBillingController::class, 'index'])->name('billing.index');
        Route::get('billing/report', [AdminBillingController::class, 'report'])->name('billing.report');
        Route::get('billing/subscriptions', [AdminBillingController::class, 'subscriptions'])->name('billing.subscriptions');
        Route::get('billing/{subscription}', [AdminBillingController::class, 'show'])->name('billing.show');
        Route::put('billing/{subscription}', [AdminBillingController::class, 'update'])->name('billing.update');
        Route::post('billing/{subscription}/cancel', [AdminBillingController::class, 'cancel'])->name('billing.cancel');
        Route::patch('billing/{subscription}/reactivate', [AdminBillingController::class, 'reactivate'])->name('billing.reactivate');
        Route::post('billing/subscriptions/{tenant}/cancel', [AdminBillingController::class, 'cancelSubscription'])->name('billing.cancel-subscription');
        
        // Settings Management
        Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings');
        Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/clear-cache', [AdminSettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('settings/toggle-maintenance', [AdminSettingsController::class, 'toggleMaintenance'])->name('settings.toggle-maintenance');
        Route::get('settings/system-info', [AdminSettingsController::class, 'systemInfo'])->name('settings.system-info');

        // Role and Permission Management Routes
        Route::resource('roles', RolePermissionController::class);
        Route::get('roles/{role}/permissions', [RolePermissionController::class, 'getRolePermissions'])->name('roles.permissions');
        
        // Bulk actions for roles
        Route::post('roles/bulk-update', [RolePermissionController::class, 'bulkUpdate'])->name('roles.bulk-update');
        
        // AJAX endpoints for roles
        Route::get('roles/{role}/user-permissions', [RolePermissionController::class, 'getUserPermissions'])->name('roles.user-permissions');
        Route::get('role-permissions', [RolePermissionController::class, 'getRolePermissions'])->name('role-permissions');
        
        // Permissions Management
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [RolePermissionController::class, 'permissionsIndex'])->name('index');
            Route::get('/create', [RolePermissionController::class, 'permissionsCreate'])->name('create');
            Route::post('/', [RolePermissionController::class, 'permissionsStore'])->name('store');
            Route::get('/{permission}/edit', [RolePermissionController::class, 'permissionsEdit'])->name('edit');
            Route::put('/{permission}', [RolePermissionController::class, 'permissionsUpdate'])->name('update');
            Route::delete('/{permission}', [RolePermissionController::class, 'permissionsDestroy'])->name('destroy');
        });
        
        // User Management
        Route::resource('users', AdminUserController::class);
        Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    });
});

// User Profile Routes
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Stripe Webhook Route (outside of auth middleware)
Route::post('/webhooks/stripe', [AdminBillingController::class, 'webhook'])->name('webhooks.stripe');

// N8N Webhook Routes (domínio principal - sem autenticação e sem CSRF)
Route::withoutMiddleware(['web'])->group(function () {
    Route::prefix('api')->group(function () {
        Route::post('/invoices/simple', [App\Http\Controllers\Api\N8nController::class, 'createInvoice'])
            ->name('api.invoices.simple');
        
        Route::post('/invoices/check', [App\Http\Controllers\Api\N8nController::class, 'checkInvoiceNumber'])
            ->name('api.invoices.check');
        
        Route::post('/invoices/remove-duplicate', [App\Http\Controllers\Api\N8nController::class, 'handleDuplicateInvoice'])
            ->name('api.invoices.remove-duplicate');
        
        Route::post('/webhooks/n8n', [App\Http\Controllers\Api\N8nController::class, 'webhook'])
            ->name('api.webhooks.n8n');
            
        Route::post('/webhooks/ai-chat', [App\Http\Controllers\Api\N8nController::class, 'aiChatWebhook'])
            ->name('api.webhooks.ai-chat');
    });
});

// Authentication Routes - MOVER PARA O FINAL
require __DIR__.'/auth.php';

// Client Area Routes
require __DIR__.'/client.php';
