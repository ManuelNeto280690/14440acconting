<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'admin']);
    }

    /**
     * Display a listing of the plans.
     */
    public function index()
    {
        $plans = Plan::withCount(['subscriptions'])
            ->orderBy('price')
            ->paginate(10);

        $stats = [
            'total_plans' => Plan::count(),
            'active_plans' => Plan::where('is_active', true)->count(),
            'total_subscriptions' => DB::table('subscriptions')->count(),
            'monthly_revenue' => DB::table('subscriptions')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.status', 'active')
                ->where('plans.billing_cycle', 'monthly')
                ->sum('plans.price'),
        ];

        return view('admin.plans.index', compact('plans', 'stats'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created plan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:plans,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'billing_cycle_days' => 'required|integer|min:1',
            'trial_days' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'limits' => 'nullable|array',
            'limits.max_users' => 'nullable|integer|min:1',
            'limits.max_storage_gb' => 'nullable|integer|min:1',
            'limits.max_documents' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'stripe_price_id' => 'nullable|string|max:255',
        ]);

        // Gerar slug automaticamente baseado no nome
        $validated['slug'] = Str::slug($validated['name']);
        
        // Garantir que o slug seja único
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Plan::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Adicionar UUID explícito
        $validated['id'] = Str::uuid();

        $plan = Plan::create($validated);

        Log::info('Plan created', [
            'plan_id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Display the specified plan.
     */
    public function show(Plan $plan)
    {
        $plan->load(['subscriptions.tenant']);
        
        $stats = [
            'total_subscriptions' => $plan->subscriptions->count(),
            'active_subscriptions' => $plan->subscriptions->where('status', 'active')->count(),
            'trial_subscriptions' => $plan->subscriptions->where('status', 'trial')->count(),
            'monthly_revenue' => $plan->billing_cycle === 'monthly' 
                ? $plan->subscriptions->where('status', 'active')->count() * $plan->price
                : 0,
            'yearly_revenue' => $plan->billing_cycle === 'yearly' 
                ? $plan->subscriptions->where('status', 'active')->count() * $plan->price
                : ($plan->subscriptions->where('status', 'active')->count() * $plan->price * 12),
        ];

        return view('admin.plans.show', compact('plan', 'stats'));
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified plan in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'max_users' => 'nullable|integer|min:1',
            'max_storage_gb' => 'nullable|integer|min:1',
            'max_documents' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'stripe_price_id' => 'nullable|string|max:255',
        ]);

        // Convert features array to JSON
        if (isset($validated['features'])) {
            $validated['features'] = json_encode(array_filter($validated['features']));
        }

        $plan->update($validated);

        Log::info('Plan updated', [
            'plan_id' => $plan->id,
            'name' => $plan->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified plan from storage.
     */
    public function destroy(Plan $plan)
    {
        // Check if plan has active subscriptions
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return redirect()->route('admin.plans.index')
                ->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $planName = $plan->name;
        $plan->delete();

        Log::info('Plan deleted', [
            'plan_name' => $planName,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }

    /**
     * Activate the specified plan.
     */
    public function activate(Plan $plan)
    {
        $plan->update(['is_active' => true]);

        Log::info('Plan activated', [
            'plan_id' => $plan->id,
            'name' => $plan->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Plan activated successfully.');
    }

    /**
     * Deactivate the specified plan.
     */
    public function deactivate(Plan $plan)
    {
        $plan->update(['is_active' => false]);

        Log::info('Plan deactivated', [
            'plan_id' => $plan->id,
            'name' => $plan->name,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Plan deactivated successfully.');
    }
}