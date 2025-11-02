@extends('layouts.app')

@section('title', 'Processing Documents')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Processing Documents</h1>
        <p class="text-slate-600 mt-2">Your documents are being processed by our AI system</p>
    </div>

    <!-- Processing Status Card -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex items-center mb-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
            <h2 class="text-xl font-semibold text-slate-900">Processing in Progress</h2>
        </div>
        
        <div class="space-y-4">
            <!-- Progress Bar -->
            <div class="w-full bg-slate-200 rounded-full h-3">
                <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
            </div>
            
            <!-- Status Text -->
            <div id="status-text" class="text-center text-slate-600">
                Initializing processing...
            </div>
            
            <!-- Duplicate Notifications -->
            <div id="duplicate-notifications" class="space-y-2 hidden">
                <!-- Duplicate notifications will be populated here -->
            </div>
            
            <!-- Document List -->
            <div id="document-list" class="space-y-2">
                <!-- Documents will be populated here -->
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Please wait</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Your documents are being processed by our AI system. This may take a few minutes depending on the file size and complexity. You'll be automatically redirected when processing is complete.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let processingInterval;
let startTime = Date.now();

function checkProcessingStatus() {
    fetch('{{ route("tenant.documents.check-processing") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Processing status response:', data); // Debug log
        
        updateProgressDisplay(data);

        // Só redirecionar se realmente completou
        if (data.status === 'completed') {
            clearInterval(processingInterval);
            console.log('Processing completed, redirecting in 3 seconds...'); // Debug log
            setTimeout(() => {
                window.location.href = '{{ route("tenant.documents.index") }}?success=' + 
                    encodeURIComponent(data.message);
            }, 3000); // Aumentar para 3 segundos para dar tempo de ver a mensagem
        } else if (data.status === 'no_session' || data.status === 'no_documents') {
            // Se não há sessão ou documentos, redirecionar imediatamente
            clearInterval(processingInterval);
            console.log('No session or documents, redirecting...'); // Debug log
            window.location.href = '{{ route("tenant.documents.index") }}?info=' + 
                encodeURIComponent(data.message);
        }
    })
    .catch(error => {
        console.error('Error checking processing status:', error);
        // Em caso de erro, não redirecionar imediatamente, tentar novamente
        // Se houver muitos erros consecutivos, pode implementar um contador
    });
}

function updateProgressDisplay(data) {
    const progressBar = document.getElementById('progress-bar');
    const statusText = document.getElementById('status-text');
    const duplicateNotifications = document.getElementById('duplicate-notifications');
    
    // Update progress bar
    if (data.percentage !== undefined) {
        progressBar.style.width = data.percentage + '%';
    }
    
    // Update status text
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    if (data.message) {
        statusText.innerHTML = `
            ${data.message}
            <br><small class="text-slate-500">Tempo decorrido: ${elapsed}s</small>
        `;
    }
    
    // Update duplicate notifications
    if (data.duplicate_notifications && data.duplicate_notifications.length > 0) {
        duplicateNotifications.classList.remove('hidden');
        duplicateNotifications.innerHTML = '';
        
        data.duplicate_notifications.forEach(notification => {
            const notificationElement = document.createElement('div');
            notificationElement.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-4';
            notificationElement.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Fatura Duplicada Detectada</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p><strong>Documento:</strong> ${notification.document_name}</p>
                            ${notification.duplicate_details ? `
                                <p><strong>Cliente:</strong> ${notification.duplicate_details.client_name || 'N/A'}</p>
                                <p><strong>Número da Fatura:</strong> ${notification.duplicate_details.invoice_number || 'N/A'}</p>
                                <p><strong>Valor:</strong> ${notification.duplicate_details.amount || 'N/A'}</p>
                            ` : ''}
                            <p class="mt-2 text-xs text-yellow-600">
                                O n8n detectou que esta fatura já existe para este cliente. 
                                Verifique os dados antes de prosseguir.
                            </p>
                        </div>
                    </div>
                </div>
            `;
            duplicateNotifications.appendChild(notificationElement);
        });
    } else {
        duplicateNotifications.classList.add('hidden');
    }
    
    // Log detalhado para debug
    console.log('Progress update:', {
        percentage: data.percentage,
        status: data.status,
        processed: data.processed_count,
        failed: data.failed_count,
        processing: data.processing_count,
        pending: data.pending_count,
        total: data.total_count,
        duplicates: data.duplicate_notifications ? data.duplicate_notifications.length : 0,
        elapsed: elapsed
    });
}

// Start checking processing status
document.addEventListener('DOMContentLoaded', function() {
    console.log('Processing page loaded, starting status checks...'); // Debug log
    checkProcessingStatus(); // Check immediately
    processingInterval = setInterval(checkProcessingStatus, 3000); // Aumentar para 3 segundos para reduzir carga
});

// Clear interval when page is unloaded
window.addEventListener('beforeunload', function() {
    console.log('Page unloading, clearing interval...'); // Debug log
    if (processingInterval) {
        clearInterval(processingInterval);
    }
});

// Adicionar listener para visibilidade da página
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        console.log('Page hidden, clearing interval...');
        if (processingInterval) {
            clearInterval(processingInterval);
        }
    } else {
        console.log('Page visible, restarting status checks...');
        if (!processingInterval) {
            checkProcessingStatus();
            processingInterval = setInterval(checkProcessingStatus, 3000);
        }
    }
});
</script>
@endpush
@endsection