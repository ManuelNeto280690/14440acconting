<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Override default authenticate middleware
        $middleware->replace(
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\Authenticate::class
        );
        
        // Override default CSRF middleware to use our custom one
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );
        
        // Register custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminAuth::class,
            'tenant' => \App\Http\Middleware\TenantAuth::class,
            'check.role' => \App\Http\Middleware\CheckRole::class,
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
        
        // Configure authentication redirects
        $middleware->redirectGuestsTo(function ($request) {
            // Determine the appropriate login route based on the guard
            $guard = $request->route() ? $request->route()->getAction('middleware') : [];
            
            if (is_array($guard)) {
                $guard = collect($guard)->first(function ($middleware) {
                    return str_contains($middleware, 'auth:');
                });
            }
            
            if ($guard && str_contains($guard, 'auth:admin')) {
                return route('admin.login');
            } elseif ($guard && str_contains($guard, 'auth:tenant')) {
                return route('tenant.login');
            } elseif ($guard && str_contains($guard, 'auth:client')) {
                return route('client.login');
            }
            
            // Default to the default login route
            return route('default.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
