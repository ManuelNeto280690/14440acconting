@extends('layouts.client')

@section('title', 'AI Assistant')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">AI Assistant</h1>
                    <p class="text-gray-600">Chat with our intelligent assistant for help and support</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('client.messages.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-comments mr-2"></i>
                        Messages
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-comments text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Messages</p>
                        <p class="text-2xl font-bold text-gray-900" id="total-messages">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-calendar-day text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Today</p>
                        <p class="text-2xl font-bold text-gray-900" id="today-messages">{{ $stats['today'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <i class="fas fa-user text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Your Messages</p>
                        <p class="text-2xl font-bold text-gray-900" id="user-messages">{{ $stats['user'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100">
                        <i class="fas fa-robot text-indigo-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">AI Responses</p>
                        <p class="text-2xl font-bold text-gray-900" id="ai-messages">{{ $stats['ai'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Chat Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                        <i class="fas fa-robot text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-white font-semibold">AI Assistant</h3>
                        <p class="text-blue-100 text-sm">Always here to help</p>
                    </div>
                    <div class="ml-auto">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5"></span>
                            Online
                        </span>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="h-96 overflow-y-auto p-6 space-y-4" id="messages-container">
                @forelse($messages as $message)
                    @if($message->sender_type === 'client')
                        <!-- User Message -->
                        <div class="flex justify-end">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="bg-blue-600 text-white rounded-lg px-4 py-2">
                                    <p class="text-sm">{{ $message->message }}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 text-right">
                                    {{ $message->created_at->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    @else
                        <!-- AI Message -->
                        <div class="flex justify-start">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="bg-gray-100 text-gray-900 rounded-lg px-4 py-2">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-robot text-blue-600 mr-2"></i>
                                        <span class="text-xs font-medium text-gray-600">AI Assistant</span>
                                        @if($message->meta && isset($message->meta['confidence']))
                                            <div class="ml-auto flex items-center">
                                                <div class="w-12 bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-blue-600 h-1.5 rounded-full" 
                                                         style="width: {{ $message->meta['confidence'] }}%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500 ml-1">{{ $message->meta['confidence'] }}%</span>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-sm">{{ $message->message }}</p>
                                    @if($message->meta && isset($message->meta['intent']))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $message->meta['intent'] }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $message->created_at->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-robot text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Start a conversation</h3>
                        <p class="text-gray-500">Ask me anything! I'm here to help you.</p>
                    </div>
                @endforelse
            </div>

            <!-- Message Input -->
            <div class="border-t border-gray-200 p-4">
                <form id="message-form" class="flex space-x-4">
                    @csrf
                    <div class="flex-1">
                        <textarea id="message-input" 
                                  name="message"
                                  rows="1"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                  placeholder="Type your message..."
                                  required></textarea>
                    </div>
                    <button type="submit" 
                            id="send-button"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
class ClientAIChatManager {
    constructor() {
        this.messagesContainer = document.getElementById('messages-container');
        this.messageForm = document.getElementById('message-form');
        this.messageInput = document.getElementById('message-input');
        this.sendButton = document.getElementById('send-button');
        this.lastMessageId = {{ $messages->first()?->id ?? 0 }};
        
        this.init();
    }

    init() {
        this.messageForm.addEventListener('submit', (e) => this.handleSubmit(e));
        this.messageInput.addEventListener('input', () => this.autoResize());
        this.messageInput.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        // Start polling for new messages
        this.startPolling();
        
        // Scroll to bottom
        this.scrollToBottom();
    }

    handleSubmit(e) {
        e.preventDefault();
        
        const message = this.messageInput.value.trim();
        if (!message) return;
        
        this.sendMessage(message);
    }

    handleKeydown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.messageForm.dispatchEvent(new Event('submit'));
        }
    }

    autoResize() {
        this.messageInput.style.height = 'auto';
        this.messageInput.style.height = Math.min(this.messageInput.scrollHeight, 120) + 'px';
    }

    async sendMessage(message) {
        // Disable form
        this.sendButton.disabled = true;
        this.messageInput.disabled = true;
        
        // Add user message to UI immediately
        this.addMessage({
            message: message,
            sender_type: 'client',
            created_at: new Date().toISOString()
        });
        
        // Clear input
        this.messageInput.value = '';
        this.autoResize();
        
        try {
            const response = await fetch('{{ route("client.chat.ai.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Failed to send message');
            }
            
            // Update stats
            this.updateStats();
            
        } catch (error) {
            console.error('Error sending message:', error);
            this.showError('Failed to send message. Please try again.');
        } finally {
            // Re-enable form
            this.sendButton.disabled = false;
            this.messageInput.disabled = false;
            this.messageInput.focus();
        }
    }

    addMessage(message) {
        const messageElement = this.createMessageElement(message);
        this.messagesContainer.appendChild(messageElement);
        this.scrollToBottom();
        
        // Update last message ID
        if (message.id) {
            this.lastMessageId = Math.max(this.lastMessageId, message.id);
        }
    }

    createMessageElement(message) {
        const div = document.createElement('div');
        const isUser = message.sender_type === 'client';
        const time = new Date(message.created_at).toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        });
        
        if (isUser) {
            div.className = 'flex justify-end';
            div.innerHTML = `
                <div class="max-w-xs lg:max-w-md">
                    <div class="bg-blue-600 text-white rounded-lg px-4 py-2">
                        <p class="text-sm">${this.escapeHtml(message.message)}</p>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 text-right">${time}</p>
                </div>
            `;
        } else {
            div.className = 'flex justify-start';
            const confidence = message.meta?.confidence || 0;
            const intent = message.meta?.intent || '';
            
            div.innerHTML = `
                <div class="max-w-xs lg:max-w-md">
                    <div class="bg-gray-100 text-gray-900 rounded-lg px-4 py-2">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-robot text-blue-600 mr-2"></i>
                            <span class="text-xs font-medium text-gray-600">AI Assistant</span>
                            ${confidence > 0 ? `
                                <div class="ml-auto flex items-center">
                                    <div class="w-12 bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: ${confidence}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">${confidence}%</span>
                                </div>
                            ` : ''}
                        </div>
                        <p class="text-sm">${this.escapeHtml(message.message)}</p>
                        ${intent ? `
                            <div class="mt-2 flex flex-wrap gap-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    ${this.escapeHtml(intent)}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">${time}</p>
                </div>
            `;
        }
        
        return div;
    }

    async fetchNewMessages() {
        try {
            const response = await fetch(`{{ route('client.chat.ai.messages') }}?since=${this.lastMessageId}`);
            const data = await response.json();
            
            if (data.success && data.messages.length > 0) {
                data.messages.forEach(message => {
                    this.addMessage(message);
                });
                
                this.updateStats();
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    }

    startPolling() {
        setInterval(() => {
            this.fetchNewMessages();
        }, 3000); // Poll every 3 seconds
    }

    updateStats() {
        // Update stats counters
        const totalElement = document.getElementById('total-messages');
        const todayElement = document.getElementById('today-messages');
        const userElement = document.getElementById('user-messages');
        const aiElement = document.getElementById('ai-messages');
        
        if (totalElement) totalElement.textContent = parseInt(totalElement.textContent) + 1;
        if (todayElement) todayElement.textContent = parseInt(todayElement.textContent) + 1;
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

// Initialize chat manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ClientAIChatManager();
});
</script>
@endsection