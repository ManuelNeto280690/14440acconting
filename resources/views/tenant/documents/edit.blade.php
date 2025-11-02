@extends('layouts.tenant')

@section('title', 'Editar Documento')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Editar Documento</h1>
            <p class="text-gray-600">Atualize as informações do documento</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('tenant.documents.show', $document) }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('tenant.documents.update', $document) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Document Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome do Documento <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $document->name) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Document Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo do Documento <span class="text-red-500">*</span>
                            </label>
                            <select id="type" 
                                    name="type" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione o tipo</option>
                                <option value="invoice" {{ old('type', $document->type) === 'invoice' ? 'selected' : '' }}>Fatura</option>
                                <option value="receipt" {{ old('type', $document->type) === 'receipt' ? 'selected' : '' }}>Recibo</option>
                                <option value="contract" {{ old('type', $document->type) === 'contract' ? 'selected' : '' }}>Contrato</option>
                                <option value="report" {{ old('type', $document->type) === 'report' ? 'selected' : '' }}>Relatório</option>
                                <option value="other" {{ old('type', $document->type) === 'other' ? 'selected' : '' }}>Outro</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Descrição do documento..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $document->description) }}</textarea>
                        </div>

                        <!-- Client Association -->
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Cliente Associado
                            </label>
                            <select id="client_id" 
                                    name="client_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Nenhum cliente</option>
                                @if(isset($clients))
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $document->client_id) == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                Tags
                            </label>
                            <input type="text" 
                                   id="tags" 
                                   name="tags" 
                                   value="{{ old('tags', is_array($document->tags) ? implode(', ', $document->tags) : $document->tags) }}"
                                   placeholder="contabilidade, fiscal, 2024..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">
                                Separe as tags com vírgulas
                            </p>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                Prioridade
                            </label>
                            <select id="priority" 
                                    name="priority" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="normal" {{ old('priority', $document->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority', $document->priority) === 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="urgent" {{ old('priority', $document->priority) === 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                        </div>

                        <!-- Status (if admin or specific permissions) -->
                        @if(auth()->user()->hasRole('admin') || auth()->user()->can('manage-documents'))
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select id="status" 
                                        name="status" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="pending" {{ old('status', $document->status) === 'pending' ? 'selected' : '' }}>Pendente</option>
                                    <option value="processing" {{ old('status', $document->status) === 'processing' ? 'selected' : '' }}>Processando</option>
                                    <option value="processed" {{ old('status', $document->status) === 'processed' ? 'selected' : '' }}>Processado</option>
                                    <option value="failed" {{ old('status', $document->status) === 'failed' ? 'selected' : '' }}>Falhou</option>
                                </select>
                            </div>
                        @endif

                        <!-- Extracted Text (editable if processed) -->
                        @if($document->extracted_text)
                            <div>
                                <label for="extracted_text" class="block text-sm font-medium text-gray-700 mb-2">
                                    Texto Extraído
                                </label>
                                <textarea id="extracted_text" 
                                          name="extracted_text" 
                                          rows="8"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm">{{ old('extracted_text', $document->extracted_text) }}</textarea>
                                <p class="text-sm text-gray-500 mt-1">
                                    Você pode editar o texto extraído se necessário
                                </p>
                            </div>
                        @endif

                        <!-- AI Analysis (editable if processed) -->
                        @if($document->ai_analysis)
                            <div>
                                <label for="ai_analysis" class="block text-sm font-medium text-gray-700 mb-2">
                                    Análise via IA
                                </label>
                                <textarea id="ai_analysis" 
                                          name="ai_analysis" 
                                          rows="6"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('ai_analysis', $document->ai_analysis) }}</textarea>
                                <p class="text-sm text-gray-500 mt-1">
                                    Você pode editar ou complementar a análise via IA
                                </p>
                            </div>
                        @endif

                        <!-- Custom Metadata -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Metadados Personalizados
                            </label>
                            <div id="metadata-container" class="space-y-3">
                                @php
                                    $metadata = json_decode($document->meta, true) ?? [];
                                @endphp
                                @foreach($metadata as $key => $value)
                                    <div class="flex gap-2 metadata-row">
                                        <input type="text" 
                                               name="meta_keys[]" 
                                               value="{{ $key }}"
                                               placeholder="Chave"
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <input type="text" 
                                               name="meta_values[]" 
                                               value="{{ is_array($value) ? json_encode($value) : $value }}"
                                               placeholder="Valor"
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <button type="button" 
                                                onclick="removeMetadataRow(this)"
                                                class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                                
                                <!-- Empty row for new metadata -->
                                <div class="flex gap-2 metadata-row">
                                    <input type="text" 
                                           name="meta_keys[]" 
                                           placeholder="Nova chave"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <input type="text" 
                                           name="meta_values[]" 
                                           placeholder="Novo valor"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="button" 
                                            onclick="removeMetadataRow(this)"
                                            class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" 
                                    onclick="addMetadataRow()"
                                    class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                + Adicionar Metadado
                            </button>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('tenant.documents.show', $document) }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md font-medium transition-colors duration-200">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200">
                            Atualizar Documento
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Current File Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Arquivo Atual</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
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
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $document->original_name ?? $document->name }}</p>
                            <p class="text-sm text-gray-500">{{ number_format($document->size / 1024, 1) }} KB</p>
                        </div>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-2">Tipo MIME:</p>
                        <p class="text-sm text-gray-900">{{ $document->mime_type }}</p>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-2">Upload em:</p>
                        <p class="text-sm text-gray-900">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    @if($document->processed_at)
                        <div class="pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-500 mb-2">Processado em:</p>
                            <p class="text-sm text-gray-900">{{ $document->processed_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('tenant.documents.download', $document) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Baixar Arquivo
                    </a>
                </div>
            </div>

            <!-- Processing Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ações de Processamento</h3>
                
                <div class="space-y-3">
                    @if(in_array($document->status, ['pending', 'failed']))
                        <button type="button" 
                                onclick="processDocument()"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Processar Agora
                        </button>
                    @endif

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

                    <button type="button" 
                            onclick="resetProcessing()"
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Resetar Status
                    </button>
                </div>
            </div>

            <!-- Replace File -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Substituir Arquivo</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Você pode substituir o arquivo atual mantendo os metadados.
                </p>
                
                <form method="POST" 
                      action="{{ route('tenant.documents.replace-file', $document) }}" 
                      enctype="multipart/form-data"
                      onsubmit="return confirm('Tem certeza que deseja substituir o arquivo? Esta ação não pode ser desfeita.')">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-3">
                        <input type="file" 
                               name="file" 
                               required
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        
                        <button type="submit" 
                                class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                            Substituir Arquivo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <h3 class="text-lg font-medium text-red-900 mb-4">Zona de Perigo</h3>
                
                <div class="space-y-3">
                    <button type="button" 
                            onclick="deleteDocument()"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Excluir Documento
                    </button>
                </div>
                
                <p class="text-sm text-red-600 mt-2">
                    Esta ação não pode ser desfeita. O documento será permanentemente removido.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function addMetadataRow() {
    const container = document.getElementById('metadata-container');
    const newRow = document.createElement('div');
    newRow.className = 'flex gap-2 metadata-row';
    newRow.innerHTML = `
        <input type="text" 
               name="meta_keys[]" 
               placeholder="Nova chave"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="text" 
               name="meta_values[]" 
               placeholder="Novo valor"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="button" 
                onclick="removeMetadataRow(this)"
                class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        </button>
    `;
    container.appendChild(newRow);
}

function removeMetadataRow(button) {
    const row = button.closest('.metadata-row');
    const container = document.getElementById('metadata-container');
    
    // Don't remove if it's the last row
    if (container.children.length > 1) {
        row.remove();
    } else {
        // Clear the inputs instead
        row.querySelectorAll('input').forEach(input => input.value = '');
    }
}

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

function resetProcessing() {
    if (confirm('Deseja resetar o status de processamento? O documento voltará ao status pendente.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("tenant.documents.reset-processing", $document) }}';
        
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

// Auto-save form data to localStorage
function saveFormData() {
    const formData = new FormData(document.querySelector('form'));
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        if (data[key]) {
            if (Array.isArray(data[key])) {
                data[key].push(value);
            } else {
                data[key] = [data[key], value];
            }
        } else {
            data[key] = value;
        }
    }
    
    localStorage.setItem('documentEditFormData', JSON.stringify(data));
}

// Save form data on input changes
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('change', saveFormData);
        input.addEventListener('input', saveFormData);
    });
});

// Clear saved data on successful submission
window.addEventListener('beforeunload', function() {
    localStorage.removeItem('documentEditFormData');
});
</script>
@endsection