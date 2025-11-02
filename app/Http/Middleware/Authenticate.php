<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
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
            } elseif ($guard && str_contains($guard, 'auth:web')) {
                return route('default.login'); // Web guard uses default login
            }
            
            // Default fallback to default login route
            return route('default.login');
        }
        
        return null;
    }
}