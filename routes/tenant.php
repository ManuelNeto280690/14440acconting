<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Rota raiz - redireciona para login se não autenticado, dashboard se autenticado
    Route::get('/', function () {
        if (auth('tenant')->check()) {
            return redirect()->route('tenant.dashboard');
        }
        return redirect()->route('tenant.login');
    })->name('tenant.home');
    
    // Tenant Dashboard Route
    Route::get('/dashboard', [App\Http\Controllers\Tenant\DashboardController::class, 'index'])
        ->middleware('auth:tenant')
        ->name('tenant.dashboard');
    
    // Authentication Routes (Guest only for tenant guard)
    Route::middleware('guest:tenant')->group(function () {
        Route::get('/login', [App\Http\Controllers\Tenant\AuthController::class, 'showLoginForm'])->name('tenant.login');
        Route::post('/login', [App\Http\Controllers\Tenant\AuthController::class, 'login']);
        Route::get('/register', [App\Http\Controllers\Tenant\AuthController::class, 'showRegistrationForm'])->name('tenant.register');
        Route::post('/register', [App\Http\Controllers\Tenant\AuthController::class, 'register']);
        Route::get('/forgot-password', [App\Http\Controllers\Tenant\AuthController::class, 'showForgotPasswordForm'])->name('tenant.password.request');
        Route::post('/forgot-password', [App\Http\Controllers\Tenant\AuthController::class, 'forgotPassword'])->name('tenant.password.email');
        Route::get('/reset-password/{token}', [App\Http\Controllers\Tenant\AuthController::class, 'showResetPasswordForm'])->name('tenant.password.reset');
        Route::post('/reset-password', [App\Http\Controllers\Tenant\AuthController::class, 'resetPassword'])->name('tenant.password.update');
    });

    // Authenticated Routes
    Route::middleware('auth:tenant')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Tenant\AuthController::class, 'logout'])->name('tenant.logout');
        Route::get('/email/verify', [App\Http\Controllers\Tenant\AuthController::class, 'showVerifyEmailForm'])->name('tenant.verification.notice');
        Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Tenant\AuthController::class, 'verifyEmail'])->name('tenant.verification.verify');
        Route::post('/email/verification-notification', [App\Http\Controllers\Tenant\AuthController::class, 'resendVerificationEmail'])->name('tenant.verification.send');

        // Client Management Routes
        Route::prefix('clients')->name('tenant.clients.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\ClientController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Tenant\ClientController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Tenant\ClientController::class, 'store'])->name('store');
            Route::get('/{client}', [App\Http\Controllers\Tenant\ClientController::class, 'show'])->name('show');
            Route::get('/{client}/edit', [App\Http\Controllers\Tenant\ClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [App\Http\Controllers\Tenant\ClientController::class, 'update'])->name('update');
            Route::delete('/{client}', [App\Http\Controllers\Tenant\ClientController::class, 'destroy'])->name('destroy');
            Route::patch('/{client}/archive', [App\Http\Controllers\Tenant\ClientController::class, 'archive'])->name('archive');
            Route::patch('/{client}/restore', [App\Http\Controllers\Tenant\ClientController::class, 'restore'])->name('restore');
            Route::get('/export/csv', [App\Http\Controllers\Tenant\ClientController::class, 'export'])->name('export');
        });

        // Integration Management Routes
        Route::prefix('integrations')->name('tenant.integrations.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\IntegrationController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Tenant\IntegrationController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Tenant\IntegrationController::class, 'store'])->name('store');
            Route::get('/{integration}', [App\Http\Controllers\Tenant\IntegrationController::class, 'show'])->name('show');
            Route::get('/{integration}/edit', [App\Http\Controllers\Tenant\IntegrationController::class, 'edit'])->name('edit');
            Route::put('/{integration}', [App\Http\Controllers\Tenant\IntegrationController::class, 'update'])->name('update');
            Route::delete('/{integration}', [App\Http\Controllers\Tenant\IntegrationController::class, 'destroy'])->name('destroy');
            Route::post('/{integration}/test', [App\Http\Controllers\Tenant\IntegrationController::class, 'testConnection'])->name('test');
            Route::patch('/{integration}/toggle', [App\Http\Controllers\Tenant\IntegrationController::class, 'toggleStatus'])->name('toggle');
            Route::post('/{integration}/sync', [App\Http\Controllers\Tenant\IntegrationController::class, 'forceSync'])->name('sync');
            Route::patch('/{integration}/reset-retry', [App\Http\Controllers\Tenant\IntegrationController::class, 'resetRetryCount'])->name('reset-retry');
            Route::get('/stats/overview', [App\Http\Controllers\Tenant\IntegrationController::class, 'getStats'])->name('stats');
        });

        // Document Management Routes
        Route::prefix('documents')->name('tenant.documents.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\DocumentController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Tenant\DocumentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Tenant\DocumentController::class, 'store'])->name('store');
            Route::get('/{document}', [App\Http\Controllers\Tenant\DocumentController::class, 'show'])->name('show');
            Route::get('/{document}/edit', [App\Http\Controllers\Tenant\DocumentController::class, 'edit'])->name('edit');
            Route::put('/{document}', [App\Http\Controllers\Tenant\DocumentController::class, 'update'])->name('update');
            Route::put('/{document}/replace-file', [App\Http\Controllers\Tenant\DocumentController::class, 'replaceFile'])->name('replace-file');
            Route::delete('/{document}', [App\Http\Controllers\Tenant\DocumentController::class, 'destroy'])->name('destroy');
           // Route::get('/{document}/download', [App\Http\Controllers\Tenant\DocumentController::class, 'download'])->name('download');
            Route::post('/{document}/process', [App\Http\Controllers\Tenant\DocumentController::class, 'processDocument'])->name('process');
            Route::post('/{document}/reprocess', [App\Http\Controllers\Tenant\DocumentController::class, 'reprocess'])->name('reprocess');
            Route::post('/{document}/reset-processing', [App\Http\Controllers\Tenant\DocumentController::class, 'resetProcessing'])->name('reset-processing');
            Route::post('/{document}/share', [App\Http\Controllers\Tenant\DocumentController::class, 'share'])->name('share');
            Route::post('/bulk-upload', [App\Http\Controllers\Tenant\DocumentController::class, 'bulkUpload'])->name('bulk-upload');
            Route::delete('/bulk-delete', [App\Http\Controllers\Tenant\DocumentController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/processing', [App\Http\Controllers\Tenant\DocumentController::class, 'processing'])->name('processing');
            Route::get('/check-processing', [App\Http\Controllers\Tenant\DocumentController::class, 'checkProcessingStatus'])->name('check-processing');
            Route::get('/stats/overview', [App\Http\Controllers\Tenant\DocumentController::class, 'getStats'])->name('stats');
        });

        // Invoice Management Routes
        Route::prefix('invoices')->name('tenant.invoices.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\InvoiceController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Tenant\InvoiceController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Tenant\InvoiceController::class, 'store'])->name('store');
            Route::get('/{invoice}', [App\Http\Controllers\Tenant\InvoiceController::class, 'show'])->name('show');
            Route::get('/{invoice}/edit', [App\Http\Controllers\Tenant\InvoiceController::class, 'edit'])->name('edit');
            Route::put('/{invoice}', [App\Http\Controllers\Tenant\InvoiceController::class, 'update'])->name('update');
            Route::delete('/{invoice}', [App\Http\Controllers\Tenant\InvoiceController::class, 'destroy'])->name('destroy');
            Route::get('/{invoice}/pdf', [App\Http\Controllers\Tenant\InvoiceController::class, 'generatePdf'])->name('pdf');
            Route::post('/{invoice}/download', [App\Http\Controllers\Tenant\InvoiceController::class, 'download'])->name('download');
            Route::post('/{invoice}/mark-as-paid', [App\Http\Controllers\Tenant\InvoiceController::class, 'markAsPaid'])->name('mark-as-paid');
            Route::post('/{invoice}/send', [App\Http\Controllers\Tenant\InvoiceController::class, 'send'])->name('send');
            Route::patch('/{invoice}/status', [App\Http\Controllers\Tenant\InvoiceController::class, 'updateStatus'])->name('status');
            Route::get('/stats/overview', [App\Http\Controllers\Tenant\InvoiceController::class, 'getStats'])->name('stats');
        });

        // Billing Management Routes
        Route::prefix('billing')->name('tenant.billing.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\BillingController::class, 'index'])->name('index');
            Route::get('/plans', [App\Http\Controllers\Tenant\BillingController::class, 'plans'])->name('plans');
            Route::get('/history', [App\Http\Controllers\Tenant\BillingController::class, 'history'])->name('history');
            Route::get('/usage', [App\Http\Controllers\Tenant\BillingController::class, 'getUsage'])->name('usage');
            Route::get('/settings', [App\Http\Controllers\Tenant\BillingController::class, 'settings'])->name('settings');
            Route::post('/settings', [App\Http\Controllers\Tenant\BillingController::class, 'updateSettings'])->name('update-settings');
            Route::post('/subscribe/{plan}', [App\Http\Controllers\Tenant\BillingController::class, 'subscribe'])->name('subscribe');
            Route::post('/cancel', [App\Http\Controllers\Tenant\BillingController::class, 'cancel'])->name('cancel');
            Route::post('/resume', [App\Http\Controllers\Tenant\BillingController::class, 'resume'])->name('resume');
            Route::post('/change-plan/{plan}', [App\Http\Controllers\Tenant\BillingController::class, 'changePlan'])->name('change-plan');
            Route::get('/invoice/{invoice}', [App\Http\Controllers\Tenant\BillingController::class, 'downloadInvoice'])->name('invoice');
            Route::post('/update-payment-method', [App\Http\Controllers\Tenant\BillingController::class, 'updatePaymentMethod'])->name('update-payment-method');
        });

        // Chat/Messages Routes
        Route::prefix('chat')->name('tenant.chat.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\ChatController::class, 'index'])->name('index');
            Route::get('/conversation/{client?}', [App\Http\Controllers\Tenant\ChatController::class, 'conversation'])->name('conversation');
            Route::post('/send', [App\Http\Controllers\Tenant\ChatController::class, 'sendMessage'])->name('send');
            Route::get('/messages/{client}', [App\Http\Controllers\Tenant\ChatController::class, 'getMessages'])->name('messages');
            Route::post('/mark-read/{message}', [App\Http\Controllers\Tenant\ChatController::class, 'markAsRead'])->name('mark-read');
            Route::get('/unread-counts', [App\Http\Controllers\Tenant\ChatController::class, 'getUnreadCounts'])->name('unread-counts');
            Route::post('/upload', [App\Http\Controllers\Tenant\ChatController::class, 'uploadFiles'])->name('upload');
            
            // AI Chat Routes
            Route::get('/ai', [App\Http\Controllers\Tenant\ChatController::class, 'aiChat'])->name('ai');
            Route::post('/ai/send', [App\Http\Controllers\Tenant\ChatController::class, 'sendToAI'])->name('ai.send');
            Route::get('/ai/messages', [App\Http\Controllers\Tenant\ChatController::class, 'getAIMessages'])->name('ai.messages');
        });

        // Messages Routes (alias for Chat routes for backward compatibility)
        Route::prefix('messages')->name('tenant.messages.')->group(function () {
            Route::get('/', function(Request $request) {
                return redirect()->route('tenant.chat.index', $request->query());
            })->name('index');
            Route::get('/create', [App\Http\Controllers\Tenant\ChatController::class, 'create'])->name('create');
            Route::get('/{client}', function($client, Request $request) {
                return redirect()->route('tenant.chat.messages', ['client' => $client] + $request->query());
            })->name('show');
        });

        // Profile Routes
        Route::prefix('profile')->name('tenant.profile.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\ProfileController::class, 'show'])->name('show');
            Route::get('/edit', [App\Http\Controllers\Tenant\ProfileController::class, 'edit'])->name('edit');
            Route::put('/', [App\Http\Controllers\Tenant\ProfileController::class, 'update'])->name('update');
            Route::delete('/', [App\Http\Controllers\Tenant\ProfileController::class, 'destroy'])->name('destroy');
        });
    });
    
    // API Routes for external integrations (n8n, etc.)
    Route::prefix('api')->name('tenant.api.')->group(function () {
        Route::get('/documents/{document}/download', [App\Http\Controllers\Tenant\DocumentController::class, 'apiDownload'])
            ->name('documents.download');
    });
    
    // Web download route (requires authentication)
    Route::get('/{document}/download', [App\Http\Controllers\Tenant\DocumentController::class, 'download'])
        ->middleware('auth:tenant')
        ->name('tenant.documents.download');
});

        
