<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Stripe webhooks
        'webhooks/stripe',
        
        // N8N API routes (domínio principal)
        'api/invoices/simple',
        'api/invoices/check',
        'api/invoices/remove-duplicate',
        'api/webhooks/n8n',
        'api/webhooks/ai-chat',
        
        // Padrões genéricos para webhooks e APIs
        '*/webhooks/*',
        '*/api/*',
        
        // Rotas específicas de tenant (se necessário)
        'tenant/*/api/*',
        'tenant/*/webhooks/*',
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}