@extends('layouts.admin')

@section('title', 'Edit Tenant - ' . $tenant->name)

@section('content')
<!-- Header with Gradient Background -->
<div class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.tenants.show', $tenant) }}" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Details
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Tenant: {{ $tenant->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Update tenant information and subscription settings</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @if($tenant->status === 'active')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        Active
                    </span>
                @elseif($tenant->status === 'inactive')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-1"></i>
                        Inactive
                    </span>
                @elseif($tenant->status === 'suspended')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-pause-circle mr-1"></i>
                        Suspended
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-clock mr-1"></i>
                        {{ ucfirst($tenant->status) }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Success/Error Messages -->
    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

    <!-- Main Form -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.tenants.update', $tenant) }}" method="POST" class="divide-y divide-gray-200" id="tenantEditForm">
            @csrf
            @method('PUT')

            <!-- Basic Information Section -->
            <div class="p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-building text-blue-600 mr-3"></i>
                        Basic Information
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">Essential details about the tenant</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Tenant Name -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Tenant Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $tenant->name) }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               required>
                    </div>

                    <!-- Domain -->
                    <div class="space-y-2">
                        <label for="domain" class="block text-sm font-medium text-gray-700">
                            Domain <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="domain" 
                               name="domain" 
                               value="{{ old('domain', $tenant->domains->first()?->domain ?? $tenant->domain) }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               required>
                        <p class="text-xs text-gray-500">Domain can contain lowercase letters, numbers, dots, hyphens, and colons</p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Contact Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $tenant->email) }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               required>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            Phone Number
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $tenant->phone) }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                    </div>
                </div>
            </div>

            <!-- Company Information Section -->
            <div class="p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-briefcase text-green-600 mr-3"></i>
                        Company Information
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">Business details and legal information</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Company Name -->
                    <div class="space-y-2">
                        <label for="company_name" class="block text-sm font-medium text-gray-700">
                            Company Name
                        </label>
                        <input type="text" 
                               id="company_name" 
                               name="company_name" 
                               value="{{ old('company_name', $tenant->company_name) }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                    </div>

                    <!-- Tax ID -->
                    <div class="space-y-2">
                        <label for="tax_id" class="block text-sm font-medium text-gray-700">
                            Tax ID / CNPJ
                        </label>
                        <input type="text" 
                               id="tax_id" 
                               name="tax_id" 
                               value="{{ old('tax_id', $tenant->tax_id) }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                    </div>

                    <!-- Website -->
                    <div class="space-y-2">
                        <label for="website" class="block text-sm font-medium text-gray-700">
                            Website
                        </label>
                        <input type="url" 
                               id="website" 
                               name="website" 
                               value="{{ old('website', $tenant->website) }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="https://example.com">
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Status
                        </label>
                        <select id="status" 
                                name="status" 
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <option value="active" {{ old('status', $tenant->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $tenant->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $tenant->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="pending" {{ old('status', $tenant->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>

                    <!-- Address (full width) -->
                    <div class="lg:col-span-2 space-y-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">
                            Address
                        </label>
                        <textarea id="address" 
                                  name="address" 
                                  rows="3"
                                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                  placeholder="Complete business address...">{{ old('address', $tenant->address) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Subscription Plan Section -->
            <div class="p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-credit-card text-purple-600 mr-3"></i>
                        Subscription Plan
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">Manage subscription plan and billing</p>
                </div>

                <div class="space-y-6">
                    <!-- Current Subscription Info -->
                    @php
                        $activeSubscription = $tenant->subscriptions->where('status', 'active')->first();
                        $currentPlan = $activeSubscription?->plan;
                    @endphp

                    @if($activeSubscription)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">Current Subscription</p>
                                    <p class="text-sm text-blue-700">
                                        {{ $currentPlan->name ?? 'Unknown Plan' }} - 
                                        {{ $currentPlan ? $currentPlan->formatted_price : 'N/A' }} - 
                                        Status: {{ ucfirst($activeSubscription->status) }}
                                    </p>
                                    @if($activeSubscription->trial_ends_at)
                                        <p class="text-xs text-blue-600">Trial ends: {{ $activeSubscription->trial_ends_at->format('M d, Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Plan Selection -->
                    <div class="space-y-2">
                        <label for="plan_id" class="block text-sm font-medium text-gray-700">
                            Change Plan
                        </label>
                        <select name="plan_id" id="plan_id" class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <option value="">Keep current plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" 
                                    {{ old('plan_id') == $plan->id ? 'selected' : '' }}
                                    data-price="{{ $plan->formatted_price }}">
                                    {{ $plan->name }} - {{ $plan->formatted_price }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500">Select a new plan to change the subscription. Leave empty to keep current plan.</p>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-sticky-note text-yellow-600 mr-3"></i>
                        Additional Notes
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">Internal notes and observations</p>
                </div>

                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Notes
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="4"
                              class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                              placeholder="Any additional notes about this tenant...">{{ old('notes', $tenant->settings['notes'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-8 py-6 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.tenants.show', $tenant) }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                            id="submitBtn">
                        <i class="fas fa-save mr-2"></i>
                        <span id="submitText">Update Tenant</span>
                    </button>
                </div>

                <!-- Additional Actions -->
                <!--div class="flex items-center space-x-2">
                    @if($tenant->status === 'active')
                        <form action="{{ route('admin.tenants.deactivate', $tenant) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                                    onclick="return confirm('Are you sure you want to deactivate this tenant?')">
                                <i class="fas fa-pause mr-2"></i>
                                Deactivate
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.tenants.activate', $tenant) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                <i class="fas fa-play mr-2"></i>
                                Activate
                            </button>
                        </form>
                    @endif
                </div-->
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tenantEditForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const domainInput = document.getElementById('domain');
    const planSelect = document.getElementById('plan_id');

    // Domain validation - permitir pontos, hífens e dois pontos (igual ao create)
    domainInput.addEventListener('input', function() {
        const domain = this.value;
        const isValid = /^[a-z0-9.-:]+$/.test(domain);
        
        if (!isValid && domain.length > 0) {
            this.setCustomValidity('Domain can only contain lowercase letters, numbers, dots, hyphens, and colons');
            this.classList.add('border-red-500');
            this.classList.remove('border-gray-300');
        } else {
            this.setCustomValidity('');
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-300');
        }
    });

    // Plan change warning
    planSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.dataset.price;
            
            if (confirm(`Are you sure you want to change to ${selectedOption.text}? This will create a new subscription and cancel the current one.`)) {
                // User confirmed
            } else {
                this.value = '';
            }
        }
    });

    // Form submission handling
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitText.textContent = 'Updating...';
        
        // Re-enable after 5 seconds to prevent permanent disable
        setTimeout(() => {
            submitBtn.disabled = false;
            submitText.textContent = 'Update Tenant';
        }, 5000);
    });

    // Detect changes and show warning
    let originalFormData = new FormData(form);
    let hasChanges = false;

    form.addEventListener('input', function() {
        hasChanges = true;
    });

    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    // Remove warning when form is submitted
    form.addEventListener('submit', function() {
        hasChanges = false;
    });
});
</script>
@endsection