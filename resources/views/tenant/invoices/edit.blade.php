@extends('layouts.tenant')

@section('title', 'Editar Fatura #' . $invoice->invoice_number)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Editar Fatura #{{ $invoice->invoice_number }}</h1>
            <p class="text-gray-600">{{ $invoice->client->name }}</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('tenant.invoices.show', $invoice) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Ver Detalhes
            </a>
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

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Invoice Form -->
    <form method="POST" action="{{ route('tenant.invoices.update', $invoice) }}" id="invoiceForm">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Cliente <span class="text-red-500">*</span>
                            </label>
                            <select id="client_id" 
                                    name="client_id" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um cliente</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id', $invoice->client_id) == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Número da Fatura <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="invoice_number" 
                                   name="invoice_number" 
                                   value="{{ old('invoice_number', $invoice->invoice_number) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="issue_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Emissão <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="issue_date" 
                                   name="issue_date" 
                                   value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Vencimento <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="due_date" 
                                   name="due_date" 
                                   value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                Moeda <span class="text-red-500">*</span>
                            </label>
                            <select id="currency" 
                                    name="currency" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="BRL" {{ old('currency', $invoice->currency) == 'BRL' ? 'selected' : '' }}>Real (R$)</option>
                                <option value="USD" {{ old('currency', $invoice->currency) == 'USD' ? 'selected' : '' }}>Dólar ($)</option>
                                <option value="EUR" {{ old('currency', $invoice->currency) == 'EUR' ? 'selected' : '' }}>Euro (€)</option>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" 
                                    name="status" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="draft" {{ old('status', $invoice->status) == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                <option value="pending" {{ old('status', $invoice->status) == 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="sent" {{ old('status', $invoice->status) == 'sent' ? 'selected' : '' }}>Enviada</option>
                                <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>Paga</option>
                                <option value="overdue" {{ old('status', $invoice->status) == 'overdue' ? 'selected' : '' }}>Vencida</option>
                                <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Itens da Fatura</h3>
                        <button type="button" 
                                onclick="addItem()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Adicionar Item
                        </button>
                    </div>

                    <div id="items-container">
                        @foreach(old('items', $invoice->items) as $index => $item)
                            <div class="item-row border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="text-sm font-medium text-gray-900">Item {{ $index + 1 }}</h4>
                                    <button type="button" 
                                            onclick="removeItem(this)" 
                                            class="text-red-600 hover:text-red-800 transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <input type="text" 
                                               name="items[{{ $index }}][description]" 
                                               value="{{ $item['description'] ?? '' }}"
                                               required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                                        <input type="number" 
                                               name="items[{{ $index }}][quantity]" 
                                               value="{{ $item['quantity'] ?? 1 }}"
                                               min="0.01" 
                                               step="0.01" 
                                               required
                                               onchange="calculateItemTotal(this)"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Preço Unitário</label>
                                        <input type="number" 
                                               name="items[{{ $index }}][unit_price]" 
                                               value="{{ $item['unit_price'] ?? 0 }}"
                                               min="0" 
                                               step="0.01" 
                                               required
                                               onchange="calculateItemTotal(this)"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div class="mt-3 text-right">
                                    <span class="text-sm text-gray-600">Total do Item: </span>
                                    <span class="font-medium item-total">R$ {{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(empty(old('items', $invoice->items)))
                        <div class="text-center py-8 text-gray-500">
                            <p>Nenhum item adicionado ainda.</p>
                            <p class="text-sm">Clique em "Adicionar Item" para começar.</p>
                        </div>
                    @endif
                </div>

                <!-- Notes and Terms -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Observações e Termos</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Observações
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="4"
                                      placeholder="Observações adicionais sobre a fatura..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>

                        <div>
                            <label for="terms" class="block text-sm font-medium text-gray-700 mb-2">
                                Termos e Condições
                            </label>
                            <textarea id="terms" 
                                      name="terms" 
                                      rows="4"
                                      placeholder="Termos e condições de pagamento..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('terms', $invoice->terms) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Totals -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Totais</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="subtotal" class="block text-sm font-medium text-gray-700 mb-2">
                                Subtotal
                            </label>
                            <input type="number" 
                                   id="subtotal" 
                                   name="subtotal" 
                                   value="{{ old('subtotal', $invoice->subtotal) }}"
                                   min="0" 
                                   step="0.01" 
                                   readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        </div>

                        <div>
                            <label for="tax_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Impostos
                            </label>
                            <input type="number" 
                                   id="tax_amount" 
                                   name="tax_amount" 
                                   value="{{ old('tax_amount', $invoice->tax_amount) }}"
                                   min="0" 
                                   step="0.01" 
                                   onchange="calculateTotals()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Desconto
                            </label>
                            <input type="number" 
                                   id="discount_amount" 
                                   name="discount_amount" 
                                   value="{{ old('discount_amount', $invoice->discount_amount) }}"
                                   min="0" 
                                   step="0.01" 
                                   onchange="calculateTotals()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Total Final
                            </label>
                            <input type="number" 
                                   id="total_amount" 
                                   name="total_amount" 
                                   value="{{ old('total_amount', $invoice->total_amount) }}"
                                   min="0" 
                                   step="0.01" 
                                   readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-lg font-semibold">
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
                    
                    <div class="space-y-3">
                        <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Atualizar Fatura
                        </button>

                        <a href="{{ route('tenant.invoices.show', $invoice) }}" 
                           class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Ver Detalhes
                        </a>

                        <a href="{{ route('tenant.invoices.index') }}" 
                           class="w-full bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let itemIndex = {{ count(old('items', $invoice->items)) }};

function addItem() {
    const container = document.getElementById('items-container');
    const itemHtml = `
        <div class="item-row border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-start mb-3">
                <h4 class="text-sm font-medium text-gray-900">Item ${itemIndex + 1}</h4>
                <button type="button" 
                        onclick="removeItem(this)" 
                        class="text-red-600 hover:text-red-800 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <input type="text" 
                           name="items[${itemIndex}][description]" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                    <input type="number" 
                           name="items[${itemIndex}][quantity]" 
                           value="1"
                           min="0.01" 
                           step="0.01" 
                           required
                           onchange="calculateItemTotal(this)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preço Unitário</label>
                    <input type="number" 
                           name="items[${itemIndex}][unit_price]" 
                           value="0"
                           min="0" 
                           step="0.01" 
                           required
                           onchange="calculateItemTotal(this)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-3 text-right">
                <span class="text-sm text-gray-600">Total do Item: </span>
                <span class="font-medium item-total">R$ 0,00</span>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    itemIndex++;
    calculateTotals();
}

function removeItem(button) {
    if (document.querySelectorAll('.item-row').length > 1) {
        button.closest('.item-row').remove();
        calculateTotals();
    } else {
        alert('Deve haver pelo menos um item na fatura.');
    }
}

function calculateItemTotal(input) {
    const row = input.closest('.item-row');
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
    const total = quantity * unitPrice;
    
    row.querySelector('.item-total').textContent = `R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
        const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
        subtotal += quantity * unitPrice;
    });
    
    const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const totalAmount = subtotal + taxAmount - discountAmount;
    
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('total_amount').value = Math.max(0, totalAmount).toFixed(2);
}

// Calculate totals on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});
</script>
@endsection