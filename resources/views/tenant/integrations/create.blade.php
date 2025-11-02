@extends('layouts.tenant')

@section('title', 'Create Integration')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('tenant.integrations.index') }}" 
               class="text-slate-600 hover:text-slate-900 mr-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Create Integration</h1>
                <p class="text-slate-600 mt-2">Connect your external services and configure webhooks</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('tenant.integrations.store') }}" class="space-y-8">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-6">Basic Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                        Integration Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror"
                           placeholder="My N8N Integration">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="service_name" class="block text-sm font-medium text-slate-700 mb-2">
                        Service Type <span class="text-red-500">*</span>
                    </label>
                    <select id="service_name" 
                            name="service_name" 
                            required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('service_name') border-red-300 @enderror">
                        <option value="">Select a service</option>
                        @foreach($serviceTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('service_name') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-300 @enderror"
                          placeholder="Brief description of this integration...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Connection Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-6">Connection Settings</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="api_key" class="block text-sm font-medium text-slate-700 mb-2">
                        API Key <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="api_key" 
                           name="api_key" 
                           required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('api_key') border-red-300 @enderror"
                           placeholder="Enter your API key">
                    @error('api_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="api_secret" class="block text-sm font-medium text-slate-700 mb-2">
                        API Secret
                    </label>
                    <input type="password" 
                           id="api_secret" 
                           name="api_secret" 
                           value="{{ old('api_secret') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('api_secret') border-red-300 @enderror"
                           placeholder="Enter your API secret (if required)">
                    @error('api_secret')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="webhook_url" class="block text-sm font-medium text-slate-700 mb-2">
                    Webhook URL <span class="text-red-500">*</span>
                </label>
                <input type="url" 
                       id="webhook_url" 
                       name="webhook_url" 
                       value="{{ old('webhook_url') }}"
                       required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('webhook_url') border-red-300 @enderror"
                       placeholder="https://your-service.com/webhook">
                @error('webhook_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-slate-500">
                    This URL will receive webhook notifications from our system.
                </p>
            </div>

            <div class="mt-6">
                <label for="webhook_secret" class="block text-sm font-medium text-slate-700 mb-2">
                    Webhook Secret
                </label>
                <input type="password" 
                       id="webhook_secret" 
                       name="webhook_secret" 
                       value="{{ old('webhook_secret') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('webhook_secret') border-red-300 @enderror"
                       placeholder="Enter webhook secret for HMAC verification">
                @error('webhook_secret')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-slate-500">
                    Used to verify webhook authenticity. Leave empty to auto-generate.
                </p>
            </div>
        </div>

        <!-- Advanced Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-6">Advanced Settings</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="max_retries" class="block text-sm font-medium text-slate-700 mb-2">
                        Max Retries
                    </label>
                    <input type="number" 
                           id="max_retries" 
                           name="max_retries" 
                           value="{{ old('max_retries', 3) }}"
                           min="0" 
                           max="10"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('max_retries') border-red-300 @enderror">
                    @error('max_retries')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rate_limit_per_minute" class="block text-sm font-medium text-slate-700 mb-2">
                        Rate Limit (per minute)
                    </label>
                    <input type="number" 
                           id="rate_limit_per_minute" 
                           name="rate_limit_per_minute" 
                           value="{{ old('rate_limit_per_minute', 60) }}"
                           min="1" 
                           max="1000"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('rate_limit_per_minute') border-red-300 @enderror">
                    @error('rate_limit_per_minute')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="timeout_seconds" class="block text-sm font-medium text-slate-700 mb-2">
                        Timeout (seconds)
                    </label>
                    <input type="number" 
                           id="timeout_seconds" 
                           name="timeout_seconds" 
                           value="{{ old('timeout_seconds', 30) }}"
                           min="5" 
                           max="300"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('timeout_seconds') border-red-300 @enderror">
                    @error('timeout_seconds')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="settings" class="block text-sm font-medium text-slate-700 mb-2">
                    Additional Settings (JSON)
                </label>
                <textarea id="settings" 
                          name="settings" 
                          rows="4"
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('settings') border-red-300 @enderror"
                          placeholder='{"custom_field": "value", "another_setting": true}'>{{ old('settings') }}</textarea>
                @error('settings')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-slate-500">
                    Optional JSON configuration for service-specific settings.
                </p>
            </div>
        </div>

        <!-- Status -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-6">Status</h2>
            
            <div class="flex items-center">
                <input type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-slate-700">
                    Enable this integration immediately
                </label>
            </div>
            <p class="mt-2 text-sm text-slate-500">
                You can enable or disable this integration later from the integrations list.
            </p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('tenant.integrations.index') }}" 
               class="px-6 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-medium transition-colors duration-200">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                Create Integration
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Service-specific help text
    const serviceHelp = {
        'n8n': {
            description: 'N8N workflow automation platform for AI and OCR processing',
            webhook_placeholder: 'https://your-n8n-instance.com/webhook/your-workflow-id'
        },
        'quickbooks': {
            description: 'QuickBooks Online integration for accounting and invoicing',
            webhook_placeholder: 'https://your-quickbooks-app.com/webhook'
        },
        'custom': {
            description: 'Custom integration for your specific needs',
            webhook_placeholder: 'https://your-custom-service.com/webhook'
        }
    };

    document.getElementById('service_name').addEventListener('change', function() {
        const service = this.value;
        const descriptionField = document.getElementById('description');
        const webhookField = document.getElementById('webhook_url');
        
        if (service && serviceHelp[service]) {
            if (!descriptionField.value) {
                descriptionField.value = serviceHelp[service].description;
            }
            webhookField.placeholder = serviceHelp[service].webhook_placeholder;
        }
    });

    // JSON validation for settings field
    document.getElementById('settings').addEventListener('blur', function() {
        const value = this.value.trim();
        if (value && value !== '') {
            try {
                JSON.parse(value);
                this.classList.remove('border-red-300');
            } catch (e) {
                this.classList.add('border-red-300');
            }
        }
    });
</script>
@endpush
@endsection