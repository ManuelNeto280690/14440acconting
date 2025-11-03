@extends('layouts.admin')

@section('title', 'Create New Tenant')

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
                    <h1 class="text-3xl font-bold text-gray-900">Create New Tenant</h1>
                    <p class="mt-1 text-sm text-gray-600">Add a new tenant to the system with their subscription plan</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Error Messages -->
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Form -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.tenants.store') }}" method="POST" class="divide-y divide-gray-200">
            @csrf

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
                               value="{{ old('name') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="Enter tenant name"
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
                               value="{{ old('domain') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="tenant-domain"
                               required>
                        <p class="text-xs text-gray-500">Domain used to access the tenant (e.g., client1.localhost)</p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Contact Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="contact@example.com"
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
                               value="{{ old('phone') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="+1 (555) 123-4567">
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
                    <p class="mt-1 text-sm text-gray-600">Additional company details</p>
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
                               value="{{ old('company_name') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="Company Inc.">
                    </div>

                    <!-- Tax ID -->
                    <div class="space-y-2">
                        <label for="tax_id" class="block text-sm font-medium text-gray-700">
                            Tax ID / CNPJ
                        </label>
                        <input type="text" 
                               id="tax_id" 
                               name="tax_id" 
                               value="{{ old('tax_id') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="XX.XXX.XXX/XXXX-XX">
                    </div>

                    <!-- Website -->
                    <div class="space-y-2">
                        <label for="website" class="block text-sm font-medium text-gray-700">
                            Website
                        </label>
                        <input type="url" 
                               id="website" 
                               name="website" 
                               value="{{ old('website') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="https://example.com">
                    </div>

                    <!-- Address -->
                    <div class="space-y-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">
                            Address
                        </label>
                        <textarea id="address" 
                                  name="address" 
                                  rows="3"
                                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                  placeholder="Full address">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Responsible User Section -->
            <div class="p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user-tie text-green-600 mr-3"></i>
                        Responsible User
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">Create the main user who will be responsible for this tenant</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- User Name -->
                    <div class="space-y-2">
                        <label for="user_name" class="block text-sm font-medium text-gray-700">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="user_name" 
                               name="user_name" 
                               value="{{ old('user_name') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="Enter full name"
                               required>
                        @error('user_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- User Email -->
                    <div class="space-y-2">
                        <label for="user_email" class="block text-sm font-medium text-gray-700">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="user_email" 
                               name="user_email" 
                               value="{{ old('user_email') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="user@example.com"
                               required>
                        @error('user_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500">This will be the login email for the responsible user</p>
                    </div>

                    <!-- User Password -->
                    <!--div class="space-y-2">
                        <label for="user_password" class="block text-sm font-medium text-gray-700">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="user_password" 
                               name="user_password" 
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="Enter password"
                               required
                               minlength="8">
                        @error('user_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500">Minimum 8 characters</p>
                    </div-->

                    <!-- Confirm Password -->
                    <!--div class="space-y-2">
                        <label for="user_password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="user_password_confirmation" 
                               name="user_password_confirmation" 
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="Confirm password"
                               required
                               minlength="8">
                        @error('user_password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div-->

                    <!-- User Phone -->
                    <div class="space-y-2">
                        <label for="user_phone" class="block text-sm font-medium text-gray-700">
                            Phone Number
                        </label>
                        <input type="tel" 
                               id="user_phone" 
                               name="user_phone" 
                               value="{{ old('user_phone') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="+1 (555) 123-4567">
                        @error('user_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- User Role Display -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            User Role
                        </label>
                        <div class="block w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                            <i class="fas fa-crown text-yellow-500 mr-2"></i>
                            Tenant Owner (Full Access)
                        </div>
                        <p class="text-xs text-gray-500">This user will have full administrative access to the tenant</p>
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
                    <p class="mt-1 text-sm text-gray-600">Select the subscription plan for this tenant</p>
                </div>

                <div class="space-y-6">
                    <!-- Plan Selection -->
                    <div class="space-y-2">
                        <label for="plan_id" class="block text-sm font-medium text-gray-700">
                            Select Plan <span class="text-red-500">*</span>
                        </label>
                        <select id="plan_id" 
                                name="plan_id" 
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                required>
                            <option value="">Choose a plan...</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" >
                                    {{ $plan->name }} - ${{ number_format($plan->price, 2) }}/{{ $plan->billing_cycle }} 
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Active Status -->
                        <div class="flex items-start space-x-3">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            </div>
                            <div class="text-sm">
                                <label for="is_active" class="font-medium text-gray-700">
                                    Activate Tenant
                                </label>
                                <p class="text-gray-500">Tenant will be active immediately after creation</p>
                            </div>
                        </div>

                        <!-- Trial Enabled -->
                        <div class="flex items-start space-x-3">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       id="trial_enabled" 
                                       name="trial_enabled" 
                                       value="1"
                                       {{ old('trial_enabled') ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            </div>
                            <div class="text-sm">
                                <label for="trial_enabled" class="font-medium text-gray-700">
                                    Enable Trial Period
                                </label>
                                <p class="text-gray-500">Start with a trial period before billing</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Notes Section -->
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-sticky-note text-yellow-600 mr-3"></i>
                        Additional Notes
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">Any additional information about this tenant</p>
                </div>

                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Notes
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="4"
                              class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                              placeholder="Any additional notes about this tenant...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-8 py-6 bg-gray-50 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    All required fields must be completed
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.tenants.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Create Tenant
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate domain from name
    const nameInput = document.getElementById('name');
    const domainInput = document.getElementById('domain');
    const planSelect = document.getElementById('plan_id');
    const form = document.querySelector('form');
    
    // Debug: Log all available plan options
    console.log('Available plans:');
    Array.from(planSelect.options).forEach(option => {
        if (option.value) {
            console.log(`Plan: ${option.text}, UUID: ${option.value}`);
        }
    });
    
    nameInput.addEventListener('input', function() {
        const name = this.value;
        const domain = name.toLowerCase()
            .replace(/[^a-z0-9]/g, '')
            .substring(0, 20) + '.website-1440accounting.tarfvp.easypanel.host';
        domainInput.value = domain;
    });

    // Domain validation - permitir pontos, hífens e dois pontos
    domainInput.addEventListener('input', function() {
        this.value = this.value
            .toLowerCase()
            .replace(/[^a-z0-9.:-]/g, '')
            .replace(/\.+/g, '.')
            .replace(/^\.|-$/g, '');
    });

    // Debug plan_id value
    planSelect.addEventListener('change', function() {
        console.log('Plan selected:', this.value);
        console.log('Plan selected type:', typeof this.value);
    });

    // UUID validation function
    function isValidUUID(str) {
        const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
        return uuidRegex.test(str);
    }

    // Intercept form submission to validate plan_id UUID
    form.addEventListener('submit', function(e) {
        const planValue = planSelect.value;
        console.log('Form submission - plan_id value:', planValue);
        console.log('Form submission - plan_id type:', typeof planValue);
        console.log('Form submission - plan_id length:', planValue.length);
        
        // Check if plan_id is "0" and convert to empty string
        if (planValue === '0') {
            planSelect.value = '';
            console.log('Converted plan_id from "0" to empty string');
        }
        
        // Validate that a valid UUID is selected
        if (!planSelect.value || planSelect.value === '' || planSelect.value === '0') {
            e.preventDefault();
            alert('Please select a valid plan.');
            planSelect.focus();
            return false;
        }
        
        // Validate UUID format
        if (!isValidUUID(planSelect.value)) {
            e.preventDefault();
            alert('The selected plan ID must be a valid UUID format.');
            planSelect.focus();
            return false;
        }
    });
});
</script>
@endsection