@extends('layouts.tenant')

@section('title', 'New Document')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">New Document</h1>
            <p class="text-gray-600">Upload a new document for processing</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('tenant.documents.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upload Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" 
              action="{{ route('tenant.documents.store') }}" 
              enctype="multipart/form-data" 
              id="documentForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column - File Upload -->
                <div class="space-y-6">
                    <!-- File Upload Area -->
                    <div>
                        <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                            Files <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors duration-200"
                             id="dropZone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Click to upload</span>
                                        <input id="files" 
                                               name="files[]" 
                                               type="file" 
                                               class="sr-only" 
                                               required
                                               multiple
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                                               onchange="handleFileSelect(this)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF up to 10MB each (max 10 files)
                                </p>
            </div>
        </div>
        
        <!-- Files Preview -->
        <div id="filesPreview" class="mt-4 hidden">
            <div class="space-y-2" id="filesList">
                <!-- Files will be displayed here -->
            </div>
            <div class="mt-3 flex justify-between items-center text-sm text-gray-600">
                <span id="filesCount">0 files selected</span>
                <button type="button" 
                        onclick="removeAllFiles()"
                        class="text-red-600 hover:text-red-800 font-medium">
                    Remove all
                </button>
            </div>
        </div>
    </div>

                    <!-- Document Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Document Name
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               placeholder="Name will be auto-filled based on the file"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">
                            Leave blank to use the file name
                        </p>
                    </div>

                    <!-- Document Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Document Type <span class="text-red-500">*</span>
                        </label>
                        <select id="type" 
                                name="type" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select type</option>
                            <option value="invoice" {{ old('type') === 'invoice' ? 'selected' : '' }}>Invoice</option>
                            <option value="receipt" {{ old('type') === 'receipt' ? 'selected' : '' }}>Receipt</option>
                            <option value="contract" {{ old('type') === 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="report" {{ old('type') === 'report' ? 'selected' : '' }}>Report</option>
                            <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <!-- Right Column - Additional Information -->
                <div class="space-y-6">
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Optional document description..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    </div>

                    <!-- Client Association -->
                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Associated Client
                        </label>
                        <select id="client_id" 
                                name="client_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">No client</option>
                            @if(isset($clients))
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <p class="text-sm text-gray-500 mt-1">
                            Optional: associate this document with a specific client
                        </p>
                    </div>

                    <!-- Tags -->
                    <!--div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                            Tags
                        </label>
                        <input type="text" 
                               id="tags" 
                               name="tags" 
                               value="{{ old('tags') }}"
                               placeholder="accounting, 2024..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">
                            Separate tags with commas to make searching easier
                        </p>
                    </div-->

                    {{-- Processing Options - Hidden as requested --}}
                    {{-- 
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Processing Options</h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="auto_process" 
                                       value="1"
                                       {{ old('auto_process') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">
                                    Process automatically after upload
                                </span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="extract_text" 
                                       value="1"
                                       {{ old('extract_text') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">
                                    Extract text via OCR
                                </span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="ai_analysis" 
                                       value="1"
                                       {{ old('ai_analysis') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">
                                    AI analysis
                                </span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="quickbooks_sync" 
                                       value="1"
                                       {{ old('quickbooks_sync') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">
                                    Sync with QuickBooks
                                </span>
                            </label>
                        </div>

                        <p class="text-xs text-gray-500 mt-2">
                            Processing options depend on your configured integrations
                        </p>
                    </div>
                    --}}

                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Priority
                        </label>
                        <select id="priority" 
                                name="priority" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('tenant.documents.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md font-medium transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        id="submitBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submitText">Upload Document</span>
                    <svg id="submitSpinner" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white hidden inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Upload Progress -->
    <div id="uploadProgress" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Uploading...</h3>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                    <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="progressText" class="text-sm text-gray-600">Preparing upload...</p>
            </div>
        </div>
    </div>
</div>

<script>
let selectedFiles = [];

// Drag and drop functionality
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('files');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

dropZone.addEventListener('drop', handleDrop, false);

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(e) {
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
}

function unhighlight(e) {
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 10) {
        alert('You can upload a maximum of 10 files at once.');
        return;
    }
    
    if (files.length > 0) {
        fileInput.files = files;
        handleFileSelect(fileInput);
    }
}

function handleFileSelect(input) {
    const files = Array.from(input.files);
    if (files.length === 0) return;
    
    // Check maximum files limit
    if (files.length > 10) {
        alert('You can upload a maximum of 10 files at once.');
        input.value = ''; // Clear the input
        return;
    }
    
    selectedFiles = [];
    
    files.forEach((file, index) => {
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
            return;
        }
        
        // Validate file type
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            alert(`Unsupported file type for "${file.name}".`);
            return;
        }
        
        selectedFiles.push(file);
    });
    
    if (selectedFiles.length > 0) {
        displayFilesPreview();
        
        // Auto-fill document name with first file if empty
        const nameInput = document.getElementById('name');
        if (!nameInput.value && selectedFiles.length > 0) {
            if (selectedFiles.length === 1) {
                nameInput.value = selectedFiles[0].name.replace(/\.[^/.]+$/, "");
            } else {
                nameInput.value = `${selectedFiles.length} documents`;
            }
        }
        
        // Auto-detect document type based on first file
        if (selectedFiles.length > 0) {
            autoDetectDocumentType(selectedFiles[0].name);
        }
    }
}

function displayFilesPreview() {
    const filesPreview = document.getElementById('filesPreview');
    const filesList = document.getElementById('filesList');
    const filesCount = document.getElementById('filesCount');
    
    // Clear previous files
    filesList.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center p-3 bg-gray-50 rounded-md';
        fileItem.innerHTML = `
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900">${file.name}</p>
                <p class="text-sm text-gray-500">${formatFileSize(file.size)}</p>
            </div>
            <button type="button" 
                    onclick="removeFile(${index})"
                    class="ml-3 text-red-600 hover:text-red-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        filesList.appendChild(fileItem);
    });
    
    filesCount.textContent = `${selectedFiles.length} file${selectedFiles.length !== 1 ? 's' : ''} selected`;
    filesPreview.classList.remove('hidden');
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    
    if (selectedFiles.length === 0) {
        removeAllFiles();
    } else {
        displayFilesPreview();
        updateFileInput();
    }
}

function removeAllFiles() {
    selectedFiles = [];
    document.getElementById('files').value = '';
    document.getElementById('filesPreview').classList.add('hidden');
}

function updateFileInput() {
    // Create a new FileList with remaining files
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function autoDetectDocumentType(filename) {
    const typeSelect = document.getElementById('type');
    const lowerName = filename.toLowerCase();
    
    if (lowerName.includes('invoice') || lowerName.includes('fatura')) {
        typeSelect.value = 'invoice';
    } else if (lowerName.includes('receipt') || lowerName.includes('recibo')) {
        typeSelect.value = 'receipt';
    } else if (lowerName.includes('contract') || lowerName.includes('contrato')) {
        typeSelect.value = 'contract';
    } else if (lowerName.includes('report') || lowerName.includes('relatorio')) {
        typeSelect.value = 'report';
    }
}

// Form submission with progress
document.getElementById('documentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!selectedFiles || selectedFiles.length === 0) {
        alert('Please select at least one file.');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    const progressModal = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitText.textContent = 'Uploading...';
    submitSpinner.classList.remove('hidden');
    
    // Show progress modal
    progressModal.classList.remove('hidden');
    
    // Create FormData
    const formData = new FormData(this);
    
    // Create XMLHttpRequest for progress tracking
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressText.textContent = `Uploading... ${Math.round(percentComplete)}%`;
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200 || xhr.status === 302) {
            progressText.textContent = 'Upload completed! Redirecting...';
            setTimeout(() => {
                window.location.href = '{{ route("tenant.documents.index") }}';
            }, 1000);
        } else {
            progressModal.classList.add('hidden');
            submitBtn.disabled = false;
            submitText.textContent = 'Upload Document';
            submitSpinner.classList.add('hidden');
            alert('Upload error. Please try again.');
        }
    });
    
    xhr.addEventListener('error', function() {
        progressModal.classList.add('hidden');
        submitBtn.disabled = false;
        submitText.textContent = 'Upload Document';
        submitSpinner.classList.add('hidden');
        alert('Upload error. Check your connection and try again.');
    });
    
    xhr.open('POST', this.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});

// Auto-save form data to localStorage
function saveFormData() {
    const formData = {
        name: document.getElementById('name').value,
        type: document.getElementById('type').value,
        description: document.getElementById('description').value,
        client_id: document.getElementById('client_id').value,
        tags: document.getElementById('tags').value,
        priority: document.getElementById('priority').value,
        auto_process: document.querySelector('input[name="auto_process"]').checked,
        extract_text: document.querySelector('input[name="extract_text"]').checked,
        ai_analysis: document.querySelector('input[name="ai_analysis"]').checked,
        quickbooks_sync: document.querySelector('input[name="quickbooks_sync"]').checked
    };
    
    localStorage.setItem('documentFormData', JSON.stringify(formData));
}

// Restore form data from localStorage
function restoreFormData() {
    const savedData = localStorage.getItem('documentFormData');
    if (savedData) {
        const formData = JSON.parse(savedData);
        
        Object.keys(formData).forEach(key => {
            const element = document.getElementById(key) || document.querySelector(`input[name="${key}"]`);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = formData[key];
                } else {
                    element.value = formData[key];
                }
            }
        });
    }
}

// Save form data on input changes
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('change', saveFormData);
        input.addEventListener('input', saveFormData);
    });
    
    // Restore form data on page load
    restoreFormData();
});

// Clear saved data on successful submission
window.addEventListener('beforeunload', function() {
    if (document.getElementById('submitBtn').disabled) {
        localStorage.removeItem('documentFormData');
    }
});
</script>
@endsection