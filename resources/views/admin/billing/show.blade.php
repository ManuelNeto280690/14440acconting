@extends('layouts.admin')

@section('title', 'Subscription Details - ' . $subscription->tenant->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.billing.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Subscription Details</h1>
        </div>
        <div class="flex space-x-3">
            @if($subscription->status === 'active')
                <form action="{{ route('admin.billing.cancel', $subscription) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200"
                            onclick="return confirm('Are you sure you want to cancel this subscription?')">
                        <i class="fas fa-ban mr-2"></i>Cancel Subscription
                    </button>
                </form>
            @elseif($subscription->status === 'cancelled')
                <form action="{{ route('admin.billing.reactivate', $subscription) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-play mr-2"></i>Reactivate
                    </button>
                </form>
            @elseif($subscription->status === 'canceled')
                <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                    <i class="fas fa-times-circle mr-2"></i>Canceled
                </span>
            @elseif($subscription->status === 'cancelled')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <i class="fas fa-times-circle mr-2"></i>Cancelled
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    {{ ucfirst($subscription->status) }}
                </span>
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
            <!-- Subscription Overview -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Subscription Overview</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tenant</label>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                        <span class="text-white font-medium text-sm">
                                            {{ strtoupper(substr($subscription->tenant->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-medium text-gray-900">{{ $subscription->tenant->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $subscription->tenant->domain }}</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Plan</label>
                            <p class="text-lg text-gray-900">{{ $subscription->plan->name }}</p>
                            <p class="text-sm text-gray-500">${{ number_format($subscription->plan->price, 2) }}/{{ $subscription->plan->billing_cycle }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            @if($subscription->status === 'active')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>Active
                                </span>
                            @elseif($subscription->status === 'trial')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-2"></i>Trial
                                </span>
                            @elseif($subscription->status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-2"></i>Cancelled
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                            <p class="text-lg text-gray-900">{{ $subscription->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                        @if($subscription->trial_ends_at)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Trial Ends</label>
                                <p class="text-lg text-gray-900">{{ $subscription->trial_ends_at->format('M d, Y \a\t g:i A') }}</p>
                                <p class="text-sm text-gray-500">{{ $subscription->trial_ends_at->diffForHumans() }}</p>
                            </div>
                        @endif
                        @if($subscription->ends_at)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Subscription Ends</label>
                                <p class="text-lg text-gray-900">{{ $subscription->ends_at->format('M d, Y \a\t g:i A') }}</p>
                                <p class="text-sm text-gray-500">{{ $subscription->ends_at->diffForHumans() }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Update Subscription Form -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Update Subscription</h3>
                </div>
                <form action="{{ route('admin.billing.update', $subscription) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" 
                                    name="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="active" {{ $subscription->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="trial" {{ $subscription->status === 'trial' ? 'selected' : '' }}>Trial</option>
                                <option value="cancelled" {{ $subscription->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="expired" {{ $subscription->status === 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>

                        <div>
                            <label for="plan_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Plan
                            </label>
                            <select id="plan_id" 
                                    name="plan_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @foreach(\App\Models\Plan::all() as $plan)
                                    <option value="{{ $plan->id }}" {{ $subscription->plan_id == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} - ${{ number_format($plan->price, 2) }}/{{ $plan->billing_cycle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="trial_ends_at" class="block text-sm font-medium text-gray-700 mb-2">
                                Trial End Date
                            </label>
                            <input type="date" 
                                   id="trial_ends_at" 
                                   name="trial_ends_at" 
                                   value="{{ $subscription->trial_ends_at?->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Leave empty to remove trial period</p>
                        </div>

                        <div>
                            <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-2">
                                Subscription End Date
                            </label>
                            <input type="date" 
                                   id="ends_at" 
                                   name="ends_at" 
                                   value="{{ $subscription->ends_at?->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Leave empty for ongoing subscription</p>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>Update Subscription
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Plan Features -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Plan Features</h3>
                </div>
                <div class="p-6">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach(is_array($subscription->plan->features) ? $subscription->plan->features : json_decode($subscription->plan->features, true) ?? [] as $feature)
                            <li class="text-gray-600">{{ $feature }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Billing Information</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Plan Price</span>
                            <span class="text-lg font-semibold text-gray-900">${{ number_format($subscription->plan->price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Billing Cycle</span>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst($subscription->plan->billing_cycle) }}</span>
                        </div>
                        @if($subscription->plan->billing_cycle === 'monthly')
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Annual Revenue</span>
                                <span class="text-sm font-medium text-gray-900">${{ number_format($subscription->plan->price * 12, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center border-t pt-4">
                            <span class="text-sm font-medium text-gray-600">Total Revenue</span>
                            <span class="text-lg font-bold text-green-600">${{ number_format($subscription->plan->price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('admin.tenants.show', $subscription->tenant) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-building mr-2"></i>View Tenant Details
                        </a>
                        
                        <a href="{{ route('admin.tenants.edit', $subscription->tenant) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-edit mr-2"></i>Edit Tenant
                        </a>

                        <a href="{{ route('admin.billing.report') }}?tenant_id={{ $subscription->tenant->id }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-chart-line mr-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection