@extends('layouts.admin')

@section('title', 'Create New Plan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.plans.index') }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Create New Plan</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Plan Information</h3>
        </div>
        
        <form action="{{ route('admin.plans.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="md:col-span-2">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Plan Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Basic, Professional, Enterprise"
                           required>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                        Price <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               value="{{ old('price') }}"
                               step="0.01"
                               min="0"
                               class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0.00"
                               required>
                    </div>
                </div>

                <div>
                    <label for="billing_cycle" class="block text-sm font-medium text-gray-700 mb-2">
                        Billing Cycle <span class="text-red-500">*</span>
                    </label>
                    <select id="billing_cycle" 
                            name="billing_cycle" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Select billing cycle</option>
                        <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>

                <div>
                    <label for="stripe_price_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Stripe Price ID
                    </label>
                    <input type="text" 
                           id="stripe_price_id" 
                           name="stripe_price_id" 
                           value="{{ old('stripe_price_id') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="price_1234567890">
                    <p class="mt-1 text-sm text-gray-500">Optional: Link to Stripe price for payment processing</p>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Brief description of the plan and its benefits">{{ old('description') }}</textarea>
                </div>

                <!-- Limits and Features -->
                <div class="md:col-span-2 mt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Limits and Features</h4>
                </div>

                <div>
                    <label for="max_users" class="block text-sm font-medium text-gray-700 mb-2">
                        Maximum Users
                    </label>
                    <input type="number" 
                           id="max_users" 
                           name="max_users" 
                           value="{{ old('max_users') }}"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., 10">
                    <p class="mt-1 text-sm text-gray-500">Leave empty for unlimited users</p>
                </div>

                <div>
                    <label for="max_storage_gb" class="block text-sm font-medium text-gray-700 mb-2">
                        Storage Limit (GB)
                    </label>
                    <input type="number" 
                           id="max_storage_gb" 
                           name="max_storage_gb" 
                           value="{{ old('max_storage_gb') }}"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., 100">
                    <p class="mt-1 text-sm text-gray-500">Leave empty for unlimited storage</p>
                </div>

                <div>
                    <label for="max_documents" class="block text-sm font-medium text-gray-700 mb-2">
                        Document Limit
                    </label>
                    <input type="number" 
                           id="max_documents" 
                           name="max_documents" 
                           value="{{ old('max_documents') }}"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., 1000">
                    <p class="mt-1 text-sm text-gray-500">Leave empty for unlimited documents</p>
                </div>

                <!-- Plan Features -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Plan Features
                    </label>
                    <div id="features-container">
                        <div class="feature-item flex items-center space-x-2 mb-2">
                            <input type="text" 
                                   name="features[]" 
                                   value="{{ old('features.0') }}"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., Document OCR Processing">
                            <button type="button" 
                                    onclick="removeFeature(this)" 
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" 
                            onclick="addFeature()" 
                            class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-plus mr-1"></i>Add Feature
                    </button>
                </div>

                <!-- Settings -->
                <div class="md:col-span-2 mt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Settings</h4>
                </div>

                <div class="md:col-span-2">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active Plan
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="is_featured" 
                                   name="is_featured" 
                                   value="1"
                                   {{ old('is_featured') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                                Featured Plan
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.plans.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>Create Plan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addFeature() {
    const container = document.getElementById('features-container');
    const featureItem = document.createElement('div');
    featureItem.className = 'feature-item flex items-center space-x-2 mb-2';
    featureItem.innerHTML = `
        <input type="text" 
               name="features[]" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
               placeholder="Enter feature description">
        <button type="button" 
                onclick="removeFeature(this)" 
                class="text-red-600 hover:text-red-800">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(featureItem);
}

function removeFeature(button) {
    const container = document.getElementById('features-container');
    if (container.children.length > 1) {
        button.parentElement.remove();
    }
}

// Auto-generate price suggestions based on billing cycle
document.getElementById('billing_cycle').addEventListener('change', function() {
    const priceInput = document.getElementById('price');
    const currentPrice = parseFloat(priceInput.value) || 0;
    
    if (this.value === 'yearly' && currentPrice > 0) {
        // Suggest 10 months pricing for yearly (2 months free)
        const yearlyPrice = (currentPrice * 10).toFixed(2);
        if (confirm(`Would you like to set the yearly price to $${yearlyPrice} (equivalent to 10 months, giving 2 months free)?`)) {
            priceInput.value = yearlyPrice;
        }
    }
});
</script>
@endsection