@extends('layouts.tenant')

@section('title', 'Documents')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Documents
            <p class="text-gray-600">Manage and process your documents</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('tenant.documents.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Document
            </a>
            <button type="button" 
                    onclick="openBulkUploadModal()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Upload in Bulk
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    @if($stats)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Documents</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Processed</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['processed']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['pending']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-times-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Failed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['failed'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                        <i class="fas fa-hdd text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Storage Used</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format(($stats['storage_used'] ?? 0) / 1024 / 1024, 1) }}MB</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('tenant.documents.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search by document name..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                <select id="type" 
                        name="type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All types</option>
                    <option value="invoice" {{ request('type') === 'invoice' ? 'selected' : '' }}>Invoice</option>
                    <option value="receipt" {{ request('type') === 'receipt' ? 'selected' : '' }}>Receipt</option>
                    <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>Contract</option>
                    <option value="report" {{ request('type') === 'report' ? 'selected' : '' }}>Report</option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" 
                       id="date_from" 
                       name="date_from" 
                       value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Documents</h3>
            
            @if($documents->count() > 0)
                <div class="flex items-center gap-3">
                    <select id="bulk-action" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">Bulk actions</option>
                        <option value="process">Process selected</option>
                        <option value="download">Download selected</option>
                        <option value="delete">Delete selected</option>
                    </select>
                    <button type="button" 
                            onclick="executeBulkAction()"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Execute
                    </button>
                </div>
            @endif
        </div>

        @if($documents->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" 
                                       id="select-all"
                                       onchange="toggleSelectAll()"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="flex items-center gap-1 hover:text-gray-700 group">
                                    Document
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 {{ request('sort') === 'name' && request('direction') === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'size', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="flex items-center gap-1 hover:text-gray-700 group">
                                    Size
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 {{ request('sort') === 'size' && request('direction') === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="flex items-center gap-1 hover:text-gray-700 group">
                                    Date
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 {{ (request('sort') === 'created_at' || !request('sort')) && request('direction') === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($documents as $document)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" 
                                           name="selected_documents[]" 
                                           value="{{ $document->id }}"
                                           class="document-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                            @php
                                                $extension = strtolower(pathinfo($document->name, PATHINFO_EXTENSION));
                                                $iconClass = match($extension) {
                                                    'pdf' => 'text-red-600',
                                                    'doc', 'docx' => 'text-blue-600',
                                                    'xls', 'xlsx' => 'text-green-600',
                                                    'jpg', 'jpeg', 'png', 'gif' => 'text-purple-600',
                                                    default => 'text-gray-600'
                                                };
                                            @endphp
                                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900 truncate">
                                                {{ Str::limit($document->name, 40) }}
                                            </div>
                                            @if($document->description)
                                                <div class="text-sm text-gray-500 truncate">
                                                    {{ Str::limit($document->description, 60) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeLabels = [
                                            'invoice' => 'Invoice',
                                            'receipt' => 'Receipt',
                                            'contract' => 'Contract',
                                            'report' => 'Report',
                                            'other' => 'Other'
                                        ];
                                        $typeColors = [
                                            'invoice' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'receipt' => 'bg-green-100 text-green-800 border-green-200',
                                            'contract' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'report' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'other' => 'bg-gray-100 text-gray-800 border-gray-200'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $typeColors[$document->type] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                        {{ $typeLabels[$document->type] ?? ucfirst($document->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($document->size / 1024, 1) }} KB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'processed' => 'bg-green-100 text-green-800 border-green-200',
                                            'failed' => 'bg-red-100 text-red-800 border-red-200',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pending',
                                            'processing' => 'Processing',
                                            'processed' => 'Processed',
                                            'failed' => 'Failed',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClasses[$document->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                        {{ $statusLabels[$document->status] ?? ucfirst($document->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $document->created_at->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $document->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('tenant.documents.show', $document) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition-colors duration-200 p-1 rounded hover:bg-blue-50"
                                           title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>

                                        <a href="{{ route('tenant.documents.download', $document) }}" 
                                           class="text-green-600 hover:text-green-900 transition-colors duration-200 p-1 rounded hover:bg-green-50"
                                           title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </a>

                                        @if($document->status === 'pending' || $document->status === 'failed')
                                            <button type="button" 
                                                    onclick="processDocument('{{ $document->id }}')"
                                                    class="text-purple-600 hover:text-purple-900 transition-colors duration-200 p-1 rounded hover:bg-purple-50"
                                                    title="Process">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </button>
                                        @endif

                                        <a href="{{ route('tenant.documents.edit', $document) }}" 
                                           class="text-yellow-600 hover:text-yellow-900 transition-colors duration-200 p-1 rounded hover:bg-yellow-50"
                                           title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        <button type="button" 
                                                onclick="deleteDocument('{{ $document->id }}')"
                                                class="text-red-600 hover:text-red-900 transition-colors duration-200 p-1 rounded hover:bg-red-50"
                                                title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($documents->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $documents->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-3">No Documents Found</h3>
                <p class="text-gray-600 mb-6">
                    @if(request()->hasAny(['search', 'type', 'status', 'date_from']))
                        No documents match your current filters. Try adjusting your search criteria or clear the filters to see all documents.
                    @else
                        You haven't uploaded any documents yet. Start by uploading your first document to get started with document management.
                    @endif
                </p>
                <div class="flex justify-center gap-3">
                    <a href="{{ route('tenant.documents.index') }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200">
                        Clear Filters
                    </a>
                    <a href="{{ route('tenant.documents.create') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200">
                        Upload Your First Document
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Bulk Upload Modal -->
<div id="bulkUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 xl:w-1/3 shadow-lg rounded-xl bg-white">
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Bulk Upload Documents</h3>
            <button type="button" 
                    onclick="closeBulkUploadModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="{{ route('tenant.documents.bulk-upload') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- File Upload Area -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Files
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors duration-200">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="bulk_files" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload files</span>
                                <input id="bulk_files" 
                                       name="files[]" 
                                       type="file" 
                                       multiple 
                                       class="sr-only"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                                       onchange="updateFileList(this)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF up to 10MB each
                        </p>
                    </div>
                </div>
                <div id="fileList" class="mt-3 hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">Selected files:</p>
                    <ul id="selectedFiles" class="text-sm text-gray-600 space-y-1"></ul>
                </div>
            </div>

            <!-- Document Type -->
            <div class="mb-6">
                <label for="bulk_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Document Type
                </label>
                <select id="bulk_type" 
                        name="type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="other">Other</option>
                    <option value="invoice">Invoice</option>
                    <option value="receipt">Receipt</option>
                    <option value="contract">Contract</option>
                    <option value="report">Report</option>
                </select>
            </div>

            <!-- Auto Process -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="auto_process" 
                           value="1"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Automatically process documents after upload</span>
                </label>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3">
                <button type="button" 
                        onclick="closeBulkUploadModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Upload Documents
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Bulk actions
function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.document-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function executeBulkAction() {
    const action = document.getElementById('bulk-action').value;
    const selectedDocs = Array.from(document.querySelectorAll('.document-checkbox:checked')).map(cb => cb.value);
    
    if (!action) {
        alert('Please select an action.');
        return;
    }
    
    if (selectedDocs.length === 0) {
        alert('Please select at least one document.');
        return;
    }
    
    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${selectedDocs.length} document(s)? This action cannot be undone.`)) {
            return;
        }
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/tenant/documents/bulk-${action}`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    selectedDocs.forEach(docId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'documents[]';
        input.value = docId;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Individual document actions
function deleteDocument(documentId) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        // Criar um formulário temporário com a rota correta
        const form = document.createElement('form');
        form.method = 'POST';
        
        // Usar a URL base atual e adicionar o path correto
        const currentUrl = window.location.href;
        const baseUrl = currentUrl.split('/documents')[0];
        form.action = `${baseUrl}/documents/${documentId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function processDocument(documentId) {
    if (confirm('Do you want to process this document?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/tenant/documents/${documentId}/process`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk upload modal
function openBulkUploadModal() {
    document.getElementById('bulkUploadModal').classList.remove('hidden');
}

function closeBulkUploadModal() {
    document.getElementById('bulkUploadModal').classList.add('hidden');
    document.getElementById('bulk_files').value = '';
    document.getElementById('fileList').classList.add('hidden');
    document.getElementById('selectedFiles').innerHTML = '';
}

function updateFileList(input) {
    const fileList = document.getElementById('fileList');
    const selectedFiles = document.getElementById('selectedFiles');
    
    if (input.files.length > 0) {
        fileList.classList.remove('hidden');
        selectedFiles.innerHTML = '';
        
        Array.from(input.files).forEach(file => {
            const li = document.createElement('li');
            li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            selectedFiles.appendChild(li);
        });
    } else {
        fileList.classList.add('hidden');
    }
}


// Bulk delete function
function bulkDelete() {
    const selectedDocuments = Array.from(document.querySelectorAll('input[name="selected_documents[]"]:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedDocuments.length === 0) {
        alert('Please select at least one document to delete.');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedDocuments.length} document(s)? This action cannot be undone.`)) {
        // Usar a URL base atual para construir a URL correta
        const currentUrl = window.location.href;
        const baseUrl = currentUrl.split('/documents')[0];
        const deleteUrl = `${baseUrl}/documents/bulk-delete`;
        
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                document_ids: selectedDocuments
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Recarregar a página para atualizar a lista
            } else {
                alert(data.message || 'Error deleting documents.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting documents. Please try again.');
        });
    }
}

// Close modal when clicking outside
document.getElementById('bulkUploadModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeBulkUploadModal();
    }
});
</script>
@endsection

