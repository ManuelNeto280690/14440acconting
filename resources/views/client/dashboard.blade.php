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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Documents</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-documents">{{ $stats['total_documents'] }}</p>
                        @if($stats['documents_growth'] != 0)
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Invoices</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-invoices">{{ $stats['total_invoices'] }}</p>
                        <div class="flex items-center mt-2 text-sm">
                            <span class="text-green-600 font-medium">{{ $stats['paid_invoices'] }} paid</span>
                            <span class="text-gray-400 mx-2">•</span>
                            <span class="text-yellow-600 font-medium">{{ $stats['pending_invoices'] }} pending</span>
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Amount</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-amount">${{ number_format($stats['total_amount'], 2) }}</p>
                        <div class="mt-2">
                            <span class="text-sm text-green-600 font-medium">${{ number_format($stats['paid_amount'], 2) }} paid</span>
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Messages</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-messages">{{ $stats['total_messages'] }}</p>
                        @if($stats['unread_messages'] > 0)
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('client.documents.create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-upload me-2"></i>Upload Document
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('client.invoices.index') }}" class="btn btn-success w-100">
                                <i class="fas fa-file-invoice-dollar me-2"></i>View Invoices
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('client.messages.index') }}" class="btn btn-info w-100">
                                <i class="fas fa-comment me-2"></i>Messages
                                @if($stats['unread_messages'] > 0)
                                    <span class="badge badge-light ms-1">{{ $stats['unread_messages'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('client.profile.show') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="row mb-4">
        <!-- Document Processing Status -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Document Processing</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-success">Processed</span>
                            <span class="font-weight-bold">{{ $stats['processed_documents'] }}</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $stats['total_documents'] > 0 ? ($stats['processed_documents'] / $stats['total_documents']) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                    
                    @if($stats['processing_documents'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-warning">Processing</span>
                                <span class="font-weight-bold">{{ $stats['processing_documents'] }}</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ $stats['total_documents'] > 0 ? ($stats['processing_documents'] / $stats['total_documents']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($stats['failed_documents'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-danger">Failed</span>
                                <span class="font-weight-bold">{{ $stats['failed_documents'] }}</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-danger" 
                                     style="width: {{ $stats['total_documents'] > 0 ? ($stats['failed_documents'] / $stats['total_documents']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Status -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Invoice Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-success">Paid</span>
                            <span class="font-weight-bold">${{ number_format($stats['paid_amount'], 2) }}</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $stats['total_amount'] > 0 ? ($stats['paid_amount'] / $stats['total_amount']) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                    
                    @if($stats['pending_amount'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-warning">Pending</span>
                                <span class="font-weight-bold">${{ number_format($stats['pending_amount'], 2) }}</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ $stats['total_amount'] > 0 ? ($stats['pending_amount'] / $stats['total_amount']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($stats['overdue_amount'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-danger">Overdue</span>
                                <span class="font-weight-bold">${{ number_format($stats['overdue_amount'], 2) }}</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-danger" 
                                     style="width: {{ $stats['total_amount'] > 0 ? ($stats['overdue_amount'] / $stats['total_amount']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">Payment Rate: {{ $stats['payment_rate'] }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Storage Usage -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Storage Usage</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="h4 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['storage_used_mb'] }} MB
                        </div>
                        <p class="text-muted mb-3">Used Storage</p>
                        
                        <div class="progress mb-3">
                            <div class="progress-bar bg-info" 
                                 style="width: {{ min(($stats['storage_used_mb'] / 1000) * 100, 100) }}%">
                            </div>
                        </div>
                        
                        <small class="text-muted">
                            {{ 1000 - $stats['storage_used_mb'] }} MB remaining
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Documents -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Documents</h6>
                    <a href="{{ route('client.documents.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentDocuments->count() > 0)
                        <div class="list-group list-group-flush" id="recent-documents">
                            @foreach($recentDocuments as $document)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $document->name }}</h6>
                                        <p class="mb-1 text-muted">{{ strtoupper($document->type) }} • {{ number_format($document->size / 1024, 2) }} KB</p>
                                        <small class="text-muted">{{ $document->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div>
                                        <span class="badge badge-{{ $document->status === 'processed' ? 'success' : ($document->status === 'processing' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($document->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No documents uploaded yet.</p>
                            <a href="{{ route('client.documents.create') }}" class="btn btn-primary">
                                Upload Your First Document
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Invoices</h6>
                    <a href="{{ route('client.invoices.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentInvoices->count() > 0)
                        <div class="list-group list-group-flush" id="recent-invoices">
                            @foreach($recentInvoices as $invoice)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $invoice->invoice_number }}</h6>
                                        <p class="mb-1 text-muted">${{ number_format($invoice->amount, 2) }}</p>
                                        <small class="text-muted">Due: {{ $invoice->due_date->format('M d, Y') }}</small>
                                    </div>
                                    <div>
                                        <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice-dollar fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No invoices yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    @if($recentMessages->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Messages</h6>
                        <div>
                            @if($stats['unread_messages'] > 0)
                                <button class="btn btn-sm btn-outline-warning me-2" onclick="markAllRead()">
                                    Mark All Read
                                </button>
                            @endif
                            <a href="{{ route('client.messages.index') }}" class="btn btn-sm btn-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush" id="recent-messages">
                            @foreach($recentMessages as $message)
                                <div class="list-group-item {{ $message->status === 'unread' ? 'bg-light' : '' }}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ ucfirst($message->type) }} Message</h6>
                                        <small>{{ $message->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ Str::limit($message->message, 100) }}</p>
                                    @if($message->status === 'unread')
                                        <span class="badge badge-warning">Unread</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh dashboard every 5 minutes
    setInterval(refreshDashboard, 300000);
});

function refreshDashboard() {
    // Show loading indicator
    const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
    const originalText = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
    refreshBtn.disabled = true;

    // Fetch updated statistics
    fetch('{{ route("client.dashboard.data") }}?type=stats')
        .then(response => response.json())
        .then(data => {
            // Update statistics
            document.getElementById('total-documents').textContent = data.total_documents;
            document.getElementById('total-invoices').textContent = data.total_invoices;
            document.getElementById('total-amount').textContent = '$' + new Intl.NumberFormat().format(data.total_amount);
            document.getElementById('total-messages').textContent = data.total_messages;
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
        })
        .finally(() => {
            // Restore button
            refreshBtn.innerHTML = originalText;
            refreshBtn.disabled = false;
        });
}

function markAllRead() {
    fetch('{{ route("client.dashboard.mark-notifications-read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread styling
            document.querySelectorAll('#recent-messages .list-group-item.bg-light').forEach(item => {
                item.classList.remove('bg-light');
            });
            
            // Remove unread badges
            document.querySelectorAll('#recent-messages .badge-warning').forEach(badge => {
                badge.remove();
            });
            
            // Update unread count
            const unreadBadge = document.querySelector('.btn-info .badge');
            if (unreadBadge) {
                unreadBadge.remove();
            }
            
            // Show success message
            showAlert('All messages marked as read.', 'success');
        }
    })
    .catch(error => {
        console.error('Error marking messages as read:', error);
        showAlert('Failed to mark messages as read.', 'error');
    });
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
@endpush