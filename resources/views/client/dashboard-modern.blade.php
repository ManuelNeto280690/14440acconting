@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ $client->name }}!</h1>
                <p class="text-gray-600 mt-2">Here's what's happening with your account today.</p>
                <div class="flex items-center mt-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Last updated: <span id="last-updated">{{ now()->format('M d, Y H:i') }}</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="refreshDashboard()" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
                <a href="{{ route('client.show') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    My Profile
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Documents Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Documents</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-documents">{{ $stats['total_documents'] ?? 0 }}</p>
                        @if(isset($stats['documents_growth']) && $stats['documents_growth'] != 0)
                            <div class="flex items-center mt-2">
                                <svg class="w-4 h-4 mr-1 {{ $stats['documents_growth'] > 0 ? 'text-green-500' : 'text-red-500' }}" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="{{ $stats['documents_growth'] > 0 ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6' }}"/>
                                </svg>
                                <span class="text-sm {{ $stats['documents_growth'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ abs($stats['documents_growth']) }}% from last month
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Invoices Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Invoices</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-invoices">{{ $stats['total_invoices'] ?? 0 }}</p>
                        <div class="flex items-center mt-2 text-sm">
                            <span class="text-green-600 font-medium">{{ $stats['paid_invoices'] ?? 0 }} paid</span>
                            <span class="text-gray-400 mx-2">•</span>
                            <span class="text-yellow-600 font-medium">{{ $stats['pending_invoices'] ?? 0 }} pending</span>
                        </div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Amount Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Amount</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-amount">${{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                        <div class="mt-2">
                            <span class="text-sm text-green-600 font-medium">${{ number_format($stats['paid_amount'] ?? 0, 2) }} paid</span>
                        </div>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-full">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Messages Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Messages</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-messages">{{ $stats['total_messages'] ?? 0 }}</p>
                        @if(isset($stats['unread_messages']) && $stats['unread_messages'] > 0)
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $stats['unread_messages'] }} unread
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="p-3 bg-yellow-50 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Quick Actions
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('client.documents.create') }}" 
                       class="flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload Document
                    </a>
                    <a href="{{ route('client.invoices') }}" 
                       class="flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                        View Invoices
                        @if(isset($stats['pending_invoices']) && $stats['pending_invoices'] > 0)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ $stats['pending_invoices'] }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('client.messages') }}" 
                       class="flex items-center justify-center px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Messages
                        @if(isset($stats['unread_messages']) && $stats['unread_messages'] > 0)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ $stats['unread_messages'] }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('client.edit') }}" 
                       class="flex items-center justify-center px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Document Processing Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Document Processing</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-green-600">Processed</span>
                                <span class="text-sm font-bold text-gray-900">{{ $stats['processed_documents'] ?? 0 }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ isset($stats['total_documents']) && $stats['total_documents'] > 0 ? (($stats['processed_documents'] ?? 0) / $stats['total_documents']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        
                        @if(isset($stats['processing_documents']) && $stats['processing_documents'] > 0)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-yellow-600">Processing</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $stats['processing_documents'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ ($stats['processing_documents'] / $stats['total_documents']) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if(isset($stats['failed_documents']) && $stats['failed_documents'] > 0)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-red-600">Failed</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $stats['failed_documents'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ ($stats['failed_documents'] / $stats['total_documents']) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Invoice Status</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-green-600">Paid</span>
                                <span class="text-sm font-bold text-gray-900">${{ number_format($stats['paid_amount'] ?? 0, 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ isset($stats['total_amount']) && $stats['total_amount'] > 0 ? (($stats['paid_amount'] ?? 0) / $stats['total_amount']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        
                        @if(isset($stats['pending_amount']) && $stats['pending_amount'] > 0)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-yellow-600">Pending</span>
                                    <span class="text-sm font-bold text-gray-900">${{ number_format($stats['pending_amount'], 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ ($stats['pending_amount'] / $stats['total_amount']) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if(isset($stats['overdue_amount']) && $stats['overdue_amount'] > 0)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-red-600">Overdue</span>
                                    <span class="text-sm font-bold text-gray-900">${{ number_format($stats['overdue_amount'], 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ ($stats['overdue_amount'] / $stats['total_amount']) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="text-center pt-2 border-t border-gray-200">
                            <span class="text-sm text-gray-600">Payment Rate: <strong>{{ $stats['payment_rate'] ?? 0 }}%</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Usage -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Storage Usage</h3>
                </div>
                <div class="p-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900 mb-2">
                            {{ $stats['storage_used_mb'] ?? 0 }} MB
                        </div>
                        <p class="text-gray-600 mb-4">Used Storage</p>
                        
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                            <div class="bg-blue-500 h-3 rounded-full transition-all duration-300" 
                                 style="width: {{ min((($stats['storage_used_mb'] ?? 0) / 1000) * 100, 100) }}%">
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600">
                            {{ 1000 - ($stats['storage_used_mb'] ?? 0) }} MB remaining
                        </p>
                        
                        @if(isset($stats['storage_used_mb']) && $stats['storage_used_mb'] > 800)
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    Storage almost full
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Documents -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Documents</h3>
                    <a href="{{ route('client.documents') }}" 
                       class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
                </div>
                <div class="p-6">
                    @if(isset($recentDocuments) && $recentDocuments->count() > 0)
                        <div class="space-y-4" id="recent-documents">
                            @foreach($recentDocuments as $document)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $document->name }}</h4>
                                        <p class="text-xs text-gray-600 mt-1">
                                            {{ strtoupper($document->type) }} • {{ number_format($document->size / 1024, 2) }} KB
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $document->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                   {{ $document->status === 'processed' ? 'bg-green-100 text-green-800' : 
                                                      ($document->status === 'processing' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($document->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-600 mb-4">No documents uploaded yet.</p>
                            <a href="{{ route('client.documents.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Upload Your First Document
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Invoices</h3>
                    <a href="{{ route('client.invoices') }}" 
                       class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
                </div>
                <div class="p-6">
                    @if(isset($recentInvoices) && $recentInvoices->count() > 0)
                        <div class="space-y-4" id="recent-invoices">
                            @foreach($recentInvoices as $invoice)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">${{ number_format($invoice->amount, 2) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                   {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                                      ($invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                            </svg>
                            <p class="text-gray-600">No invoices yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        @if(isset($recentMessages) && $recentMessages->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Messages</h3>
                    <div class="flex items-center space-x-3">
                        @if(isset($stats['unread_messages']) && $stats['unread_messages'] > 0)
                            <button onclick="markAllRead()" 
                                    class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">
                                Mark All Read
                            </button>
                        @endif
                        <a href="{{ route('client.messages') }}" 
                           class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4" id="recent-messages">
                        @foreach($recentMessages as $message)
                            <div class="p-4 {{ $message->status === 'unread' ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50' }} rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-sm font-medium text-gray-900">{{ ucfirst($message->type) }} Message</h4>
                                    <div class="flex items-center space-x-2">
                                        @if($message->status === 'unread')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Unread
                                            </span>
                                        @endif
                                        <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700">{{ Str::limit($message->message, 150) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh dashboard every 5 minutes
    setInterval(refreshDashboard, 300000);
    
    // Initialize tooltips and other interactive elements
    initializeInteractiveElements();
});

function refreshDashboard() {
    const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
    const originalContent = refreshBtn.innerHTML;
    
    // Show loading state
    refreshBtn.innerHTML = `
        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Refreshing...
    `;
    refreshBtn.disabled = true;

    // Fetch updated data
    fetch('{{ route("client.dashboard.data") }}?type=stats', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update statistics
        updateStatistics(data);
        
        // Update last updated time
        document.getElementById('last-updated').textContent = new Date().toLocaleString();
        
        // Show success notification
        showNotification('Dashboard refreshed successfully!', 'success');
    })
    .catch(error => {
        console.error('Error refreshing dashboard:', error);
        showNotification('Failed to refresh dashboard. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button
        refreshBtn.innerHTML = originalContent;
        refreshBtn.disabled = false;
    });
}

function updateStatistics(data) {
    // Update main statistics
    if (data.total_documents !== undefined) {
        document.getElementById('total-documents').textContent = data.total_documents;
    }
    if (data.total_invoices !== undefined) {
        document.getElementById('total-invoices').textContent = data.total_invoices;
    }
    if (data.total_amount !== undefined) {
        document.getElementById('total-amount').textContent = '$' + new Intl.NumberFormat().format(data.total_amount);
    }
    if (data.total_messages !== undefined) {
        document.getElementById('total-messages').textContent = data.total_messages;
    }
}

function markAllRead() {
    fetch('{{ route("client.messages.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread styling
            document.querySelectorAll('#recent-messages .bg-blue-50').forEach(item => {
                item.classList.remove('bg-blue-50', 'border', 'border-blue-200');
                item.classList.add('bg-gray-50');
            });
            
            // Remove unread badges
            document.querySelectorAll('#recent-messages .bg-blue-100').forEach(badge => {
                badge.remove();
            });
            
            // Update unread count in quick actions
            const unreadBadges = document.querySelectorAll('.bg-red-100');
            unreadBadges.forEach(badge => badge.remove());
            
            showNotification('All messages marked as read.', 'success');
        }
    })
    .catch(error => {
        console.error('Error marking messages as read:', error);
        showNotification('Failed to mark messages as read.', 'error');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"/>
            </svg>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

function initializeInteractiveElements() {
    // Add hover effects to cards
    const cards = document.querySelectorAll('.hover\\:shadow-md');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('transform', 'scale-105');
        });
        card.addEventListener('mouseleave', function() {
            this.classList.remove('transform', 'scale-105');
        });
    });
}
</script>
@endpush