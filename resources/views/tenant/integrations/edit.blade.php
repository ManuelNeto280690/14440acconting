@extends('layouts.tenant')

@section('title', 'Edit Integration')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('tenant.integrations.show', $integration) }}" 
               class="text-slate-600 hover:text-slate-900 mr-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Edit Integration</h1>
                <p class="text-slate-600 mt-2">Update your {{ ucfirst($integration->service_name) }} integration settings</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('tenant.integrations.update', $integration) }}" class="space-y-8">
        @csrf
        @method('PUT')

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
                           value="{{ old('name', $integration->name) }}"
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
                            <option value="{{ $value }}" {{ old('service_name', $integration->service_name) == $value ? 'selected' : '' }}>
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
                          placeholder="Brief description of this integration...">{{ old('description', $integration->description) }}</textarea>
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
                    <div class="relative">
                        <input type="password" 
                               id="api_key" 
                               name="api_key" 
                               value="{{ old('api_key') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('api_key') border-red-300 @enderror"
                               placeholder="Enter new API key or leave empty to keep current">
                        <button type="button" 
                                onclick="togglePasswordVisibility('api_key')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('api_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-slate-500">
                        Leave empty to keep the current API key. Current key is {{ $integration->api_key ? 'set' : 'not set' }}.
                    </p>
                </div>

                <div>
                    <label for="api_secret" class="block text-sm font-medium text-slate-700 mb-2">
                        API Secret
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="api_secret" 
                               name="api_secret" 
                               value="{{ old('api_secret') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('api_secret') border-red-300 @enderror"
                               placeholder="Enter new API secret or leave empty">
                        <button type="button" 
                                onclick="togglePasswordVisibility('api_secret')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('api_secret')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-slate-500">
                        Current secret is {{ $integration->api_secret ? 'set' : 'not set' }}.
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <label for="webhook_url" class="block text-sm font-medium text-slate-700 mb-2">
                    Webhook URL <span class="text-red-500">*</span>
                </label>
                <input type="url" 
                       id="webhook_url" 
                       name="webhook_url" 
                       value="{{ old('webhook_url', $integration->webhook_url) }}"
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
                <div class="relative">
                    <input type="password" 
                           id="webhook_secret" 
                           name="webhook_secret" 
                           value="{{ old('webhook_secret') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('webhook_secret') border-red-300 @enderror"
                           placeholder="Enter new webhook secret or leave empty">
                    <button type="button" 
                            onclick="togglePasswordVisibility('webhook_secret')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
                @error('webhook_secret')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-slate-500">
                    Used to verify webhook authenticity. Current secret is {{ $integration->webhook_secret ? 'set' : 'not set' }}.
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
                           value="{{ old('max_retries', $integration->max_retries) }}"
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
                           value="{{ old('rate_limit_per_minute', $integration->rate_limit_per_minute) }}"
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
                           value="{{ old('timeout_seconds', $integration->timeout_seconds) }}"
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
                          placeholder='{"custom_field": "value", "another_setting": true}'>{{ old('settings', $integration->settings ? json_encode($integration->settings, JSON_PRETTY_PRINT) : '') }}</textarea>
                @error('settings')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-slate-500">
                    Optional JSON configuration for service-specific settings.
                </p>
            </div>
        </div>

        <!-- Status and Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-6">Status & Actions</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $integration->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-slate-700">
                            Enable this integration
                        </label>
                    </div>
                    <span class="text-sm text-slate-500">
                        Currently: {{ $integration->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                @if($integration->hasErrors())
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-amber-800">Integration has errors</h3>
                                <p class="text-sm text-amber-700 mt-1">
                                    This integration has {{ $integration->retry_count }}/{{ $integration->max_retries }} failed attempts.
                                    @if($integration->canRetry())
                                        You can retry or reset the error count after saving.
                                    @else
                                        Maximum retries reached. Consider resetting the retry count.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center">
                    <input type="checkbox" 
                           id="reset_retry_count" 
                           name="reset_retry_count" 
                           value="1"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                    <label for="reset_retry_count" class="ml-2 block text-sm text-slate-700">
                        Reset retry count to 0
                    </label>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('tenant.integrations.show', $integration) }}" 
               class="px-6 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-medium transition-colors duration-200">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                Update Integration
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
        field.setAttribute('type', type);
    }

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
        const webhookField = document.getElementById('webhook_url');
        
        if (service && serviceHelp[service]) {
            webhookField.placeholder = serviceHelp[service].webhook_placeholder;
        }
    });
</script>
@endpush
@endsection