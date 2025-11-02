@extends('layouts.tenant')

@section('title', 'Chat com IA')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        Chat com IA
                    </h1>
                    <p class="text-gray-600 mt-2">Converse com nossa inteligência artificial para obter ajuda e insights</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('tenant.chat.index') }}" 
                       class="bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl font-medium transition-all duration-200 shadow-sm border border-gray-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Chat com Clientes
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total de Mensagens</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_messages'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Mensagens Hoje</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['today_messages'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Suas Mensagens</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['user_messages'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Respostas da IA</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['ai_responses'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden" style="height: 600px;">
            <!-- Chat Header -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Assistente IA</h3>
                            <p class="text-sm text-white/80">Online • Sempre disponível</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm">Conectado</span>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div id="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4" style="height: 450px;">
                @if($messages->count() > 0)
                    @foreach($messages as $message)
                        <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs lg:max-w-md">
                                @if($message->direction === 'outbound')
                                    <!-- User Message -->
                                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-2xl rounded-br-md px-4 py-3">
                                        <p class="text-sm">{{ $message->message }}</p>
                                        <p class="text-xs text-white/70 mt-2">{{ $message->created_at->format('H:i') }}</p>
                                    </div>
                                @else
                                    <!-- AI Response -->
                                    <div class="bg-gray-100 text-gray-900 rounded-2xl rounded-bl-md px-4 py-3">
                                        <div class="flex items-start gap-2">
                                            <div class="w-6 h-6 bg-gradient-to-br from-purple-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm">{{ $message->message }}</p>
                                                @if($message->ai_confidence)
                                                    <div class="flex items-center gap-2 mt-2">
                                                        <span class="text-xs text-gray-500">Confiança:</span>
                                                        <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                            <div class="h-full bg-gradient-to-r from-green-400 to-green-600 rounded-full" 
                                                                 style="width: {{ $message->ai_confidence * 100 }}%"></div>
                                                        </div>
                                                        <span class="text-xs text-gray-500">{{ number_format($message->ai_confidence * 100, 1) }}%</span>
                                                    </div>
                                                @endif
                                                <p class="text-xs text-gray-500 mt-2">{{ $message->created_at->format('H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Inicie uma conversa</h3>
                        <p class="text-gray-600">Envie sua primeira mensagem para começar a conversar com a IA</p>
                    </div>
                @endif
            </div>

            <!-- Message Input -->
            <div class="border-t border-gray-200 p-4">
                <form id="aiChatForm" class="flex gap-3">
                    @csrf
                    <div class="flex-1">
                        <textarea id="messageInput" 
                                  name="message" 
                                  rows="1"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none transition-all duration-200" 
                                  placeholder="Digite sua mensagem..."
                                  required></textarea>
                    </div>
                    <button type="submit" 
                            id="sendButton"
                            class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg id="sendIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <svg id="loadingIcon" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="sendText">Enviar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
class AIChatManager {
    constructor() {
        this.form = document.getElementById('aiChatForm');
        this.messageInput = document.getElementById('messageInput');
        this.messagesContainer = document.getElementById('messagesContainer');
        this.sendButton = document.getElementById('sendButton');
        this.sendIcon = document.getElementById('sendIcon');
        this.loadingIcon = document.getElementById('loadingIcon');
        this.sendText = document.getElementById('sendText');
        this.lastMessageId = null;
        this.pollingInterval = null;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.autoResize();
        this.scrollToBottom();
        this.startPolling();
        
        // Get last message ID
        const messages = document.querySelectorAll('[data-message-id]');
        if (messages.length > 0) {
            this.lastMessageId = messages[messages.length - 1].dataset.messageId;
        }
    }

    bindEvents() {
        this.form.addEventListener('submit', this.sendMessage.bind(this));
        this.messageInput.addEventListener('input', this.autoResize.bind(this));
        this.messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage(e);
            }
        });
    }

    async sendMessage(e) {
        e.preventDefault();
        
        const message = this.messageInput.value.trim();
        if (!message) return;

        // Disable form
        this.setLoading(true);

        // Add user message to UI immediately
        this.addMessageToUI(message, 'outbound');
        this.messageInput.value = '';
        this.autoResize();
        this.scrollToBottom();

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const xsrfCookie = this.getCookie('XSRF-TOKEN'); // fallback para header X-XSRF-TOKEN

            const response = await fetch('{{ route("tenant.chat.ai.send") }}', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfMeta,
                    'X-XSRF-TOKEN': xsrfCookie || csrfMeta,
                },
                body: JSON.stringify({
                    message,
                    _token: csrfMeta
                })
            });

            const data = await response.json();

            if (!data.success) {
                this.showError(data.message || 'Erro ao enviar mensagem');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            this.showError('Erro de conexão. Tente novamente.');
        } finally {
            this.setLoading(false);
        }
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            return decodeURIComponent(parts.pop().split(';').shift());
        }
        return null;
    }
    addMessageToUI(message, direction, aiData = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${direction === 'outbound' ? 'justify-end' : 'justify-start'}`;
        
        const now = new Date();
        const timeStr = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

        if (direction === 'outbound') {
            messageDiv.innerHTML = `
                <div class="max-w-xs lg:max-w-md">
                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-2xl rounded-br-md px-4 py-3">
                        <p class="text-sm">${this.escapeHtml(message)}</p>
                        <p class="text-xs text-white/70 mt-2">${timeStr}</p>
                    </div>
                </div>
            `;
        } else {
            const confidenceBar = aiData?.ai_confidence ? `
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-gray-500">Confiança:</span>
                    <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-green-400 to-green-600 rounded-full" 
                             style="width: ${aiData.ai_confidence * 100}%"></div>
                    </div>
                    <span class="text-xs text-gray-500">${(aiData.ai_confidence * 100).toFixed(1)}%</span>
                </div>
            ` : '';

            messageDiv.innerHTML = `
                <div class="max-w-xs lg:max-w-md">
                    <div class="bg-gray-100 text-gray-900 rounded-2xl rounded-bl-md px-4 py-3">
                        <div class="flex items-start gap-2">
                            <div class="w-6 h-6 bg-gradient-to-br from-purple-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm">${this.escapeHtml(message)}</p>
                                ${confidenceBar}
                                <p class="text-xs text-gray-500 mt-2">${timeStr}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        if (aiData?.id) {
            messageDiv.dataset.messageId = aiData.id;
        }

        this.messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    async startPolling() {
        this.pollingInterval = setInterval(async () => {
            await this.pollNewMessages();
        }, 3000); // Poll every 3 seconds
    }

    async pollNewMessages() {
        if (!this.lastMessageId) return;

        try {
            const response = await fetch(`{{ route("tenant.chat.ai.messages") }}?after=${this.lastMessageId}`);
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                data.messages.forEach(message => {
                    if (message.direction === 'inbound') {
                        this.addMessageToUI(message.message, 'inbound', message);
                        this.lastMessageId = message.id;
                    }
                });
            }
        } catch (error) {
            console.error('Error polling messages:', error);
        }
    }

    setLoading(loading) {
        this.sendButton.disabled = loading;
        this.messageInput.disabled = loading;
        
        if (loading) {
            this.sendIcon.classList.add('hidden');
            this.loadingIcon.classList.remove('hidden');
            this.sendText.textContent = 'Enviando...';
        } else {
            this.sendIcon.classList.remove('hidden');
            this.loadingIcon.classList.add('hidden');
            this.sendText.textContent = 'Enviar';
        }
    }

    autoResize() {
        this.messageInput.style.height = 'auto';
        this.messageInput.style.height = Math.min(this.messageInput.scrollHeight, 120) + 'px';
    }

    scrollToBottom() {
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    showError(message) {
        // You can implement a toast notification here
        alert(message);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AIChatManager();
});
</script>
@endsection