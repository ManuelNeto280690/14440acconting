@extends('layouts.admin')

@section('title', 'Tenant Details - ' . $tenant->name)

@section('content')
<!-- Header with Gradient Background -->
<div class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.tenants.index') }}" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Tenants
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $tenant->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Tenant details and management</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                @if($tenant->is_active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-2"></i>
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-2"></i>
                        Inactive
                    </span>
                @endif
                
                <a href="{{ route('admin.tenants.edit', $tenant) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Tenant
                </a>
                
                @if($tenant->is_active)
                    <form action="{{ route('admin.tenants.toggle-status', $tenant) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                                onclick="return confirm('Are you sure you want to deactivate this tenant?')">
                            <i class="fas fa-pause mr-2"></i>
                            Deactivate
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.tenants.toggle-status', $tenant) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                            <i class="fas fa-play mr-2"></i>
                            Activate
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Basic Information -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-building text-blue-600 mr-3"></i>
                        Tenant Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Tenant Name</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $tenant->name }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Domain</label>
                            <p class="text-lg text-gray-900 font-mono bg-gray-50 px-3 py-1 rounded-md">{{ $tenant->domain }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Contact Email</label>
                            <p class="text-lg text-gray-900">
                                <a href="mailto:{{ $tenant->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $tenant->email }}
                                </a>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="text-lg text-gray-900">{{ $tenant->phone ?: 'Not provided' }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <div>
                                @if($tenant->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-2"></i>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-2"></i>Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Created</label>
                            <p class="text-lg text-gray-900">{{ $tenant->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    
                    @if($tenant->notes)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Notes</label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-900">{{ $tenant->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Database Information -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-database text-green-600 mr-3"></i>
                        Database Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Database Name</label>
                            <p class="text-lg text-gray-900 font-mono bg-gray-50 px-3 py-1 rounded-md">{{ $tenant->tenancy_db_name ?? 'tenant_' . $tenant->domain }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Database Status</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-database mr-2"></i>Connected
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-8">
            <!-- Subscription Information -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-credit-card text-purple-600 mr-3"></i>
                        Subscription
                    </h3>
                </div>
                <div class="p-6">
                    @if($tenant->subscription)
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-500">Plan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenant->subscription->plan->name }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-500">Price</label>
                                <p class="text-lg text-gray-900">${{ number_format($tenant->subscription->plan->price, 2) }}/{{ $tenant->subscription->plan->billing_cycle }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-500">Status</label>
                                <div>
                                    @if($tenant->subscription->status === 'active')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-2"></i>Active
                                        </span>
                                    @elseif($tenant->subscription->status === 'trial')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-2"></i>Trial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-2"></i>{{ ucfirst($tenant->subscription->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($tenant->subscription->trial_ends_at)
                                <div class="space-y-1">
                                    <label class="block text-sm font-medium text-gray-500">Trial Ends</label>
                                    <p class="text-lg text-gray-900">{{ $tenant->subscription->trial_ends_at->format('M d, Y') }}</p>
                                </div>
                            @endif
                            @if($tenant->subscription->ends_at)
                                <div class="space-y-1">
                                    <label class="block text-sm font-medium text-gray-500">Subscription Ends</label>
                                    <p class="text-lg text-gray-900">{{ $tenant->subscription->ends_at->format('M d, Y') }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-4xl text-yellow-400 mb-4"></i>
                            <p class="text-gray-600 mb-4">No active subscription</p>
                            <a href="{{ route('admin.tenants.edit', $tenant) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                <i class="fas fa-plus mr-2"></i>
                                Assign Plan
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar text-indigo-600 mr-3"></i>
                        Quick Stats
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-users text-blue-500 mr-3"></i>
                                <span class="text-sm font-medium text-gray-600">Total Users</span>
                            </div>
                            <span class="text-lg font-bold text-gray-900">{{ $tenant->users_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-user-tie text-green-500 mr-3"></i>
                                <span class="text-sm font-medium text-gray-600">Total Clients</span>
                            </div>
                            <span class="text-lg font-bold text-gray-900">{{ $tenant->clients_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-file-alt text-purple-500 mr-3"></i>
                                <span class="text-sm font-medium text-gray-600">Total Documents</span>
                            </div>
                            <span class="text-lg font-bold text-gray-900">{{ $tenant->documents_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-hdd text-orange-500 mr-3"></i>
                                <span class="text-sm font-medium text-gray-600">Storage Used</span>
                            </div>
                            <span class="text-lg font-bold text-gray-900">{{ $tenant->storage_used ?? '0 MB' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock text-yellow-600 mr-3"></i>
                        Recent Activity
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center p-3 bg-green-50 rounded-lg">
                            <i class="fas fa-user-plus text-green-500 mr-3"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Tenant created</p>
                                <p class="text-xs text-gray-500">{{ $tenant->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @if($tenant->subscription)
                            <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                                <i class="fas fa-credit-card text-blue-500 mr-3"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Subscription activated</p>
                                    <p class="text-xs text-gray-500">{{ $tenant->subscription->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endif
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-sync text-gray-400 mr-3"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Last updated</p>
                                <p class="text-xs text-gray-500">{{ $tenant->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection