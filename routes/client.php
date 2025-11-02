<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
|
| Here you can register the client routes for your application.
| These routes are loaded by the RouteServiceProvider and are separate
| from tenant routes to avoid conflicts with the tenancy system.
|
*/

// Client Authentication Routes (Guest only)
Route::prefix('client')->middleware('guest:client')->group(function () {
    Route::get('/login', [App\Http\Controllers\Client\AuthController::class, 'showLoginForm'])->name('client.login');
    Route::post('/login', [App\Http\Controllers\Client\AuthController::class, 'login']);
    Route::get('/register', [App\Http\Controllers\Client\AuthController::class, 'showRegistrationForm'])->name('client.register');
    Route::post('/register', [App\Http\Controllers\Client\AuthController::class, 'register']);
    Route::get('/forgot-password', [App\Http\Controllers\Client\AuthController::class, 'showForgotPasswordForm'])->name('client.password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Client\AuthController::class, 'forgotPassword'])->name('client.password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Client\AuthController::class, 'showResetPasswordForm'])->name('client.password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Client\AuthController::class, 'resetPassword'])->name('client.password.update');
});

// Client Area Routes (Authenticated)
Route::prefix('client')->name('client.')->middleware(['auth:client', 'verified'])->group(function () {
    // Logout route
    Route::post('/logout', [App\Http\Controllers\Client\AuthController::class, 'logout'])->name('logout');
    
    // Dashboard Routes
    Route::get('/dashboard', [App\Http\Controllers\Client\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [App\Http\Controllers\Client\DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/monthly-data', [App\Http\Controllers\Client\DashboardController::class, 'getMonthlyData'])->name('dashboard.monthly-data');
    Route::get('/dashboard/data', [App\Http\Controllers\Client\DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    Route::post('/notifications/mark-read', [App\Http\Controllers\Client\DashboardController::class, 'markNotificationsAsRead'])->name('notifications.mark-read');
    Route::get('/profile-summary', [App\Http\Controllers\Client\DashboardController::class, 'getProfileSummary'])->name('profile.summary');
    
    // Client Information Routes
    Route::get('/', [App\Http\Controllers\Client\ClientController::class, 'show'])->name('show');
    Route::get('/edit', [App\Http\Controllers\Client\ClientController::class, 'edit'])->name('edit');
    Route::put('/', [App\Http\Controllers\Client\ClientController::class, 'update'])->name('update');
    Route::get('/change-password', [App\Http\Controllers\Client\ClientController::class, 'showChangePasswordForm'])->name('change-password');
    Route::put('/change-password', [App\Http\Controllers\Client\ClientController::class, 'updatePassword'])->name('change-password.update');
    
    // Profile Management Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\Client\ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [App\Http\Controllers\Client\ProfileController::class, 'update'])->name('update');
        Route::put('/password', [App\Http\Controllers\Client\ProfileController::class, 'updatePassword'])->name('password.update');
        Route::put('/preferences', [App\Http\Controllers\Client\ProfileController::class, 'updatePreferences'])->name('preferences.update');
        Route::get('/download-data', [App\Http\Controllers\Client\ProfileController::class, 'downloadData'])->name('download-data');
        Route::get('/delete-account', [App\Http\Controllers\Client\ProfileController::class, 'showDeleteAccount'])->name('delete-account');
        Route::delete('/delete-account', [App\Http\Controllers\Client\ProfileController::class, 'deleteAccount'])->name('delete-account.confirm');
        Route::get('/activity-log', [App\Http\Controllers\Client\ProfileController::class, 'activityLog'])->name('activity-log');
    });
    
    // Documents Routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [App\Http\Controllers\Client\ClientController::class, 'documents'])->name('index');
        Route::get('/create', function () {
            return view('client.documents.create');
        })->name('create');
        Route::post('/', [App\Http\Controllers\Client\DocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [App\Http\Controllers\Client\DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/download', [App\Http\Controllers\Client\DocumentController::class, 'download'])->name('download');
        Route::delete('/{document}', [App\Http\Controllers\Client\DocumentController::class, 'destroy'])->name('destroy');
    });
    
    // Invoices Routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [App\Http\Controllers\Client\ClientController::class, 'invoices'])->name('index');
        Route::get('/{invoice}', [App\Http\Controllers\Client\InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download', [App\Http\Controllers\Client\InvoiceController::class, 'download'])->name('download');
        Route::post('/{invoice}/pay', [App\Http\Controllers\Client\InvoiceController::class, 'pay'])->name('pay');
    });
    
    // Messages Routes
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [App\Http\Controllers\Client\ClientController::class, 'messages'])->name('index');
        Route::post('/', [App\Http\Controllers\Client\MessageController::class, 'store'])->name('store');
        Route::get('/{message}', [App\Http\Controllers\Client\MessageController::class, 'show'])->name('show');
        Route::put('/{message}/read', [App\Http\Controllers\Client\MessageController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\Client\MessageController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{message}', [App\Http\Controllers\Client\MessageController::class, 'destroy'])->name('destroy');
    });
    
    // AI Chat Routes
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/ai', [App\Http\Controllers\Client\ChatController::class, 'aiChat'])->name('ai');
        Route::post('/ai/send', [App\Http\Controllers\Client\ChatController::class, 'sendToAI'])->name('ai.send');
        Route::get('/ai/messages', [App\Http\Controllers\Client\ChatController::class, 'getAIMessages'])->name('ai.messages');
    });
});