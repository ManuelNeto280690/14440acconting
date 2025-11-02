@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Role</h1>
            <p class="mt-2 text-gray-600">Modify role details and permissions</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-semibold rounded-lg shadow-md hover:bg-gray-700 transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Roles
        </a>
    </div>

    <!-- Role Info Card -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Role Information</h3>
        </div>
        <div class="px-6 py-4">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 h-16 w-16">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-user-tag text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-semibold text-gray-900">{{ $role->display_name }}</h4>
                    <p class="text-gray-600">{{ $role->name }}</p>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-2
                        {{ $role->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Edit Role Details</h3>
        </div>
        
        <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Role Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Role Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $role->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., admin, manager"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Display Name -->
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Display Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="display_name" 
                           name="display_name" 
                           value="{{ old('display_name', $role->display_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Administrator, Manager"
                           required>
                    @error('display_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Describe the role and its responsibilities">{{ old('description', $role->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status
                </label>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="radio" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $role->is_active) ? 'checked' : '' }} 
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" 
                               name="is_active" 
                               value="0" 
                               {{ !old('is_active', $role->is_active) ? 'checked' : '' }} 
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Inactive</span>
                    </label>
                </div>
            </div>

            <!-- Permissions -->
            <div class="mt-8">
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    <i class="fas fa-key mr-1"></i>
                    Permissions
                </label>
                
                @if($permissions->count() > 0)
                    @foreach($permissions as $category => $categoryPermissions)
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 capitalize">
                                {{ str_replace('_', ' ', $category) }}
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($categoryPermissions as $permission)
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                               class="text-blue-600 focus:ring-blue-500 rounded">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">{{ $permission->display_name }}</span>
                                            @if($permission->description)
                                                <p class="text-xs text-gray-500 mt-1">{{ $permission->description }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                        <p>No permissions available. Please create permissions first.</p>
                    </div>
                @endif
                
                @error('permissions')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Role Stats -->
            <div class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-2">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Role Statistics
                </h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Users with this role:</span>
                        <span class="font-semibold text-gray-900">{{ $role->users_count ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Current permissions:</span>
                        <span class="font-semibold text-gray-900">{{ $role->permissions_count ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('admin.roles.index') }}" 
                   class="px-6 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Update Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate role name from display name
    const displayNameInput = document.getElementById('display_name');
    const nameInput = document.getElementById('name');
    
    displayNameInput.addEventListener('input', function() {
        if (!nameInput.dataset.userModified) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_')
                .trim();
            nameInput.value = slug;
        }
    });
    
    nameInput.addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });
    
    // Select all/none functionality for permissions
    function addSelectAllButtons() {
        const categories = document.querySelectorAll('.mb-6');
        categories.forEach(category => {
            const checkboxes = category.querySelectorAll('input[type="checkbox"]');
            if (checkboxes.length > 1) {
                const header = category.querySelector('h4');
                const selectAllBtn = document.createElement('button');
                selectAllBtn.type = 'button';
                selectAllBtn.className = 'ml-2 text-xs text-blue-600 hover:text-blue-800';
                selectAllBtn.textContent = 'Select All';
                
                selectAllBtn.addEventListener('click', function() {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                    this.textContent = allChecked ? 'Select All' : 'Select None';
                });
                
                header.appendChild(selectAllBtn);
            }
        });
    }
    
    addSelectAllButtons();
});
</script>
@endsection