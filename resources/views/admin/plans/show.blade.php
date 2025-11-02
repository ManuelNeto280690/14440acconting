@extends('layouts.admin')

@section('title', 'Plan Details - ' . $plan->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.plans.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $plan->name }} Plan</h1>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.plans.edit', $plan) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-edit mr-2"></i>Edit Plan
            </a>
            @if($plan->is_active)
                <form action="{{ route('admin.plans.deactivate', $plan) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200"
                            onclick="return confirm('Are you sure you want to deactivate this plan?')">
                        <i class="fas fa-pause mr-2"></i>Deactivate
                    </button>
                </form>
            @else
                <form action="{{ route('admin.plans.activate', $plan) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-play mr-2"></i>Activate
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2">
            <!-- Plan Overview -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Plan Overview</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Plan Name</label>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div class="h-12 w-12 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                        <i class="fas fa-layer-group text-white text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-xl font-medium text-gray-900">{{ $plan->name }}</p>
                                    @if($plan->is_featured)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-star mr-1"></i>Featured Plan
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Pricing</label>
                            <p class="text-2xl font-bold text-gray-900">${{ number_format($plan->price, 2) }}</p>
                            <p class="text-sm text-gray-500">per {{ $plan->billing_cycle }}</p>
                            @if($plan->billing_cycle === 'yearly')
                                <p class="text-xs text-gray-400">${{ number_format($plan->price / 12, 2) }}/month when billed annually</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            @if($plan->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-2"></i>Inactive
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Billing Cycle</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $plan->billing_cycle === 'monthly' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($plan->billing_cycle) }}
                            </span>
                        </div>
                        @if($plan->stripe_price_id)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Stripe Price ID</label>
                                <p class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ $plan->stripe_price_id }}</p>
                            </div>
                        @endif
                        @if($plan->description)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                                <p class="text-gray-900">{{ $plan->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Plan Limits -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Plan Limits</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">
                                {{ $plan->max_users ? number_format($plan->max_users) : 'Unlimited' }}
                            </h4>
                            <p class="text-sm text-gray-500">Maximum Users</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-hdd text-green-600 text-xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">
                                {{ $plan->max_storage_gb ? $plan->max_storage_gb . ' GB' : 'Unlimited' }}
                            </h4>
                            <p class="text-sm text-gray-500">Storage Limit</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-file-alt text-purple-600 text-xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">
                                {{ $plan->max_documents ? number_format($plan->max_documents) : 'Unlimited' }}
                            </h4>
                            <p class="text-sm text-gray-500">Document Limit</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscriptions -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Subscriptions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tenant
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($plan->subscriptions->take(10) as $subscription)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                                    <span class="text-white font-medium text-xs">
                                                        {{ strtoupper(substr($subscription->tenant->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $subscription->tenant->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $subscription->tenant->domain }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($subscription->status === 'active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @elseif($subscription->status === 'trial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Trial
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ ucfirst($subscription->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $subscription->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.billing.show', $subscription) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No subscriptions yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($plan->subscriptions->count() > 10)
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
                        <a href="{{ route('admin.billing.index') }}?plan_id={{ $plan->id }}" 
                           class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            View All {{ $plan->subscriptions->count() }} Subscriptions
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Statistics -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Statistics</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Subscriptions</span>
                            <span class="text-lg font-semibold text-gray-900">{{ $stats['total_subscriptions'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Active Subscriptions</span>
                            <span class="text-lg font-semibold text-green-600">{{ $stats['active_subscriptions'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Trial Subscriptions</span>
                            <span class="text-lg font-semibold text-yellow-600">{{ $stats['trial_subscriptions'] }}</span>
                        </div>
                        <div class="flex justify-between items-center border-t pt-4">
                            <span class="text-sm font-medium text-gray-600">Monthly Revenue</span>
                            <span class="text-lg font-bold text-blue-600">${{ number_format($stats['monthly_revenue'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Yearly Revenue</span>
                            <span class="text-lg font-bold text-purple-600">${{ number_format($stats['yearly_revenue'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Features -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Plan Features</h3>
                </div>
                <div class="p-6">
                    @if($plan->features)
                        <ul class="list-disc list-inside space-y-1">
                            @foreach(is_array($plan->features) ? $plan->features : json_decode($plan->features, true) ?? [] as $feature)
                                <li class="text-gray-600">{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 text-sm">No features defined for this plan.</p>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('admin.plans.edit', $plan) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-edit mr-2"></i>Edit Plan
                        </a>
                        
                        <a href="{{ route('admin.billing.index') }}?plan_id={{ $plan->id }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-users mr-2"></i>View Subscriptions
                        </a>

                        <a href="{{ route('admin.billing.report') }}?plan_id={{ $plan->id }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-chart-line mr-2"></i>View Reports
                        </a>

                        @if($plan->subscriptions->count() === 0)
                            <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50"
                                        onclick="return confirm('Are you sure you want to delete this plan? This action cannot be undone.')">
                                    <i class="fas fa-trash mr-2"></i>Delete Plan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection