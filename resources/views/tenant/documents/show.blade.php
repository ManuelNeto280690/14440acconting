@extends('layouts.tenant')

@section('title', 'Detalhes do Documento')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $document->name }}</h1>
            <p class="text-gray-600">Detalhes e informações do documento</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('tenant.documents.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
            
            <a href="{{ route('tenant.documents.edit', $document) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Document Preview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Visualização do Documento</h3>
                
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                    @php
                        $extension = strtolower(pathinfo($document->name, PATHINFO_EXTENSION));
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                        $isPdf = $extension === 'pdf';
                    @endphp
                    
                    @if($isImage)
                        <img src="{{ route('tenant.documents.download', $document) }}" 
                             alt="{{ $document->name }}"
                             class="max-w-full h-auto mx-auto rounded-lg shadow-md">
                    @elseif($isPdf)
                        <div class="text-center">
                            <svg class="w-16 h-16 text-red-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">Documento PDF</p>
                            <p class="text-gray-600 mb-4">Clique no botão abaixo para visualizar o PDF</p>
                            <a href="{{ route('tenant.documents.download', $document) }}" 
                               target="_blank"
                               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                                Abrir PDF
                            </a>
                        </div>
                    @else
                        <div class="text-center">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">{{ strtoupper($extension) }} Document</p>
                            <p class="text-gray-600 mb-4">Visualização não disponível para este tipo de arquivo</p>
                            <a href="{{ route('tenant.documents.download', $document) }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                                Baixar Arquivo
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Extracted Text -->
            @if($document->extracted_text)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Texto Extraído (OCR)</h3>
                    <div class="bg-gray-50 p-4 rounded-md max-h-96 overflow-y-auto">
                        <pre class="whitespace-pre-wrap text-sm text-gray-700">{{ $document->extracted_text }}</pre>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" 
                                onclick="copyToClipboard('{{ addslashes($document->extracted_text) }}')"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Copiar Texto
                        </button>
                    </div>
                </div>
            @endif

            <!-- AI Analysis -->
            @if($document->ai_analysis)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Análise via IA</h3>
                    <div class="prose max-w-none">
                        {!! nl2br(e($document->ai_analysis)) !!}
                    </div>
                </div>
            @endif

            <!-- Processing History -->
            @if($document->processing_history)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Histórico de Processamento</h3>
                    <div class="space-y-4">
                        @foreach(json_decode($document->processing_history, true) ?? [] as $entry)
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-md">
                                <div class="flex-shrink-0">
                                    @if($entry['status'] === 'success')
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @elseif($entry['status'] === 'error')
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $entry['action'] ?? 'Ação' }}</p>
                                            <p class="text-sm text-gray-600">{{ $entry['message'] ?? 'Sem mensagem' }}</p>
                                        </div>
                                        <span class="text-xs text-gray-500">
                                            {{ isset($entry['timestamp']) ? \Carbon\Carbon::parse($entry['timestamp'])->format('d/m/Y H:i') : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Document Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Documento</h3>
                
                <div class="space-y-4">
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        @php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'processed' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                            ];
                            $statusLabels = [
                                'pending' => 'Pendente',
                                'processing' => 'Processando',
                                'processed' => 'Processado',
                                'failed' => 'Falhou',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$document->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$document->status] ?? $document->status }}
                        </span>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        @php
                            $typeLabels = [
                                'invoice' => 'Fatura',
                                'receipt' => 'Recibo',
                                'contract' => 'Contrato',
                                'report' => 'Relatório',
                                'other' => 'Outro'
                            ];
                        @endphp
                        <p class="text-sm text-gray-900">{{ $typeLabels[$document->type] ?? $document->type }}</p>
                    </div>

                    <!-- File Size -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tamanho</label>
                        <p class="text-sm text-gray-900">{{ number_format($document->size / 1024, 1) }} KB</p>
                    </div>

                    <!-- MIME Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Arquivo</label>
                        <p class="text-sm text-gray-900">{{ $document->mime_type }}</p>
                    </div>

                    <!-- Priority -->
                    @if($document->priority && $document->priority !== 'normal')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                            @php
                                $priorityLabels = [
                                    'high' => 'Alta',
                                    'urgent' => 'Urgente'
                                ];
                                $priorityClasses = [
                                    'high' => 'bg-orange-100 text-orange-800',
                                    'urgent' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $priorityClasses[$document->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $priorityLabels[$document->priority] ?? $document->priority }}
                            </span>
                        </div>
                    @endif

                    <!-- Client -->
                    @if($document->client)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                            <a href="{{ route('tenant.clients.show', $document->client) }}" 
                               class="text-sm text-blue-600 hover:text-blue-800">
                                {{ $document->client->name }}
                            </a>
                        </div>
                    @endif

                    <!-- Tags -->
                    @if($document->tags)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                            <div class="flex flex-wrap gap-1">
                                @foreach(explode(',', $document->tags) as $tag)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                        {{ trim($tag) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Dates -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Upload</label>
                        <p class="text-sm text-gray-900">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if($document->processed_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Processamento</label>
                            <p class="text-sm text-gray-900">{{ $document->processed_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <!-- Download -->
                    <a href="{{ route('tenant.documents.download', $document) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Baixar Documento
                    </a>

                    <!-- Process -->
                    @if(in_array($document->status, ['pending', 'failed']))
                        <button type="button" 
                                onclick="processDocument()"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Processar Documento
                        </button>
                    @endif

                    <!-- Reprocess -->
                    @if($document->status === 'processed')
                        <button type="button" 
                                onclick="reprocessDocument()"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reprocessar
                        </button>
                    @endif

                    <!-- Share -->
                    <button type="button" 
                            onclick="shareDocument()"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                        Compartilhar
                    </button>

                    <!-- Delete -->
                    <button type="button" 
                            onclick="deleteDocument()"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Excluir Documento
                    </button>
                </div>
            </div>

            <!-- Description -->
            @if($document->description)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Descrição</h3>
                    <p class="text-sm text-gray-700">{{ $document->description }}</p>
                </div>
            @endif

            <!-- Metadata -->
            @if($document->meta && is_array(json_decode($document->meta, true)))
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Metadados</h3>
                    <div class="space-y-2">
                        @foreach(json_decode($document->meta, true) as $key => $value)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ ucfirst($key) }}:</span>
                                <span class="text-sm text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Share Modal -->
<div id="shareModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Compartilhar Documento</h3>
                <button type="button" 
                        onclick="closeShareModal()"
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label for="share_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email do Destinatário
                    </label>
                    <input type="email" 
                           id="share_email" 
                           placeholder="email@exemplo.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="share_message" class="block text-sm font-medium text-gray-700 mb-2">
                        Mensagem (opcional)
                    </label>
                    <textarea id="share_message" 
                              rows="3"
                              placeholder="Mensagem personalizada..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               id="share_download_allowed"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Permitir download</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" 
                        onclick="closeShareModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Cancelar
                </button>
                <button type="button" 
                        onclick="sendShare()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Compartilhar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function processDocument() {
    if (confirm('Deseja processar este documento?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("tenant.documents.process", $document) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function reprocessDocument() {
    if (confirm('Deseja reprocessar este documento? Isso substituirá os dados existentes.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("tenant.documents.reprocess", $document) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteDocument() {
    if (confirm('Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("tenant.documents.destroy", $document) }}';
        
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

function shareDocument() {
    document.getElementById('shareModal').classList.remove('hidden');
}

function closeShareModal() {
    document.getElementById('shareModal').classList.add('hidden');
    // Clear form
    document.getElementById('share_email').value = '';
    document.getElementById('share_message').value = '';
    document.getElementById('share_download_allowed').checked = false;
}

function sendShare() {
    const email = document.getElementById('share_email').value;
    const message = document.getElementById('share_message').value;
    const downloadAllowed = document.getElementById('share_download_allowed').checked;
    
    if (!email) {
        alert('Por favor, informe o email do destinatário.');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("tenant.documents.share", $document) }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const emailInput = document.createElement('input');
    emailInput.type = 'hidden';
    emailInput.name = 'email';
    emailInput.value = email;
    
    const messageInput = document.createElement('input');
    messageInput.type = 'hidden';
    messageInput.name = 'message';
    messageInput.value = message;
    
    const downloadInput = document.createElement('input');
    downloadInput.type = 'hidden';
    downloadInput.name = 'download_allowed';
    downloadInput.value = downloadAllowed ? '1' : '0';
    
    form.appendChild(csrfToken);
    form.appendChild(emailInput);
    form.appendChild(messageInput);
    form.appendChild(downloadInput);
    
    document.body.appendChild(form);
    form.submit();
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show temporary success message
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copiado!';
        button.classList.add('text-green-600');
        
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('text-green-600');
        }, 2000);
    }).catch(function(err) {
        console.error('Erro ao copiar texto: ', err);
        alert('Erro ao copiar texto para a área de transferência.');
    });
}

// Close modal when clicking outside
document.getElementById('shareModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeShareModal();
    }
});

// Auto-refresh status if processing
@if($document->status === 'processing')
    setTimeout(() => {
        location.reload();
    }, 10000); // Refresh every 10 seconds
@endif
</script>
@endsection