@extends('layouts.admin')

@section('title', 'Billing & Subscriptions')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Billing & Subscriptions</h1>
            <p class="text-gray-600">Monitor subscription revenue and manage billing</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition-colors">
                <i class="fas fa-filter w-4 h-4 mr-2"></i>
                Filter
            </button>
            <a href="{{ route('admin.billing.report') }}" 
               class="inline-flex items-center px-4 py-2 gradient-success text-white rounded-xl text-sm font-medium hover-lift transition-all">
                <i class="fas fa-chart-line w-4 h-4 mr-2"></i>
                Generate Report
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stats-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Subscriptions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_subscriptions'] }}</p>
                </div>
                <div class="w-12 h-12 gradient-primary rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="stats-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Active Subscriptions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_subscriptions'] }}</p>
                </div>
                <div class="w-12 h-12 gradient-success rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="stats-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Trial Subscriptions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['trial_subscriptions'] }}</p>
                </div>
                <div class="w-12 h-12 gradient-warning rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="stats-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">${{ number_format($stats['monthly_revenue'], 0) }}</p>
                </div>
                <div class="w-12 h-12 gradient-secondary rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-white text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Table -->
    <div class="bg-white rounded-xl lg:rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 lg:py-4 text-left">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Tenant</span>
                                <i class="fas fa-sort text-gray-400 text-xs cursor-pointer hover:text-blue-600"></i>
                            </div>
                        </th>
                        <th class="px-4 lg:px-6 py-3 lg:py-4 text-left">
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Plan</span>
                                <i class="fas fa-sort text-gray-400 text-xs cursor-pointer hover:text-blue-600"></i>
                            </div>
                        </th>
                        <th class="px-4 lg:px-6 py-3 lg:py-4 text-left">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Status</span>
                        </th>
                        <th class="px-4 lg:px-6 py-3 lg:py-4 text-left">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Revenue</span>
                        </th>
                        <th class="px-4 lg:px-6 py-3 lg:py-4 text-left hidden xl:table-cell">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Trial Ends</span>
                        </th>
                        <th class="px-4 lg:px-6 py-3 lg:py-4 text-left hidden lg:table-cell">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Created</span>
                        </th>
                        <th class="px-4 lg:px-6 py-3 lg:py-4 text-center">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptions as $subscription)
                        <tr class="table-row">
                            <td class="px-4 lg:px-6 py-4 lg:py-5">
                                <div class="flex items-center space-x-2 lg:space-x-3">
                                    <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 hidden sm:block">
                                    <div class="flex items-center space-x-2 lg:space-x-3">
                                        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg lg:rounded-xl flex items-center justify-center flex-shrink-0">
                                            <span class="text-white font-bold text-xs lg:text-sm">
                                                {{ strtoupper(substr($subscription->tenant->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-900 text-sm lg:text-base truncate">{{ $subscription->tenant->name }}</p>
                                            <p class="text-xs text-gray-500 hidden lg:block">{{ $subscription->tenant->domain }}</p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 lg:px-6 py-4 lg:py-5">
                                <p class="text-xs text-gray-500 hidden lg:block">Plan</p>
                                <p class="font-semibold text-gray-900 text-sm lg:text-base">{{ $subscription->plan->name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($subscription->plan->billing_cycle) }}</p>
                            </td>
                            <td class="px-4 lg:px-6 py-4 lg:py-5">
                                <div class="flex items-center justify-center space-x-1 lg:space-x-2">
                                    @if($subscription->status === 'active')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <span class="hidden sm:inline">ACTIVE</span>
                                            <span class="sm:hidden">ACT</span>
                                        </span>
                                    @elseif($subscription->status === 'trial')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <span class="hidden sm:inline">TRIAL</span>
                                            <span class="sm:hidden">TRI</span>
                                        </span>
                                    @elseif($subscription->status === 'canceled')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <span class="hidden sm:inline">CANCELED</span>
                                            <span class="sm:hidden">CAN</span>
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <span class="hidden sm:inline">{{ strtoupper($subscription->status) }}</span>
                                            <span class="sm:hidden">{{ strtoupper(substr($subscription->status, 0, 3)) }}</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 lg:px-6 py-4 lg:py-5">
                                <p class="text-xs text-gray-500 hidden lg:block">Monthly</p>
                                <p class="font-bold text-gray-900 text-sm lg:text-lg">${{ number_format($subscription->plan->price, 0) }}</p>
                            </td>
                            <td class="px-4 lg:px-6 py-4 lg:py-5 hidden xl:table-cell">
                                @if($subscription->trial_ends_at)
                                    <div class="flex items-center space-x-1 lg:space-x-2">
                                        <i class="fas fa-calendar text-blue-500 text-xs lg:text-sm"></i>
                                        <span class="text-xs lg:text-sm font-semibold text-gray-700">{{ $subscription->trial_ends_at->format('M d, Y') }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 lg:px-6 py-4 lg:py-5 hidden lg:table-cell">
                                <div class="flex items-center space-x-1 lg:space-x-2">
                                    <i class="fas fa-calendar text-blue-500 text-xs lg:text-sm"></i>
                                    <span class="text-xs lg:text-sm font-semibold text-gray-700">{{ $subscription->created_at->format('M d, Y') }}</span>
                                </div>
                            </td>
                            <td class="px-4 lg:px-6 py-4 lg:py-5">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.billing.show', $subscription) }}" 
                                       class="w-8 h-8 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg flex items-center justify-center transition-colors" 
                                       title="View Details">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    @if($subscription->status === 'active')
                                        <form action="{{ route('admin.billing.cancel', $subscription) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to cancel this subscription?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg flex items-center justify-center transition-colors" 
                                                    title="Cancel Subscription">
                                                <i class="fas fa-ban text-xs"></i>
                                            </button>
                                        </form>
                                    @elseif($subscription->status === 'canceled')
                                    <form action="{{ route('admin.billing.reactivate', $subscription) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to reactivate this subscription?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="w-8 h-8 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg flex items-center justify-center transition-colors" 
                                                title="Reactivate Subscription">
                                            <i class="fas fa-play text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-credit-card text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No subscriptions found</h3>
                                    <p class="text-gray-500 mb-6">Subscriptions will appear here when tenants subscribe to plans.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($subscriptions->hasPages())
            <div class="bg-gray-50 px-4 lg:px-6 py-4 border-t border-gray-200">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection