@extends('layouts.tenant')

@section('title', 'Fatura #' . $invoice->invoice_number)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Fatura #{{ $invoice->invoice_number }}</h1>
            <p class="text-gray-600">{{ $invoice->client->name }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 mt-4 sm:mt-0">
            @if($invoice->status === 'draft')
                <a href="{{ route('tenant.invoices.edit', $invoice) }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
            @endif

            {{-- Botão de envio removido conforme solicitado --}}

            @if(!in_array($invoice->status, ['paid', 'cancelled']))
                <button type="button" 
                        onclick="openMarkAsPaidModal()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Marcar como Paga
                </button>
            @endif

            <form method="POST" action="{{ route('tenant.invoices.download', $invoice) }}" class="inline">
                @csrf
                <button type="submit" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </button>
            </form>

            <a href="{{ route('tenant.invoices.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Voltar à Lista
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
        <!-- Main Invoice Content -->
        <div class="lg:col-span-2">
            <!-- Invoice Header -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">FATURA</h2>
                        <p class="text-gray-600">#{{ $invoice->invoice_number }}</p>
                    </div>
                    <div class="text-right">
                        @php
                            $statusClasses = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'sent' => 'bg-blue-100 text-blue-800',
                                'paid' => 'bg-green-100 text-green-800',
                                'overdue' => 'bg-red-100 text-red-800',
                                'cancelled' => 'bg-gray-100 text-gray-800',
                            ];
                            $statusLabels = [
                                'draft' => 'Rascunho',
                                'pending' => 'Pendente',
                                'sent' => 'Enviada',
                                'paid' => 'Paga',
                                'overdue' => 'Vencida',
                                'cancelled' => 'Cancelada',
                            ];
                        @endphp
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusClasses[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">PARA:</h3>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium">{{ $invoice->client->name }}</p>
                            @if($invoice->client->email)
                                <p>{{ $invoice->client->email }}</p>
                            @endif
                            @if($invoice->client->phone)
                                <p>{{ $invoice->client->phone }}</p>
                            @endif
                            @if($invoice->client->address)
                                <p>{{ $invoice->client->address }}</p>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">DETALHES:</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <div class="flex justify-between">
                                <span>Data de Emissão:</span>
                                <span>{{ $invoice->issue_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Vencimento:</span>
                                <span>{{ $invoice->due_date->format('d/m/Y') }}</span>
                            </div>
                            @if($daysUntilDue !== null)
                                <div class="flex justify-between">
                                    <span>Dias até vencimento:</span>
                                    <span class="{{ $daysUntilDue < 0 ? 'text-red-600 font-semibold' : ($daysUntilDue <= 7 ? 'text-yellow-600 font-semibold' : '') }}">
                                        {{ $daysUntilDue < 0 ? 'Vencida há ' . abs($daysUntilDue) . ' dias' : $daysUntilDue . ' dias' }}
                                    </span>
                                </div>
                            @endif
                            @if($invoice->paid_date)
                                <div class="flex justify-between">
                                    <span>Data do Pagamento:</span>
                                    <span>{{ $invoice->paid_date->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                            @if($invoice->payment_method)
                                <div class="flex justify-between">
                                    <span>Método de Pagamento:</span>
                                    <span>{{ $invoice->payment_method }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Itens</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Preço Unit.</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item['description'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($item['quantity'], 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ $invoice->currency }} {{ number_format($item['unit_price'], 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">
                                        {{ $invoice->currency }} {{ number_format($item['quantity'] * $item['unit_price'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-xs space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span>{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2, ',', '.') }}</span>
                        </div>
                        @if($invoice->tax_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span>Impostos:</span>
                                <span>{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($invoice->discount_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span>Desconto:</span>
                                <span>-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-semibold pt-2 border-t border-gray-200">
                            <span>Total:</span>
                            <span>{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes and Terms -->
            @if($invoice->notes || $invoice->terms)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($invoice->notes)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-2">Observações</h3>
                                <p class="text-sm text-gray-600">{{ $invoice->notes }}</p>
                            </div>
                        @endif

                        @if($invoice->terms)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-2">Termos e Condições</h3>
                                <p class="text-sm text-gray-600">{{ $invoice->terms }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
                
                <div class="space-y-3">
                    @if($invoice->status === 'draft')
                        <a href="{{ route('tenant.invoices.edit', $invoice) }}" 
                           class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar Fatura
                        </a>
                    @endif

                    {{-- Botão de envio removido conforme solicitado --}}

                    @if(!in_array($invoice->status, ['paid', 'cancelled']))
                        <button type="button" 
                                onclick="openMarkAsPaidModal()"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Marcar como Paga
                        </button>
                    @endif

                    <form method="POST" action="{{ route('tenant.invoices.download', $invoice) }}">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download PDF
                        </button>
                    </form>

                    @if($invoice->status === 'draft')
                        <form method="POST" action="{{ route('tenant.invoices.destroy', $invoice) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2"
                                    onclick="return confirm('Tem certeza que deseja excluir esta fatura?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Excluir Fatura
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumo</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-medium">{{ $statusLabels[$invoice->status] ?? $invoice->status }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Valor Total:</span>
                        <span class="font-medium">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Criada em:</span>
                        <span class="font-medium">{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($invoice->updated_at != $invoice->created_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Atualizada em:</span>
                            <span class="font-medium">{{ $invoice->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div id="markAsPaidModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Marcar como Paga</h3>
            
            <form method="POST" action="{{ route('tenant.invoices.mark-as-paid', $invoice) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                            Método de Pagamento <span class="text-red-500">*</span>
                        </label>
                        <select id="payment_method" 
                                name="payment_method" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione o método</option>
                            <option value="cash">Dinheiro</option>
                            <option value="check">Cheque</option>
                            <option value="credit_card">Cartão de Crédito</option>
                            <option value="bank_transfer">Transferência Bancária</option>
                            <option value="pix">PIX</option>
                        </select>
                    </div>

                    <div>
                        <label for="payment_reference" class="block text-sm font-medium text-gray-700 mb-2">
                            Referência do Pagamento
                        </label>
                        <input type="text" 
                               id="payment_reference" 
                               name="payment_reference" 
                               placeholder="Número da transação, cheque, etc."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="paid_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Data do Pagamento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="paid_date" 
                               name="paid_date" 
                               value="{{ date('Y-m-d') }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" 
                            onclick="closeMarkAsPaidModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                        Marcar como Paga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openMarkAsPaidModal() {
    document.getElementById('markAsPaidModal').classList.remove('hidden');
}

function closeMarkAsPaidModal() {
    document.getElementById('markAsPaidModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('markAsPaidModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMarkAsPaidModal();
    }
});
</script>
@endsection