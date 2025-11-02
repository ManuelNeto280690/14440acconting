<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gera URL de reset adequada por contexto (tenant, client ou padrão)
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $email = method_exists($notifiable, 'getEmailForPasswordReset')
                ? $notifiable->getEmailForPasswordReset()
                : (property_exists($notifiable, 'email') ? $notifiable->email : null);

            // Contexto de tenancy ativo → usa rota de tenant
            if (function_exists('tenant') && tenant()) {
                return route('tenant.password.reset', ['token' => $token, 'email' => $email]);
            }

            // Notificação para Client model → usa rota de client
            if ($notifiable instanceof \App\Models\Client) {
                return route('client.password.reset', ['token' => $token, 'email' => $email]);
            }

            // Fallback padrão
            return route('password.reset', ['token' => $token, 'email' => $email]);
        });
    }
}
