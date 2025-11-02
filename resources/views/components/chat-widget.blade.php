@props(['client' => null, 'height' => 'h-96'])

<div class="bg-white rounded-lg shadow-lg {{ $height }} flex flex-col">
    <!-- Header -->
    <div class="bg-blue-600 text-white p-4 rounded-t-lg flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-comments mr-2"></i>
            <h3 class="font-semibold">
                @if($client)
                    Chat com {{ $client->name }}
                @else
                    Chat
                @endif
            </h3>
        </div>
        @if($client)
            <div class="flex items-center text-sm">
                <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                Online
            </div>
        @endif
    </div>

    <!-- Messages Area -->
    <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chat-messages-{{ $client?->id ?? 'general' }}">
        @if($client && $client->chatMessages->count() > 0)
            @foreach($client->chatMessages->take(10) as $message)
                <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $message->direction === 'outbound' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                        <p class="text-sm">{{ $message->message }}</p>
                        <p class="text-xs mt-1 {{ $message->direction === 'outbound' ? 'text-blue-100' : 'text-gray-500' }}">
                            {{ $message->created_at->format('H:i') }}
                        </p>
                    </div>
                </div>
            @endforeach
        @else
            <div class="flex items-center justify-center h-full text-gray-500">
                <div class="text-center">
                    <i class="fas fa-comments text-4xl mb-2"></i>
                    <p>Nenhuma mensagem ainda</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Input Area -->
    <div class="border-t p-4">
        <form class="flex space-x-2" onsubmit="sendQuickMessage(event, '{{ $client?->id }}')">
            <input type="text" 
                   name="message"
                   placeholder="Digite sua mensagem..."
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<script>
function sendQuickMessage(event, clientId) {
    event.preventDefault();
    
    if (!clientId) {
        alert('Cliente não especificado');
        return;
    }
    
    const form = event.target;
    const messageInput = form.querySelector('input[name="message"]');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    // Adicionar mensagem temporariamente na interface
    const messagesContainer = document.getElementById(`chat-messages-${clientId}`);
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex justify-end';
    messageDiv.innerHTML = `
        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-blue-500 text-white">
            <p class="text-sm">${message}</p>
            <p class="text-xs mt-1 text-blue-100">Enviando...</p>
        </div>
    `;
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Limpar input
    messageInput.value = '';
    
    // Enviar mensagem
    fetch(`{{ route('tenant.chat.send') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            client_id: clientId,
            message: message,
            type: 'text'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar timestamp da mensagem
            const timeElement = messageDiv.querySelector('.text-blue-100');
            timeElement.textContent = new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
        } else {
            // Remover mensagem em caso de erro
            messageDiv.remove();
            alert('Erro ao enviar mensagem: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        messageDiv.remove();
        alert('Erro ao enviar mensagem');
    });
}
</script>