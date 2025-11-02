@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Admin Dashboard</h1>
            <p class="text-gray-600">Monitor system performance and manage your platform</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition-colors">
                <i class="fas fa-calendar w-4 h-4 mr-2"></i>
                Last 30 days
            </button>
            <a href="{{ route('admin.billing.report') }}" 
               class="inline-flex items-center px-4 py-2 gradient-success text-white rounded-xl text-sm font-medium hover-lift transition-all">
                <i class="fas fa-download w-4 h-4 mr-2"></i>
                Export Report
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Tenants -->
        <div class="stats-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Tenants</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_tenants'] ?? 0 }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-sm text-green-600 font-medium">
                            <i class="fas fa-arrow-up mr-1"></i>+12%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs last month</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="stats-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Active Tenants</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_tenants'] ?? 0 }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-sm text-green-600 font-medium">
                            <i class="fas fa-arrow-up mr-1"></i>+8%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs last month</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Plans -->
        <div class="stats-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Plans</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_plans'] ?? 0 }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-sm text-blue-600 font-medium">
                            <i class="fas fa-minus mr-1"></i>0%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs last month</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-layer-group text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="stats-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-sm text-green-600 font-medium">
                            <i class="fas fa-arrow-up mr-1"></i>+24%
                        </span>
                        <span class="text-sm text-gray-500 ml-2">vs last month</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Tenants -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Tenants</h3>
                    <a href="{{ route('admin.tenants.index') }}" 
                       class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        View all
                    </a>
                </div>
                <div class="p-6">
                    @if(isset($recent_tenants) && count($recent_tenants) > 0)
                        <div class="space-y-4">
                            @foreach($recent_tenants as $tenant)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center">
                                            <span class="text-sm font-semibold text-white">{{ substr($tenant->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $tenant->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $tenant->domain }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $tenant->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                        <span class="text-sm text-gray-400">{{ $tenant->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-users text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-lg font-medium text-gray-900 mb-2">No tenants found</p>
                            <p class="text-gray-500 mb-6">Get started by creating your first tenant.</p>
                            <a href="{{ route('admin.tenants.create') }}" 
                               class="inline-flex items-center px-4 py-2 gradient-primary text-white rounded-xl text-sm font-medium hover-lift transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Create Tenant
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & System Status -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.tenants.create') }}" 
                       class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors group">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Create Tenant</p>
                            <p class="text-sm text-gray-600">Add new tenant to system</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.plans.create') }}" 
                       class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-xl transition-colors group">
                        <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-layer-group text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Create Plan</p>
                            <p class="text-sm text-gray-600">Add subscription plan</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.billing.index') }}" 
                       class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-xl transition-colors group">
                        <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-credit-card text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">View Billing</p>
                            <p class="text-sm text-gray-600">Manage subscriptions</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.settings') }}" 
                       class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors group">
                        <div class="w-10 h-10 bg-gray-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-cog text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Settings</p>
                            <p class="text-sm text-gray-600">System configuration</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">System Status</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">Database</span>
                        </div>
                        <span class="text-sm text-green-600 font-medium">Online</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">Cache</span>
                        </div>
                        <span class="text-sm text-green-600 font-medium">Active</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">Queue</span>
                        </div>
                        <span class="text-sm text-green-600 font-medium">Running</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">Storage</span>
                        </div>
                        <span class="text-sm text-yellow-600 font-medium">78% Used</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                <button type="button" 
                        class="text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user-plus text-blue-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                New tenant <span class="font-medium">Acme Corp</span> was created
                            </p>
                            <p class="text-xs text-gray-500 mt-1">2 hours ago</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-credit-card text-green-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                Payment received from <span class="font-medium">Tech Solutions</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">4 hours ago</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-layer-group text-purple-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                New plan <span class="font-medium">Enterprise</span> was activated
                            </p>
                            <p class="text-xs text-gray-500 mt-1">6 hours ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection