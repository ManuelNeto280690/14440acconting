<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('admin');
    }

    /**
     * Display billing overview and subscriptions
     */
    public function index()
    {
        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'trial_subscriptions' => Subscription::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', now())->count(),
            'monthly_revenue' => Subscription::where('status', 'active')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('plans.billing_cycle', 'monthly')
                ->sum('plans.price'),
            'yearly_revenue' => Subscription::where('status', 'active')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('plans.billing_cycle', 'yearly')
                ->sum('plans.price'),
        ];

        return view('admin.billing.index', compact('subscriptions', 'stats'));
    }

    /**
     * Show subscription details
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan']);
        
        return view('admin.billing.show', compact('subscription'));
    }

    /**
     * Update subscription status or plan
     */
    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'status' => 'required|in:active,trial,canceled,expired',
            'plan_id' => 'nullable|exists:plans,id',
            'trial_ends_at' => 'nullable|date|after:today',
            'ends_at' => 'nullable|date|after:today',
        ]);

        DB::transaction(function () use ($request, $subscription) {
            $subscription->update([
                'status' => $request->status,
                'plan_id' => $request->plan_id ?: $subscription->plan_id,
                'trial_ends_at' => $request->trial_ends_at,
                'ends_at' => $request->ends_at,
            ]);
        });

        return redirect()
            ->route('admin.billing.show', $subscription)
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Cancel subscription
     */
    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'canceled',
            'ends_at' => now()->addDays(30), // Grace period
        ]);

        return redirect()
            ->route('admin.billing.index')
            ->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'active',
            'ends_at' => null,
        ]);

        // Carregar as relações necessárias antes do redirecionamento
        $subscription->load(['tenant', 'plan']);

        return redirect()
            ->route('admin.billing.show', $subscription)
            ->with('success', 'Subscription reactivated successfully.');
    }

    /**
     * Generate billing report
     */
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->get();

        $report = [
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
            'total_subscriptions' => $subscriptions->count(),
            'new_subscriptions' => $subscriptions->where('status', 'active')->count(),
            'trial_subscriptions' => $subscriptions->whereNotNull('trial_ends_at')->count(),
            'total_revenue' => $subscriptions->sum(function ($subscription) {
                return $subscription->plan->price ?? 0;
            }),
            'revenue_by_plan' => $subscriptions->groupBy('plan.name')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'revenue' => $group->sum(function ($subscription) {
                        return $subscription->plan->price ?? 0;
                    }),
                ];
            }),
        ];

        return view('admin.billing.report', compact('report', 'subscriptions'));
    }
}
