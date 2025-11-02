@php
    $isOutbound = $message->direction === 'outbound';
    $messageClass = $isOutbound ? 'ml-auto bg-blue-600 text-white' : 'mr-auto bg-white text-gray-900 border border-gray-200';
    $alignClass = $isOutbound ? 'justify-end' : 'justify-start';
@endphp

<div class="flex {{ $alignClass }}" data-time="{{ $message->created_at->toISOString() }}">
    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $messageClass }} shadow-sm">
        <!-- Conteúdo da mensagem baseado no tipo -->
        @switch($message->type)
            @case('text')
                <p class="text-sm whitespace-pre-wrap">{{ $message->message }}</p>
                @break
                
            @case('image')
                <div class="mb-2">
                    <img src="{{ $message->metadata['file_url'] ?? '#' }}" 
                         alt="Imagem" 
                         class="max-w-full h-auto rounded-lg">
                </div>
                @if($message->message)
                    <p class="text-sm">{{ $message->message }}</p>
                @endif
                @break
                
            @case('document')
                <div class="flex items-center space-x-2 mb-2 p-2 {{ $isOutbound ? 'bg-blue-700' : 'bg-gray-100' }} rounded">
                    <svg class="w-5 h-5 {{ $isOutbound ? 'text-blue-200' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $message->metadata['file_name'] ?? 'Documento' }}</p>
                        <p class="text-xs {{ $isOutbound ? 'text-blue-200' : 'text-gray-500' }}">
                            {{ $message->metadata['file_size'] ?? 'Tamanho desconhecido' }}
                        </p>
                    </div>
                    <a href="{{ $message->metadata['file_url'] ?? '#' }}" 
                       target="_blank" 
                       class="p-1 {{ $isOutbound ? 'text-blue-200 hover:text-white' : 'text-gray-600 hover:text-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </a>
                </div>
                @if($message->message)
                    <p class="text-sm">{{ $message->message }}</p>
                @endif
                @break
                
            @case('system')
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 {{ $isOutbound ? 'text-blue-200' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm italic">{{ $message->message }}</p>
                </div>
                @break
                
            @default
                <p class="text-sm">{{ $message->message }}</p>
        @endswitch
        
        <!-- Informações da mensagem -->
        <div class="flex items-center justify-between mt-2">
            <p class="text-xs {{ $isOutbound ? 'text-blue-100' : 'text-gray-500' }}">
                {{ $message->created_at->format('H:i') }}
            </p>
            
            @if($isOutbound)
                <!-- Status da mensagem para mensagens enviadas -->
                <div class="flex items-center space-x-1">
                    @switch($message->status)
                        @case('delivered')
                            <svg class="w-3 h-3 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            @break
                            
                        @case('read')
                            <svg class="w-3 h-3 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <svg class="w-3 h-3 text-blue-200 -ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            @break
                            
                        @case('failed')
                            <svg class="w-3 h-3 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            @break
                            
                        @default
                            <svg class="w-3 h-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                    @endswitch
                </div>
            @endif
        </div>
        
        <!-- Informações de IA (se disponível) -->
        @if($message->ai_confidence && $message->intent)
            <div class="mt-2 pt-2 border-t {{ $isOutbound ? 'border-blue-500' : 'border-gray-200' }}">
                <div class="flex items-center space-x-2 text-xs {{ $isOutbound ? 'text-blue-100' : 'text-gray-500' }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span>IA: {{ ucfirst($message->intent) }} ({{ round($message->ai_confidence * 100) }}%)</span>
                </div>
            </div>
        @endif
    </div>
</div>