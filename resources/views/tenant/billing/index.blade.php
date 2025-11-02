@extends('layouts.tenant')

@section('title', 'Faturamento')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Faturamento</h1>
            <p class="text-gray-600">Gerencie sua assinatura e informações de pagamento</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('tenant.billing.plans') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Ver Planos
            </a>
            <a href="{{ route('tenant.billing.history') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Histórico
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

    @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
            {{ session('info') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Current Plan -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Plano Atual</h3>
                
                @if($subscription)
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900">{{ $subscription->plan->name }}</h4>
                            <p class="text-gray-600">{{ $subscription->plan->description }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900">
                                R$ {{ number_format($subscription->plan->price, 2, ',', '.') }}
                            </div>
                            <div class="text-sm text-gray-600">
                                /{{ $subscription->plan->billing_cycle === 'monthly' ? 'mês' : 'ano' }}
                            </div>
                        </div>
                    </div>

                    <!-- Plan Status -->
                    <div class="flex items-center gap-4 mb-4">
                        @php
                            $statusClasses = [
                                'active' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'suspended' => 'bg-yellow-100 text-yellow-800',
                                'expired' => 'bg-gray-100 text-gray-800',
                            ];
                            $statusLabels = [
                                'active' => 'Ativo',
                                'cancelled' => 'Cancelado',
                                'suspended' => 'Suspenso',
                                'expired' => 'Expirado',
                            ];
                        @endphp
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusClasses[$subscription->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$subscription->status] ?? $subscription->status }}
                        </span>
                        
                        @if($subscription->trial_ends_at && $subscription->trial_ends_at->isFuture())
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                Período de Teste
                            </span>
                        @endif
                    </div>

                    <!-- Subscription Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Início da Assinatura:</span>
                            <span class="font-medium ml-2">{{ $subscription->created_at->format('d/m/Y') }}</span>
                        </div>
                        @if($subscription->trial_ends_at)
                            <div>
                                <span class="text-gray-600">Fim do Período de Teste:</span>
                                <span class="font-medium ml-2">{{ $subscription->trial_ends_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                        @if($subscription->ends_at)
                            <div>
                                <span class="text-gray-600">{{ $subscription->status === 'cancelled' ? 'Cancelamento em:' : 'Próxima Cobrança:' }}</span>
                                <span class="font-medium ml-2">{{ $subscription->ends_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                        @if($subscription->cancelled_at)
                            <div>
                                <span class="text-gray-600">Cancelado em:</span>
                                <span class="font-medium ml-2">{{ $subscription->cancelled_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Plan Actions -->
                    <div class="flex flex-wrap gap-3 mt-6">
                        @if($subscription->status === 'active')
                            <a href="{{ route('tenant.billing.plans') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                                Alterar Plano
                            </a>
                            
                            <form method="POST" action="{{ route('tenant.billing.cancel') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200"
                                        onclick="return confirm('Tem certeza que deseja cancelar sua assinatura?')">
                                    Cancelar Assinatura
                                </button>
                            </form>
                        @elseif($subscription->status === 'cancelled')
                            <form method="POST" action="{{ route('tenant.billing.resume') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                                    Reativar Assinatura
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma Assinatura Ativa</h3>
                        <p class="text-gray-600 mb-4">Você não possui uma assinatura ativa no momento.</p>
                        <a href="{{ route('tenant.billing.plans') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200">
                            Escolher Plano
                        </a>
                    </div>
                @endif
            </div>

            <!-- Usage Statistics -->
            @if($subscription && $usageData)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Uso do Período Atual</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Documents -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2">
                                {{ number_format($usageData['documents_processed'] ?? 0) }}
                            </div>
                            <div class="text-sm text-gray-600">Documentos Processados</div>
                            @if(isset($subscription->plan->features['max_documents']))
                                <div class="mt-2">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        @php
                                            $percentage = min(100, (($usageData['documents_processed'] ?? 0) / $subscription->plan->features['max_documents']) * 100);
                                        @endphp
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $usageData['documents_processed'] ?? 0 }} / {{ number_format($subscription->plan->features['max_documents']) }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Storage -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600 mb-2">
                                {{ number_format(($usageData['storage_used'] ?? 0) / 1024 / 1024, 1) }} MB
                            </div>
                            <div class="text-sm text-gray-600">Armazenamento Usado</div>
                            @if(isset($subscription->plan->features['max_storage']))
                                <div class="mt-2">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        @php
                                            $storageGB = ($usageData['storage_used'] ?? 0) / 1024 / 1024 / 1024;
                                            $maxStorageGB = $subscription->plan->features['max_storage'];
                                            $percentage = min(100, ($storageGB / $maxStorageGB) * 100);
                                        @endphp
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ number_format($storageGB, 1) }} GB / {{ $maxStorageGB }} GB
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- API Calls -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600 mb-2">
                                {{ number_format($usageData['api_calls'] ?? 0) }}
                            </div>
                            <div class="text-sm text-gray-600">Chamadas de API</div>
                            @if(isset($subscription->plan->features['max_api_calls']))
                                <div class="mt-2">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        @php
                                            $percentage = min(100, (($usageData['api_calls'] ?? 0) / $subscription->plan->features['max_api_calls']) * 100);
                                        @endphp
                                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $usageData['api_calls'] ?? 0 }} / {{ number_format($subscription->plan->features['max_api_calls']) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Payment Method -->
            @if($subscription)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Método de Pagamento</h3>
                        <button type="button" 
                                onclick="openUpdatePaymentModal()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                            Atualizar
                        </button>
                    </div>
                    
                    @if($paymentMethod)
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-8 bg-gray-100 rounded flex items-center justify-center">
                                @if($paymentMethod['type'] === 'card')
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium">
                                    @if($paymentMethod['type'] === 'card')
                                        **** **** **** {{ $paymentMethod['last4'] }}
                                    @else
                                        {{ $paymentMethod['description'] }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">
                                    @if($paymentMethod['type'] === 'card')
                                        {{ $paymentMethod['brand'] }} • Expira {{ $paymentMethod['exp_month'] }}/{{ $paymentMethod['exp_year'] }}
                                    @else
                                        {{ $paymentMethod['type'] }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="text-gray-400 mb-2">
                                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600">Nenhum método de pagamento configurado</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('tenant.billing.plans') }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Ver Planos
                    </a>

                    <a href="{{ route('tenant.billing.history') }}" 
                       class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Histórico de Faturas
                    </a>

                    <a href="{{ route('tenant.billing.usage') }}" 
                       class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Relatório de Uso
                    </a>

                    @if($subscription)
                        <a href="{{ route('tenant.billing.settings') }}" 
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Configurações
                        </a>
                    @endif
                </div>
            </div>

            <!-- Billing Summary -->
            @if($subscription)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumo</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Plano:</span>
                            <span class="font-medium">{{ $subscription->plan->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium">{{ $statusLabels[$subscription->status] ?? $subscription->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Valor Mensal:</span>
                            <span class="font-medium">R$ {{ number_format($subscription->plan->price, 2, ',', '.') }}</span>
                        </div>
                        @if($subscription->ends_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ $subscription->status === 'cancelled' ? 'Expira em:' : 'Próxima Cobrança:' }}</span>
                                <span class="font-medium">{{ $subscription->ends_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Update Payment Method Modal -->
@if($subscription)
<div id="updatePaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Atualizar Método de Pagamento</h3>
            
            <form method="POST" action="{{ route('tenant.billing.update-payment-method') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Pagamento <span class="text-red-500">*</span>
                        </label>
                        <select id="payment_type" 
                                name="payment_type" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione o tipo</option>
                            <option value="card">Cartão de Crédito</option>
                            <option value="pix">PIX</option>
                            <option value="bank_transfer">Transferência Bancária</option>
                        </select>
                    </div>

                    <div id="card_fields" class="hidden space-y-4">
                        <div>
                            <label for="card_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Número do Cartão
                            </label>
                            <input type="text" 
                                   id="card_number" 
                                   name="card_number" 
                                   placeholder="1234 5678 9012 3456"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="exp_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Validade
                                </label>
                                <input type="text" 
                                       id="exp_date" 
                                       name="exp_date" 
                                       placeholder="MM/AA"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="cvv" class="block text-sm font-medium text-gray-700 mb-2">
                                    CVV
                                </label>
                                <input type="text" 
                                       id="cvv" 
                                       name="cvv" 
                                       placeholder="123"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="card_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome no Cartão
                            </label>
                            <input type="text" 
                                   id="card_name" 
                                   name="card_name" 
                                   placeholder="Nome como impresso no cartão"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" 
                            onclick="closeUpdatePaymentModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                        Atualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
function openUpdatePaymentModal() {
    document.getElementById('updatePaymentModal').classList.remove('hidden');
}

function closeUpdatePaymentModal() {
    document.getElementById('updatePaymentModal').classList.add('hidden');
}

// Show/hide card fields based on payment type
document.getElementById('payment_type')?.addEventListener('change', function() {
    const cardFields = document.getElementById('card_fields');
    if (this.value === 'card') {
        cardFields.classList.remove('hidden');
    } else {
        cardFields.classList.add('hidden');
    }
});

// Close modal when clicking outside
document.getElementById('updatePaymentModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeUpdatePaymentModal();
    }
});
</script>
@endsection