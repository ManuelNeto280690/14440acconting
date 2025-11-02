@extends('layouts.admin')

@section('title', 'Role Details - ' . $role->display_name)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Role Details</h1>
            <p class="mt-2 text-gray-600">View role information, permissions and assigned users</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.roles.edit', $role) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-200">
                <i class="fas fa-edit mr-2"></i>
                Edit Role
            </a>
            <a href="{{ route('admin.roles.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-semibold rounded-lg shadow-md hover:bg-gray-700 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Roles
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Role Information -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user-tag text-blue-600 mr-3"></i>
                        Role Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-6 mb-6">
                        <div class="flex-shrink-0 h-20 w-20">
                            <div class="h-20 w-20 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center">
                                <i class="fas fa-user-tag text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-2xl font-bold text-gray-900">{{ $role->display_name }}</h4>
                            <p class="text-gray-600 text-lg font-mono">{{ $role->name }}</p>
                            <div class="flex items-center space-x-4 mt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $role->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas fa-circle text-xs mr-2"></i>
                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-users text-xs mr-2"></i>
                                    {{ $role->users ? $role->users->count() : 0 }} {{ Str::plural('user', $role->users ? $role->users->count() : 0) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Role ID</label>
                            <p class="text-lg text-gray-900 font-mono">{{ $role->id }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Total Permissions</label>
                            <p class="text-lg text-gray-900 font-semibold">{{ $role->permissions ? $role->permissions->count() : 0 }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Created</label>
                            <p class="text-lg text-gray-900">{{ $role->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                            <p class="text-lg text-gray-900">{{ $role->updated_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>

                    @if($role->description)
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                            <p class="text-gray-900">{{ $role->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Permissions Details -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-key text-purple-600 mr-3"></i>
                        Permissions ({{ $role->permissions ? $role->permissions->count() : 0 }})
                    </h3>
                </div>
                <div class="p-6">
                    @if($role->permissions && $role->permissions->count() > 0)
                        @php
                            $groupedPermissions = $role->permissions->groupBy('category');
                        @endphp
                        
                        @foreach($groupedPermissions as $category => $permissions)
                            <div class="mb-6 last:mb-0">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3 capitalize flex items-center">
                                    <i class="fas fa-folder text-gray-600 mr-2"></i>
                                    {{ str_replace('_', ' ', $category) }}
                                    <span class="ml-2 text-sm font-normal text-gray-500">({{ $permissions ? $permissions->count() : 0 }})</span>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($permissions as $permission)
                                        <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200">
                                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                            <div>
                                                <span class="text-green-800 font-medium">{{ $permission->display_name }}</span>
                                                @if($permission->description)
                                                    <p class="text-xs text-green-600 mt-1">{{ $permission->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-key text-4xl mb-4"></i>
                            <p class="text-lg">No permissions assigned to this role.</p>
                            <a href="{{ route('admin.roles.edit', $role) }}" 
                               class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>
                                Add Permissions
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-users text-green-600 mr-3"></i>
                        Assigned Users ({{ $role->users ? $role->users->count() : 0 }})
                    </h3>
                </div>
                <div class="p-6">
                    @if($role->users && $role->users->count() > 0)
                        <div class="space-y-3">
                            @foreach($role->users as $user)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center">
                                                <span class="text-white text-sm font-bold">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h5>
                                            <p class="text-gray-600">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-users text-4xl mb-4"></i>
                            <p class="text-lg">No users assigned to this role.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-8">
            <!-- Quick Stats -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar text-indigo-600 mr-3"></i>
                        Quick Stats
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Total Users</span>
                            <span class="text-2xl font-bold text-gray-900">{{ $role->users ? $role->users->count() : 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Active Users</span>
                            <span class="text-2xl font-bold text-green-600">
                                {{ $role->users ? $role->users->where('is_active', true)->count() : 0 }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Total Permissions</span>
                            <span class="text-2xl font-bold text-purple-600">{{ $role->permissions ? $role->permissions->count() : 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center border-t pt-4">
                            <span class="text-sm font-medium text-gray-600">Role Status</span>
                            <span class="text-lg font-bold {{ $role->is_active ? 'text-green-600' : 'text-red-600' }}">
                                {{ $role->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-slate-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt text-gray-600 mr-3"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('admin.roles.edit', $role) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-edit mr-2"></i>Edit Role
                        </a>
                        
                        @if($role->is_active)
                            <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_active" value="0">
                                <button type="submit" 
                                        class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50"
                                        onclick="return confirm('Are you sure you want to deactivate this role?')">
                                    <i class="fas fa-ban mr-2"></i>Deactivate Role
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_active" value="1">
                                <button type="submit" 
                                        class="w-full flex items-center justify-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-white hover:bg-green-50">
                                    <i class="fas fa-check mr-2"></i>Activate Role
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.roles.create') }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-white hover:bg-blue-50">
                            <i class="fas fa-plus mr-2"></i>Create New Role
                        </a>

                        <a href="{{ route('admin.roles.index') }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-list mr-2"></i>View All Roles
                        </a>
                    </div>
                </div>
            </div>

            <!-- Permission Categories -->
            @if($role->permissions && $role->permissions->count() > 0)
                <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-tags text-yellow-600 mr-3"></i>
                            Permission Categories
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @php
                                $categoryCounts = $role->permissions ? $role->permissions->groupBy('category')->map->count() : collect();
                            @endphp
                            @foreach($categoryCounts as $category => $count)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $category) }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $count }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection