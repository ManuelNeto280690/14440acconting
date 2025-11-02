@extends('layouts.admin')

@section('title', 'Create Role & Permissions')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-user-shield mr-2"></i>
                    Create New Role
                </h2>
                <a href="{{ route('admin.roles.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to Roles
                </a>
            </div>
        </div>

        <form action="{{ route('admin.roles.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Role Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Role Information</h3>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="e.g., manager, editor"
                               required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Display Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="display_name" 
                               name="display_name" 
                               value="{{ old('display_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('display_name') border-red-500 @enderror"
                               placeholder="e.g., Manager, Content Editor"
                               required>
                        @error('display_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                                  placeholder="Describe the role's responsibilities...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Active Role
                        </label>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Permissions</h3>
                    
                    @if($permissions->count() > 0)
                        <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-md p-4">
                            @foreach($permissions as $category => $categoryPermissions)
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-800 mb-2 capitalize">
                                        <i class="fas fa-folder mr-1"></i>
                                        {{ ucfirst($category) }}
                                    </h4>
                                    <div class="grid grid-cols-1 gap-2 ml-4">
                                        @foreach($categoryPermissions as $permission)
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}"
                                                       {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <span class="ml-2 text-sm text-gray-700">
                                                    {{ $permission->display_name }}
                                                    @if($permission->description)
                                                        <span class="text-gray-500 text-xs block ml-6">
                                                            {{ $permission->description }}
                                                        </span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                            <p>No permissions available.</p>
                            <a href="{{ route('admin.permissions.create') }}" 
                               class="text-blue-600 hover:text-blue-800 underline">
                                Create permissions first
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.roles.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition duration-200">
                    <i class="fas fa-save mr-1"></i>
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection