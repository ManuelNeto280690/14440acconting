@extends('layouts.tenant')

@section('title', 'Integration Details')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <a href="{{ route('tenant.integrations.index') }}" 
                   class="text-slate-600 hover:text-slate-900 mr-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">{{ $integration->name }}</h1>
                    <p class="text-slate-600 mt-2">{{ ucfirst($integration->service_name) }} Integration</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                @if($integration->canRetry())
                    <form method="POST" action="{{ route('tenant.integrations.test', $integration) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Test Connection
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('tenant.integrations.toggle', $integration) }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-{{ $integration->is_active ? 'amber' : 'emerald' }}-600 hover:bg-{{ $integration->is_active ? 'amber' : 'emerald' }}-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        {{ $integration->is_active ? 'Disable' : 'Enable' }}
                    </button>
                </form>

                <a href="{{ route('tenant.integrations.edit', $integration) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                    Edit
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Overview -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Status Overview</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $integration->is_active ? 'bg-emerald-100' : 'bg-slate-100' }} mb-2">
                            <svg class="w-6 h-6 {{ $integration->is_active ? 'text-emerald-600' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-900">{{ $integration->is_active ? 'Active' : 'Inactive' }}</p>
                        <p class="text-xs text-slate-500">Status</p>
                    </div>

                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full {{ $integration->getStatusBadgeColor() === 'bg-emerald-100 text-emerald-800' ? 'bg-emerald-100' : ($integration->getStatusBadgeColor() === 'bg-red-100 text-red-800' ? 'bg-red-100' : 'bg-slate-100') }} mb-2">
                            <svg class="w-6 h-6 {{ $integration->getStatusBadgeColor() === 'bg-emerald-100 text-emerald-800' ? 'text-emerald-600' : ($integration->getStatusBadgeColor() === 'bg-red-100 text-red-800' ? 'text-red-600' : 'text-slate-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-900">{{ ucfirst($integration->sync_status) }}</p>
                        <p class="text-xs text-slate-500">Sync Status</p>
                    </div>

                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-2">
                            <span class="text-blue-600 font-semibold text-sm">{{ $integration->retry_count }}</span>
                        </div>
                        <p class="text-sm font-medium text-slate-900">{{ $integration->retry_count }}/{{ $integration->max_retries }}</p>
                        <p class="text-xs text-slate-500">Retries</p>
                    </div>

                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 mb-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-900">
                            @if($integration->last_sync_at)
                                {{ $integration->last_sync_at->diffForHumans() }}
                            @else
                                Never
                            @endif
                        </p>
                        <p class="text-xs text-slate-500">Last Sync</p>
                    </div>
                </div>
            </div>

            <!-- Configuration Details -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Configuration</h2>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Service Name</label>
                            <p class="text-sm text-slate-900 bg-slate-50 px-3 py-2 rounded">{{ ucfirst($integration->service_name) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Integration Name</label>
                            <p class="text-sm text-slate-900 bg-slate-50 px-3 py-2 rounded">{{ $integration->name }}</p>
                        </div>
                    </div>

                    @if($integration->description)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <p class="text-sm text-slate-900 bg-slate-50 px-3 py-2 rounded">{{ $integration->description }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Webhook URL</label>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-slate-900 bg-slate-50 px-3 py-2 rounded flex-1 font-mono">{{ $integration->webhook_url }}</p>
                            <button onclick="copyToClipboard('{{ $integration->webhook_url }}')" 
                                    class="text-slate-600 hover:text-slate-900 p-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Max Retries</label>
                            <p class="text-sm text-slate-900 bg-slate-50 px-3 py-2 rounded">{{ $integration->max_retries }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Rate Limit</label>
                            <p class="text-sm text-slate-900 bg-slate-50 px-3 py-2 rounded">{{ $integration->rate_limit_per_minute }}/min</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Timeout</label>
                            <p class="text-sm text-slate-900 bg-slate-50 px-3 py-2 rounded">{{ $integration->timeout_seconds }}s</p>
                        </div>
                    </div>

                    @if($integration->settings)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Additional Settings</label>
                            <pre class="text-sm text-slate-900 bg-slate-50 px-3 py-2 rounded overflow-x-auto font-mono">{{ json_encode($integration->settings, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Error Details -->
            @if($integration->hasErrors())
                <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                    <h2 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Error Details
                    </h2>
                    
                    @if($integration->last_error)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h3 class="font-medium text-red-900 mb-2">Last Error</h3>
                            <p class="text-sm text-red-800 font-mono">{{ $integration->last_error }}</p>
                            @if($integration->last_error_at)
                                <p class="text-xs text-red-600 mt-2">{{ $integration->last_error_at->format('M j, Y g:i A') }}</p>
                            @endif
                        </div>
                    @endif

                    @if($integration->canRetry())
                        <div class="mt-4">
                            <form method="POST" action="{{ route('tenant.integrations.retry', $integration) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    Retry Now
                                </button>
                            </form>
                            <form method="POST" action="{{ route('tenant.integrations.reset-retry', $integration) }}" class="inline ml-2">
                                @csrf
                                <button type="submit" 
                                        class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    Reset Retry Count
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions</h3>
                
                <div class="space-y-3">
                    @if($integration->canRetry())
                        <form method="POST" action="{{ route('tenant.integrations.sync', $integration) }}" class="w-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Force Sync
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('tenant.integrations.edit', $integration) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Integration
                    </a>

                    <form method="POST" action="{{ route('tenant.integrations.destroy', $integration) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this integration? This action cannot be undone.')"
                          class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Integration
                        </button>
                    </form>
                </div>
            </div>

            <!-- Integration Info -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Integration Info</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Created:</span>
                        <span class="text-slate-900">{{ $integration->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Updated:</span>
                        <span class="text-slate-900">{{ $integration->updated_at->diffForHumans() }}</span>
                    </div>
                    @if($integration->last_sync_at)
                        <div class="flex justify-between">
                            <span class="text-slate-600">Last Sync:</span>
                            <span class="text-slate-900">{{ $integration->last_sync_at->format('M j, Y g:i A') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-600">ID:</span>
                        <span class="text-slate-900 font-mono text-xs">{{ $integration->id }}</span>
                    </div>
                </div>
            </div>

            <!-- Webhook Info -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Webhook Info</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Incoming Webhook URL</label>
                        <div class="flex items-center space-x-2">
                            <code class="text-xs bg-slate-100 px-2 py-1 rounded flex-1 break-all">
                                {{ route('webhooks.tenant.integration', ['tenant' => tenant('id'), 'integration' => $integration->id]) }}
                            </code>
                            <button onclick="copyToClipboard('{{ route('webhooks.tenant.integration', ['tenant' => tenant('id'), 'integration' => $integration->id]) }}')" 
                                    class="text-slate-600 hover:text-slate-900 p-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Required Headers</label>
                        <div class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">
                            X-Tenant-ID: {{ tenant('id') }}<br>
                            X-Webhook-Signature: [HMAC-SHA256]
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // You could show a toast notification here
            console.log('Copied to clipboard');
        });
    }

    // Auto-refresh if syncing
    @if($integration->sync_status === 'syncing')
        setTimeout(() => {
            window.location.reload();
        }, 10000); // Refresh every 10 seconds if syncing
    @endif
</script>
@endpush
@endsection