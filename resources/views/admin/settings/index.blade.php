@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">System Settings</h1>
                    <p class="mt-2 text-slate-600">Configure system-wide settings and preferences</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="clearCache()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Clear Cache
                    </button>
                    <button onclick="toggleMaintenance()" class="inline-flex items-center px-4 py-2 {{ $settings['maintenance_mode'] ? 'bg-green-600 hover:bg-green-700' : 'bg-orange-600 hover:bg-orange-700' }} text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        {{ $settings['maintenance_mode'] ? 'Disable' : 'Enable' }} Maintenance
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Settings Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Application Settings</h2>
                        <p class="text-sm text-slate-600">Configure basic application settings</p>
                    </div>
                    
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Application Settings -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="app_name" class="block text-sm font-medium text-slate-700 mb-2">Application Name</label>
                                <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" 
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="app_url" class="block text-sm font-medium text-slate-700 mb-2">Application URL</label>
                                <input type="url" id="app_url" name="app_url" value="{{ old('app_url', $settings['app_url']) }}" 
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Email Settings -->
                        <div class="border-t border-slate-200 pt-6">
                            <h3 class="text-md font-medium text-slate-900 mb-4">Email Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="mail_from_address" class="block text-sm font-medium text-slate-700 mb-2">From Email Address</label>
                                    <input type="email" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" 
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="mail_from_name" class="block text-sm font-medium text-slate-700 mb-2">From Name</label>
                                    <input type="text" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" 
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- System Limits -->
                        <div class="border-t border-slate-200 pt-6">
                            <h3 class="text-md font-medium text-slate-900 mb-4">System Limits</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="tenant_limit" class="block text-sm font-medium text-slate-700 mb-2">Maximum Tenants</label>
                                    <input type="number" id="tenant_limit" name="tenant_limit" value="{{ old('tenant_limit', $settings['tenant_limit']) }}" 
                                           min="1" max="1000" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="max_file_size" class="block text-sm font-medium text-slate-700 mb-2">Max File Size (KB)</label>
                                    <input type="number" id="max_file_size" name="max_file_size" value="{{ old('max_file_size', $settings['max_file_size']) }}" 
                                           min="1024" max="102400" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="allowed_file_types" class="block text-sm font-medium text-slate-700 mb-2">Allowed File Types</label>
                                <input type="text" id="allowed_file_types" name="allowed_file_types" value="{{ old('allowed_file_types', $settings['allowed_file_types']) }}" 
                                       placeholder="pdf,jpg,jpeg,png,doc,docx" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-slate-500">Comma-separated list of allowed file extensions</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6 border-t border-slate-200">
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- System Information -->
            <div class="space-y-6">
                <!-- System Status -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">System Status</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Maintenance Mode</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $settings['maintenance_mode'] ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                                {{ $settings['maintenance_mode'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Debug Mode</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $settings['debug_mode'] ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                                {{ $settings['debug_mode'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Cache</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $settings['cache_enabled'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $settings['cache_enabled'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">System Information</h2>
                        <button onclick="loadSystemInfo()" class="text-sm text-blue-600 hover:text-blue-700">Refresh</button>
                    </div>
                    <div id="system-info" class="p-6">
                        <div class="text-center text-slate-500">
                            <svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Loading system information...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Are you sure you want to clear the application cache?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.settings.clear-cache") }}';
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleMaintenance() {
    const action = {{ $settings['maintenance_mode'] ? 'false' : 'true' }};
    const message = action ? 'enable maintenance mode' : 'disable maintenance mode';
    
    if (confirm(`Are you sure you want to ${message}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.settings.toggle-maintenance") }}';
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

function loadSystemInfo() {
    const container = document.getElementById('system-info');
    container.innerHTML = '<div class="text-center text-slate-500"><svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Loading system information...</div>';
    
    fetch('{{ route("admin.settings.system-info") }}')
        .then(response => response.json())
        .then(data => {
            let html = '<div class="space-y-3">';
            for (const [key, value] of Object.entries(data)) {
                const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                html += `
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">${label}</span>
                        <span class="text-sm font-medium text-slate-900">${value}</span>
                    </div>
                `;
            }
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<div class="text-center text-red-500">Failed to load system information</div>';
        });
}

// Load system info on page load
document.addEventListener('DOMContentLoaded', loadSystemInfo);
</script>
@endsection