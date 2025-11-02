@extends('layouts.tenant')

@section('title', 'Chat - Conversa')

@section('content')
<div class="h-screen flex bg-gray-50">
    <!-- Sidebar - Lista de Conversas -->
    <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
        <!-- Header da Sidebar -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Conversas</h2>
                <button onclick="openNewConversationModal()" 
                        class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </button>
            </div>
            
            <!-- Busca -->
            <div class="mt-3">
                <div class="relative">
                    <input type="text" 
                           id="searchConversations"
                           placeholder="Buscar conversas..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Conversas -->
        <div class="flex-1 overflow-y-auto">
            @forelse($conversations as $conversation)
                <div class="conversation-item p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors {{ $conversation->id == $client->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
                     onclick="loadConversation({{ $conversation->id }})">
                    <div class="flex items-start space-x-3">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($conversation->name, 0, 2)) }}
                            </div>
                        </div>
                        
                        <!-- Conteúdo -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $conversation->name }}</h3>
                                @if($conversation->last_message_at)
                                    <span class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                            
                            <p class="text-sm text-gray-600 truncate">{{ $conversation->email }}</p>
                            
                            @if($conversation->last_message)
                                <div class="flex items-center justify-between mt-1">
                                    <p class="text-sm text-gray-500 truncate">
                                        @if($conversation->last_message_direction == 'outbound')
                                            <span class="text-blue-600">Você:</span>
                                        @endif
                                        {{ Str::limit($conversation->last_message, 30) }}
                                    </p>
                                    
                                    @if($conversation->unread_count > 0)
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                            {{ $conversation->unread_count }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="mt-2">Nenhuma conversa encontrada</p>
                    <button onclick="openNewConversationModal()" 
                            class="mt-2 text-blue-600 hover:text-blue-800 font-medium">
                        Iniciar nova conversa
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Área Principal do Chat -->
    <div class="flex-1 flex flex-col">
        @if($client)
            <!-- Header do Chat -->
            <div class="bg-white border-b border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr($client->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $client->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $client->email }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button onclick="markAllAsRead({{ $client->id }})"
                                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                title="Marcar todas como lidas">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        
                        <button onclick="refreshMessages()"
                                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                title="Atualizar mensagens">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Área de Mensagens -->
            <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                @forelse($messages as $message)
                    <div class="message-item flex {{ $message->direction == 'outbound' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs lg:max-w-md">
                            <!-- Mensagem de Saída (Nossa) -->
                            @if($message->direction == 'outbound')
                                <div class="bg-blue-500 text-white rounded-lg px-4 py-2">
                                    <p class="text-sm">{{ $message->message }}</p>
                                    <div class="flex items-center justify-end mt-1 space-x-1">
                                        <span class="text-xs text-blue-100">
                                            {{ $message->created_at->format('H:i') }}
                                        </span>
                                        @if($message->status == 'delivered')
                                            <svg class="w-4 h-4 text-blue-100" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @elseif($message->status == 'read')
                                            <svg class="w-4 h-4 text-blue-100" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <svg class="w-4 h-4 text-blue-100 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <!-- Mensagem de Entrada (Cliente) -->
                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 shadow-sm">
                                    <p class="text-sm text-gray-900">{{ $message->message }}</p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-gray-500">
                                            {{ $message->created_at->format('H:i') }}
                                        </span>
                                        @if($message->status != 'read')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Não lida
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="mt-2">Nenhuma mensagem ainda</p>
                        <p class="text-sm text-gray-400">Envie a primeira mensagem para iniciar a conversa</p>
                    </div>
                @endforelse
            </div>

            <!-- Input de Mensagem -->
            <div class="bg-white border-t border-gray-200 p-4">
                <form id="messageForm" class="flex items-end space-x-3">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    
                    <div class="flex-1">
                        <textarea name="message" 
                                  id="messageInput"
                                  rows="1"
                                  placeholder="Digite sua mensagem..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                  style="min-height: 40px; max-height: 120px;"></textarea>
                    </div>
                    
                    <button type="submit" 
                            id="sendButton"
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        @else
            <!-- Estado Vazio -->
            <div class="flex-1 flex items-center justify-center bg-gray-50">
                <div class="text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Selecione uma conversa</h3>
                    <p class="mt-2 text-gray-500">Escolha uma conversa da lista para começar a conversar</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Nova Conversa -->
<div id="newConversationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Nova Conversa</h3>
                    <button onclick="closeNewConversationModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <form id="newConversationForm" onsubmit="startNewConversation(event)">
                    <div class="mb-4">
                        <label for="clientSelect" class="block text-sm font-medium text-gray-700 mb-2">
                            Selecionar Cliente
                        </label>
                        <select id="clientSelect" name="client_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Escolha um cliente...</option>
                            @foreach($allClients as $clientOption)
                                <option value="{{ $clientOption->id }}">
                                    {{ $clientOption->name }} ({{ $clientOption->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label for="initialMessage" class="block text-sm font-medium text-gray-700 mb-2">
                            Mensagem Inicial (Opcional)
                        </label>
                        <textarea id="initialMessage" name="message" rows="3"
                                  placeholder="Digite uma mensagem inicial..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeNewConversationModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Iniciar Conversa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentClientId = '{{ $client->id }}';
let messagePolling;
let typingTimeout;

// Inicializar chat
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    startMessagePolling();
    
    // Auto-focus no input
    document.getElementById('messageInput').focus();
});

// Enviar mensagem
document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendMessage();
});

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    const sendButton = document.getElementById('sendButton');
    sendButton.disabled = true;
    
    fetch(`{{ route('tenant.chat.send') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            client_id: currentClientId,
            message: message,
            type: 'text'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            autoResize(messageInput);
            addMessageToChat(data.message);
            scrollToBottom();
        } else {
            alert('Erro ao enviar mensagem: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao enviar mensagem');
    })
    .finally(() => {
        sendButton.disabled = false;
        messageInput.focus();
    });
}

// Carregar conversa
function loadConversation(clientId, clientName) {
    if (clientId === currentClientId) return;
    
    currentClientId = clientId;
    document.getElementById('currentClientId').value = clientId;
    document.getElementById('currentClientName').textContent = clientName;
    
    // Atualizar seleção na sidebar
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('bg-blue-50', 'border-l-4', 'border-l-blue-500');
    });
    document.querySelector(`[data-client-id="${clientId}"]`).classList.add('bg-blue-50', 'border-l-4', 'border-l-blue-500');
    
    // Carregar mensagens
    loadMessages(clientId);
}

function loadMessages(clientId) {
    fetch(`{{ url('chat/messages') }}/${clientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('messagesContainer');
                container.innerHTML = '';
                
                data.messages.forEach(message => {
                    addMessageToChat(message, false);
                });
                
                // Atualizar info do cliente
                document.getElementById('currentClientName').textContent = data.client.name;
                document.getElementById('currentClientEmail').textContent = data.client.email;
                
                scrollToBottom();
            }
        })
        .catch(error => {
            console.error('Erro ao carregar mensagens:', error);
        });
}

// Adicionar mensagem ao chat
function addMessageToChat(message, animate = true) {
    const container = document.getElementById('messagesContainer');
    const messageDiv = document.createElement('div');
    
    const isOutbound = message.direction === 'outbound';
    const messageClass = isOutbound ? 'ml-auto bg-blue-600 text-white' : 'mr-auto bg-white text-gray-900 border border-gray-200';
    const alignClass = isOutbound ? 'justify-end' : 'justify-start';
    
    messageDiv.className = `flex ${alignClass} ${animate ? 'animate-fade-in' : ''}`;
    messageDiv.innerHTML = `
        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${messageClass} shadow-sm">
            <p class="text-sm">${escapeHtml(message.message)}</p>
            <p class="text-xs mt-1 ${isOutbound ? 'text-blue-100' : 'text-gray-500'}">
                ${formatMessageTime(message.created_at)}
            </p>
        </div>
    `;
    
    container.appendChild(messageDiv);
    
    if (animate) {
        setTimeout(() => scrollToBottom(), 100);
    }
}

// Polling de mensagens
function startMessagePolling() {
    messagePolling = setInterval(() => {
        if (currentClientId) {
            checkNewMessages();
        }
    }, 3000);
}

function checkNewMessages() {
    const lastMessage = document.querySelector('#messagesContainer > div:last-child');
    const lastMessageTime = lastMessage ? lastMessage.dataset.time : null;
    
    fetch(`{{ url('chat/messages') }}/${currentClientId}?since=${lastMessageTime || ''}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.new_messages) {
                data.new_messages.forEach(message => {
                    addMessageToChat(message);
                });
            }
        })
        .catch(error => {
            console.error('Erro no polling:', error);
        });
}

// Utilitários
function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
}

function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

function handleKeyDown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

// Modais e menus
function openNewChatModal() {
    document.getElementById('newChatModal').classList.remove('hidden');
}

function closeNewChatModal() {
    document.getElementById('newChatModal').classList.add('hidden');
    document.getElementById('newChatForm').reset();
}

function toggleAttachmentMenu() {
    const menu = document.getElementById('attachmentMenu');
    menu.classList.toggle('hidden');
}

function selectFile(type) {
    const fileInput = document.getElementById('fileInput');
    if (type === 'image') {
        fileInput.accept = 'image/*';
    } else {
        fileInput.accept = 'application/pdf,.doc,.docx,.txt';
    }
    fileInput.click();
    toggleAttachmentMenu();
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        // Implementar upload de arquivo
        console.log('Arquivo selecionado:', file.name);
        // TODO: Implementar upload via AJAX
    }
}

function refreshConversations() {
    location.reload();
}

function viewClientProfile() {
    window.open(`{{ url('clients') }}/${currentClientId}`, '_blank');
}

function toggleChatInfo() {
    // TODO: Implementar painel de informações do chat
    console.log('Toggle chat info');
}

// Nova conversa
document.getElementById('newChatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`{{ route('tenant.chat.send') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeNewChatModal();
            loadConversation(data.client_id, data.client_name);
        } else {
            alert('Erro ao iniciar conversa: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao iniciar conversa');
    });
});

// Cleanup
window.addEventListener('beforeunload', function() {
    if (messagePolling) {
        clearInterval(messagePolling);
    }
});

// Fechar menus ao clicar fora
document.addEventListener('click', function(e) {
    const attachmentMenu = document.getElementById('attachmentMenu');
    if (!e.target.closest('#attachmentMenu') && !e.target.closest('button[onclick="toggleAttachmentMenu()"]')) {
        attachmentMenu.classList.add('hidden');
    }
    
    // Fechar modal ao clicar fora
    const modal = document.getElementById('newChatModal');
    if (e.target === modal) {
        closeNewChatModal();
    }
});
</script>

<style>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Scrollbar personalizada */
#messagesContainer::-webkit-scrollbar {
    width: 6px;
}

#messagesContainer::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#messagesContainer::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#messagesContainer::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

#conversationsList::-webkit-scrollbar {
    width: 4px;
}

#conversationsList::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#conversationsList::-webkit-scrollbar-thumb {
    background: #d1d1d1;
    border-radius: 2px;
}

/* Responsividade */
@media (max-width: 768px) {
    .w-1\/3 {
        width: 100%;
        position: absolute;
        z-index: 10;
        height: 100%;
    }
    
    .flex-1 {
        width: 100%;
    }
}
</style>
@endpush