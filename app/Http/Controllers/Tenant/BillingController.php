<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class BillingController extends Controller
{
    /**
     * Display billing overview for the tenant.
     */
    public function index(): View
    {
        $tenant = tenant();
        $subscription = $tenant->subscription;
        $availablePlans = Plan::where('is_active', true)->orderBy('price')->get();

        // Get billing history (if you have a payments/invoices table)
        $billingHistory = collect(); // Placeholder for billing history

        // Calculate usage statistics
        $stats = [
            'current_period_start' => $subscription?->starts_at,
            'current_period_end' => $subscription?->ends_at,
            'days_remaining' => $subscription?->ends_at ? now()->diffInDays($subscription->ends_at, false) : 0,
            'is_trial' => $subscription?->trial_ends_at && $subscription->trial_ends_at->isFuture(),
            'trial_days_remaining' => $subscription?->trial_ends_at ? now()->diffInDays($subscription->trial_ends_at, false) : 0,
        ];

        // Usage data for current period (documents, storage, API calls)
        $periodStart = $subscription?->starts_at;
        $periodEnd = $subscription?->ends_at ?? now();

        $usageData = [
            'documents_processed' => Document::where('status', Document::STATUS_PROCESSED)
                ->when($periodStart, fn($q) => $q->whereBetween('processed_at', [$periodStart, $periodEnd]))
                ->count(),
            'storage_used' => Document::when($periodStart, fn($q) => $q->whereBetween('created_at', [$periodStart, $periodEnd]))
                ->sum('file_size'),
            // TODO: replace placeholder with real metric from API logs or integrations
            'api_calls' => 0,
        ];

        // Define payment method payload for the view (from subscription metadata if present)
        $paymentMethod = null;
        if ($subscription && is_array($subscription->metadata ?? null)) {
            $pm = $subscription->metadata['payment_method'] ?? null;
            if (is_array($pm)) {
                $paymentMethod = [
                    'type' => $pm['type'] ?? 'unknown',
                    'brand' => $pm['brand'] ?? null,
                    'last4' => $pm['last4'] ?? null,
                    'exp_month' => $pm['exp_month'] ?? null,
                    'exp_year' => $pm['exp_year'] ?? null,
                    'description' => $pm['description'] ?? null,
                ];
            }
        }

        return view('tenant.billing.index', compact('subscription', 'availablePlans', 'billingHistory', 'stats', 'usageData', 'paymentMethod'));
    }

    /**
     * Show available plans for upgrade/downgrade.
     */
    public function plans(): View
    {
        $tenant = tenant();
        $currentSubscription = $tenant->subscription;
        $availablePlans = Plan::where('is_active', true)->orderBy('price')->get();

        return view('tenant.billing.plans', compact('currentSubscription', 'availablePlans'));
    }

    /**
     * Change subscription plan.
     */
    public function changePlan(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $tenant = tenant();
            $newPlan = Plan::findOrFail($request->plan_id);
            $subscription = $tenant->subscription;

            if (!$subscription) {
                return redirect()->back()
                    ->with('error', 'No active subscription found.');
            }

            if ($subscription->plan_id == $newPlan->id) {
                return redirect()->back()
                    ->with('info', 'You are already subscribed to this plan.');
            }

            DB::transaction(function () use ($subscription, $newPlan) {
                // Update subscription
                $subscription->update([
                    'plan_id' => $newPlan->id,
                    'amount' => $newPlan->price,
                    // Here you would handle prorating, Stripe updates, etc.
                ]);

                Log::info('Subscription plan changed', [
                    'tenant_id' => tenant('id'),
                    'old_plan_id' => $subscription->getOriginal('plan_id'),
                    'new_plan_id' => $newPlan->id,
                    'subscription_id' => $subscription->id,
                ]);
            });

            return redirect()->route('tenant.billing.index')
                ->with('success', "Successfully changed to {$newPlan->name} plan!");

        } catch (\Exception $e) {
            Log::error('Failed to change subscription plan', [
                'tenant_id' => tenant('id'),
                'plan_id' => $request->plan_id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to change plan. Please try again.');
        }
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
            'cancel_immediately' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $tenant = tenant();
            $subscription = $tenant->subscription;

            if (!$subscription) {
                return redirect()->back()
                    ->with('error', 'No active subscription found.');
            }

            if ($subscription->status === 'cancelled') {
                return redirect()->back()
                    ->with('info', 'Subscription is already cancelled.');
            }

            DB::transaction(function () use ($subscription, $request) {
                $cancelAt = $request->boolean('cancel_immediately') ? now() : $subscription->ends_at;

                $subscription->update([
                    'status' => 'cancelled',
                    'canceled_at' => now(),
                    'ends_at' => $cancelAt,
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'cancellation_reason' => $request->reason,
                        'cancelled_by' => auth()->id(),
                        'cancelled_at' => now()->toISOString(),
                    ]),
                ]);

                Log::info('Subscription cancelled', [
                    'tenant_id' => tenant('id'),
                    'subscription_id' => $subscription->id,
                    'reason' => $request->reason,
                    'immediate' => $request->boolean('cancel_immediately'),
                ]);
            });

            $message = $request->boolean('cancel_immediately') 
                ? 'Subscription cancelled immediately.' 
                : 'Subscription will be cancelled at the end of the current billing period.';

            return redirect()->route('tenant.billing.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to cancel subscription', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to cancel subscription. Please try again.');
        }
    }

    /**
     * Resume cancelled subscription.
     */
    public function resume(): RedirectResponse
    {
        try {
            $tenant = tenant();
            $subscription = $tenant->subscription;

            if (!$subscription) {
                return redirect()->back()
                    ->with('error', 'No subscription found.');
            }

            if ($subscription->status !== 'cancelled') {
                return redirect()->back()
                    ->with('info', 'Subscription is not cancelled.');
            }

            if ($subscription->ends_at && $subscription->ends_at->isPast()) {
                return redirect()->back()
                    ->with('error', 'Cannot resume expired subscription. Please create a new subscription.');
            }

            DB::transaction(function () use ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'canceled_at' => null,
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'resumed_by' => auth()->id(),
                        'resumed_at' => now()->toISOString(),
                    ]),
                ]);

                Log::info('Subscription resumed', [
                    'tenant_id' => tenant('id'),
                    'subscription_id' => $subscription->id,
                ]);
            });

            return redirect()->route('tenant.billing.index')
                ->with('success', 'Subscription resumed successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to resume subscription', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to resume subscription. Please try again.');
        }
    }

    /**
     * Show billing history.
     */
    public function history(): View
    {
        $tenant = tenant();
        $subscription = $tenant->subscription;
        
        // Here you would get actual billing history from payments/invoices table
        $billingHistory = collect(); // Placeholder

        return view('tenant.billing.history', compact('subscription', 'billingHistory'));
    }

    /**
     * Download invoice (if you have invoice generation).
     */
    public function downloadInvoice(string $invoiceId): RedirectResponse
    {
        try {
            // Here you would generate and download the invoice
            // This is a placeholder implementation
            
            Log::info('Invoice download requested', [
                'tenant_id' => tenant('id'),
                'invoice_id' => $invoiceId,
            ]);

            return redirect()->back()
                ->with('info', 'Invoice download feature coming soon.');

        } catch (\Exception $e) {
            Log::error('Failed to download invoice', [
                'tenant_id' => tenant('id'),
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to download invoice.');
        }
    }

    /**
     * Update payment method.
     */
    public function updatePaymentMethod(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $tenant = tenant();
            $subscription = $tenant->subscription;

            if (!$subscription) {
                return redirect()->back()
                    ->with('error', 'No active subscription found.');
            }

            // Here you would update the payment method with Stripe or other payment provider
            // This is a placeholder implementation

            Log::info('Payment method update requested', [
                'tenant_id' => tenant('id'),
                'subscription_id' => $subscription->id,
                'payment_method_id' => $request->payment_method_id,
            ]);

            return redirect()->route('tenant.billing.index')
                ->with('success', 'Payment method updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update payment method', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update payment method. Please try again.');
        }
    }

    /**
     * Get subscription usage data (API endpoint).
     */
    public function getUsage(): JsonResponse
    {
        try {
            $tenant = tenant();
            $subscription = $tenant->subscription;

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found.',
                ], 404);
            }

            // Here you would calculate actual usage based on your metrics
            $usage = [
                'current_period' => [
                    'start' => $subscription->starts_at,
                    'end' => $subscription->ends_at,
                ],
                'usage' => [
                    'documents_processed' => 0, // Placeholder
                    'api_calls' => 0, // Placeholder
                    'storage_used' => 0, // Placeholder
                ],
                'limits' => [
                    'documents_limit' => $subscription->plan->features['documents_limit'] ?? null,
                    'api_calls_limit' => $subscription->plan->features['api_calls_limit'] ?? null,
                    'storage_limit' => $subscription->plan->features['storage_limit'] ?? null,
                ],
            ];

            return response()->json($usage);

        } catch (\Exception $e) {
            Log::error('Failed to get usage data', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get usage data.',
            ], 500);
        }
    }

    /**
     * Show subscription settings.
     */
    public function settings(): View
    {
        $tenant = tenant();
        $subscription = $tenant->subscription;

        return view('tenant.billing.settings', compact('subscription'));
    }

    /**
     * Update billing settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'auto_renew' => 'boolean',
            'billing_email' => 'nullable|email',
            'invoice_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $tenant = tenant();
            $subscription = $tenant->subscription;

            if (!$subscription) {
                return redirect()->back()
                    ->with('error', 'No active subscription found.');
            }

            $subscription->update([
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'auto_renew' => $request->boolean('auto_renew'),
                    'billing_email' => $request->billing_email,
                    'invoice_notes' => $request->invoice_notes,
                    'settings_updated_at' => now()->toISOString(),
                    'settings_updated_by' => auth()->id(),
                ]),
            ]);

            Log::info('Billing settings updated', [
                'tenant_id' => tenant('id'),
                'subscription_id' => $subscription->id,
            ]);

            return redirect()->route('tenant.billing.settings')
                ->with('success', 'Billing settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update billing settings', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update settings. Please try again.');
        }
    }
}
