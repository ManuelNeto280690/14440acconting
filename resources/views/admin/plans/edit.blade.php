@extends('layouts.admin')

@section('title', 'Edit Plan - ' . $plan->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.plans.show', $plan) }}" 
               class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Edit Plan</h1>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.plans.show', $plan) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-eye mr-2"></i>View Plan
            </a>
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

    <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <!-- Basic Information -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Plan Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $plan->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                    Price (USD) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" 
                                           id="price" 
                                           name="price" 
                                           value="{{ old('price', $plan->price) }}"
                                           step="0.01"
                                           min="0"
                                           class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
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
                                    <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                            </div>

                            <div>
                                <label for="stripe_price_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stripe Price ID
                                </label>
                                <input type="text" 
                                       id="stripe_price_id" 
                                       name="stripe_price_id" 
                                       value="{{ old('stripe_price_id', $plan->stripe_price_id) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="price_1234567890">
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description
                                </label>
                                <textarea id="description" 
                                          name="description" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Brief description of the plan...">{{ old('description', $plan->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plan Limits -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Plan Limits</h3>
                        <p class="text-sm text-gray-600 mt-1">Leave blank for unlimited</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="max_users" class="block text-sm font-medium text-gray-700 mb-2">
                                    Maximum Users
                                </label>
                                <input type="number" 
                                       id="max_users" 
                                       name="max_users" 
                                       value="{{ old('max_users', $plan->max_users) }}"
                                       min="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Unlimited">
                            </div>

                            <div>
                                <label for="max_storage_gb" class="block text-sm font-medium text-gray-700 mb-2">
                                    Storage Limit (GB)
                                </label>
                                <input type="number" 
                                       id="max_storage_gb" 
                                       name="max_storage_gb" 
                                       value="{{ old('max_storage_gb', $plan->max_storage_gb) }}"
                                       min="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Unlimited">
                            </div>

                            <div>
                                <label for="max_documents" class="block text-sm font-medium text-gray-700 mb-2">
                                    Document Limit
                                </label>
                                <input type="number" 
                                       id="max_documents" 
                                       name="max_documents" 
                                       value="{{ old('max_documents', $plan->max_documents) }}"
                                       min="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Unlimited">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plan Features -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Plan Features</h3>
                        <p class="text-sm text-gray-600 mt-1">Add features that are included in this plan</p>
                    </div>
                    <div class="p-6">
                        <div id="features-container">
                            @php
                                $features = old('features', $plan->features ? json_decode($plan->features, true) : []);
                            @endphp
                            @if(is_array($features) && count($features) > 0)
                                @foreach($features as $index => $feature)
                                    <div class="feature-item flex items-center mb-3">
                                        <input type="text" 
                                               name="features[]" 
                                               value="{{ $feature }}"
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Enter a feature...">
                                        <button type="button" 
                                                class="ml-3 px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 remove-feature">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <div class="feature-item flex items-center mb-3">
                                    <input type="text" 
                                           name="features[]" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter a feature...">
                                    <button type="button" 
                                            class="ml-3 px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 remove-feature">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <button type="button" 
                                id="add-feature" 
                                class="mt-3 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <i class="fas fa-plus mr-2"></i>Add Feature
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Plan Settings -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Plan Settings</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label for="is_active" class="text-sm font-medium text-gray-700">Active Plan</label>
                                <p class="text-xs text-gray-500">Allow new subscriptions</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label for="is_featured" class="text-sm font-medium text-gray-700">Featured Plan</label>
                                <p class="text-xs text-gray-500">Highlight this plan</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="is_featured" 
                                       name="is_featured" 
                                       value="1"
                                       {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Current Statistics -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Current Statistics</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Subscriptions</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $plan->subscriptions->count() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Active Subscriptions</span>
                                <span class="text-sm font-semibold text-green-600">{{ $plan->subscriptions->where('status', 'active')->count() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Trial Subscriptions</span>
                                <span class="text-sm font-semibold text-yellow-600">{{ $plan->subscriptions->where('status', 'trial')->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="p-6">
                        <div class="space-y-3">
                            <button type="submit" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i>Update Plan
                            </button>

                            <a href="{{ route('admin.plans.show', $plan) }}" 
                               class="w-full block text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>

                            @if($plan->subscriptions->count() === 0)
                                <button type="button" 
                                        onclick="if(confirm('Are you sure you want to delete this plan? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                    <i class="fas fa-trash mr-2"></i>Delete Plan
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($plan->subscriptions->count() === 0)
        <form id="delete-form" action="{{ route('admin.plans.destroy', $plan) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>

<script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Add feature functionality
                    document.getElementById('add-feature').addEventListener('click', function() {
                        const container = document.getElementById('features-container');
                        const newFeature = document.createElement('div');
                        newFeature.className = 'feature-item flex items-center mb-3';
                        newFeature.innerHTML = `
                            <input type="text" 
                                   name="features[]" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter a feature...">
                            <button type="button" 
                                    class="ml-3 px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 remove-feature">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        container.appendChild(newFeature);
                    });
            
                    // Remove feature functionality
                    document.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-feature')) {
                            const featureItem = e.target.closest('.feature-item');
                            const container = document.getElementById('features-container');
                            
                            // Don't remove if it's the last feature
                            if (container.children.length > 1) {
                                featureItem.remove();
                            } else {
                                // Clear the input instead
                                featureItem.querySelector('input').value = '';
                            }
                        }
                    });
            
                    // Billing cycle change suggestion
                    document.getElementById('billing_cycle').addEventListener('change', function() {
                        const priceInput = document.getElementById('price');
                        const currentPrice = parseFloat(priceInput.value) || 0;
                        
                        if (this.value === 'yearly' && currentPrice > 0) {
                            const suggestedPrice = (currentPrice * 12 * 0.8).toFixed(2); // 20% discount for yearly
                            if (confirm(`Would you like to set the yearly price to $${suggestedPrice} (20% discount from monthly)?`)) {
                                priceInput.value = suggestedPrice;
                            }
                        }
                    });
                });
            </script>
                // Initialize features
                const features = @json(old('features', is_array($plan->features) ? $plan->features : json_decode($plan->features, true) ?? []));
                features.forEach(feature => addFeature(feature));
                });
            </script>
@endsection