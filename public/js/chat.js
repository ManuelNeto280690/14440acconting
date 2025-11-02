class ChatManager {
    constructor() {
        this.currentClientId = null;
        this.lastMessageId = null;
        this.pollingInterval = null;
        this.isTyping = false;
        this.typingTimeout = null;
        this.unreadCounts = {};
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.startPolling();
        this.loadUnreadCounts();
        
        // Auto-resize textarea
        const textarea = document.getElementById('messageInput');
        if (textarea) {
            textarea.addEventListener('input', this.autoResize.bind(this));
        }
    }

    bindEvents() {
        // Send message on Enter (Shift+Enter for new line)
        document.addEventListener('keydown', (e) => {
            if (e.target.id === 'messageInput' && e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // File upload
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', this.handleFileUpload.bind(this));
        }

        // Conversation selection
        document.addEventListener('click', (e) => {
            if (e.target.closest('.conversation-item')) {
                const clientId = e.target.closest('.conversation-item').dataset.clientId;
                this.selectConversation(clientId);
            }
        });

        // Mark as read when scrolling to bottom
        const messagesContainer = document.getElementById('messagesContainer');
        if (messagesContainer) {
            messagesContainer.addEventListener('scroll', this.handleScroll.bind(this));
        }
    }

    selectConversation(clientId) {
        if (this.currentClientId === clientId) return;

        this.currentClientId = clientId;
        this.lastMessageId = null;

        // Update UI
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        
        const selectedItem = document.querySelector(`[data-client-id="${clientId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('active');
        }

        // Load messages
        this.loadMessages();
        
        // Mark as read
        this.markAsRead(clientId);

        // Update URL
        const url = new URL(window.location);
        url.pathname = `/tenant/chat/conversation/${clientId}`;
        window.history.pushState({}, '', url);
    }

    async loadMessages() {
        if (!this.currentClientId) return;

        try {
            const response = await fetch(`/tenant/chat/messages/${this.currentClientId}`);
            const data = await response.json();

            if (data.success) {
                this.renderMessages(data.messages);
                this.scrollToBottom();
                
                // Update last message ID
                if (data.messages.length > 0) {
                    this.lastMessageId = data.messages[data.messages.length - 1].id;
                }
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    async pollNewMessages() {
        if (!this.currentClientId || !this.lastMessageId) return;

        try {
            const response = await fetch(`/tenant/chat/messages/${this.currentClientId}?after=${this.lastMessageId}`);
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                this.appendMessages(data.messages);
                this.scrollToBottom();
                
                // Update last message ID
                this.lastMessageId = data.messages[data.messages.length - 1].id;
                
                // Mark as read if at bottom
                if (this.isAtBottom()) {
                    this.markAsRead(this.currentClientId);
                }
            }
        } catch (error) {
            console.error('Error polling messages:', error);
        }
    }

    renderMessages(messages) {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        container.innerHTML = '';
        messages.forEach(message => {
            this.appendMessage(message);
        });
    }

    appendMessages(messages) {
        messages.forEach(message => {
            this.appendMessage(message);
        });
    }

    appendMessage(message) {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        const messageElement = this.createMessageElement(message);
        container.appendChild(messageElement);
    }

    createMessageElement(message) {
        const div = document.createElement('div');
        div.className = `flex mb-4 ${message.direction === 'outbound' ? 'justify-end' : 'justify-start'}`;
        
        let bubbleClass = `message-bubble p-3 ${message.direction}`;
        if (message.type === 'system') {
            bubbleClass += ' system';
        }

        let statusIcon = '';
        if (message.direction === 'outbound') {
            switch (message.status) {
                case 'delivered':
                    statusIcon = '<i class="fas fa-check text-xs"></i>';
                    break;
                case 'read':
                    statusIcon = '<i class="fas fa-check-double text-xs"></i>';
                    break;
                case 'failed':
                    statusIcon = '<i class="fas fa-exclamation-triangle text-xs"></i>';
                    break;
            }
        }

        let attachments = '';
        if (message.attachments && message.attachments.length > 0) {
            attachments = message.attachments.map(att => `
                <div class="attachment-preview mt-2">
                    <i class="fas fa-file file-icon"></i>
                    <div class="file-info">
                        <div class="file-name">${att.name}</div>
                        <div class="file-size">${this.formatFileSize(att.size)}</div>
                    </div>
                    <a href="${att.url}" target="_blank" class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            `).join('');
        }

        let aiInfo = '';
        if (message.ai_confidence) {
            aiInfo = `<div class="ai-confidence">IA: ${Math.round(message.ai_confidence * 100)}%</div>`;
        }

        div.innerHTML = `
            <div class="${bubbleClass}">
                <div class="message-content">${this.formatMessage(message.message)}</div>
                ${attachments}
                ${aiInfo}
                <div class="message-time">
                    ${this.formatTime(message.created_at)}
                    ${statusIcon}
                </div>
            </div>
        `;

        return div;
    }

    formatMessage(message) {
        // Basic formatting: links, line breaks
        return message
            .replace(/\n/g, '<br>')
            .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="underline">$1</a>');
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) { // Less than 1 minute
            return 'Agora';
        } else if (diff < 3600000) { // Less than 1 hour
            return `${Math.floor(diff / 60000)}m`;
        } else if (date.toDateString() === now.toDateString()) { // Today
            return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        } else {
            return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    async sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        
        if (!message || !this.currentClientId) return;

        const sendButton = document.getElementById('sendButton');
        sendButton.disabled = true;
        input.disabled = true;

        try {
            const response = await fetch('/tenant/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    client_id: this.currentClientId,
                    message: message
                })
            });

            const data = await response.json();

            if (data.success) {
                input.value = '';
                this.autoResize({ target: input });
                
                // Message will appear via polling
                setTimeout(() => this.pollNewMessages(), 500);
            } else {
                alert(data.message || 'Erro ao enviar mensagem');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Erro ao enviar mensagem');
        } finally {
            sendButton.disabled = false;
            input.disabled = false;
            input.focus();
        }
    }

    async handleFileUpload(event) {
        const files = Array.from(event.target.files);
        if (!files.length || !this.currentClientId) return;

        const formData = new FormData();
        files.forEach(file => formData.append('files[]', file));
        formData.append('client_id', this.currentClientId);

        try {
            const response = await fetch('/tenant/chat/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Message will appear via polling
                setTimeout(() => this.pollNewMessages(), 500);
            } else {
                alert(data.message || 'Erro ao enviar arquivo');
            }
        } catch (error) {
            console.error('Error uploading file:', error);
            alert('Erro ao enviar arquivo');
        } finally {
            event.target.value = '';
        }
    }

    async markAsRead(clientId) {
        try {
            await fetch(`/tenant/chat/mark-read/${clientId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            // Update unread count in sidebar
            this.updateUnreadCount(clientId, 0);
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    async loadUnreadCounts() {
        try {
            const response = await fetch('/tenant/chat/unread-counts');
            const data = await response.json();

            if (data.success) {
                this.unreadCounts = data.counts;
                this.updateUnreadBadges();
            }
        } catch (error) {
            console.error('Error loading unread counts:', error);
        }
    }

    updateUnreadCount(clientId, count) {
        this.unreadCounts[clientId] = count;
        this.updateUnreadBadges();
    }

    updateUnreadBadges() {
        Object.entries(this.unreadCounts).forEach(([clientId, count]) => {
            const badge = document.querySelector(`[data-client-id="${clientId}"] .unread-badge`);
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        });
    }

    autoResize(event) {
        const textarea = event.target;
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    scrollToBottom() {
        const container = document.getElementById('messagesContainer');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    isAtBottom() {
        const container = document.getElementById('messagesContainer');
        if (!container) return false;
        
        return container.scrollTop + container.clientHeight >= container.scrollHeight - 10;
    }

    handleScroll() {
        if (this.isAtBottom() && this.currentClientId) {
            this.markAsRead(this.currentClientId);
        }
    }

    startPolling() {
        this.pollingInterval = setInterval(() => {
            this.pollNewMessages();
            this.loadUnreadCounts();
        }, 3000); // Poll every 3 seconds
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    destroy() {
        this.stopPolling();
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('chatContainer')) {
        window.chatManager = new ChatManager();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.chatManager) {
        window.chatManager.destroy();
    }
});