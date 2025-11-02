@extends('layouts.tenant')

@section('title', 'Nova Fatura')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Nova Fatura</h1>
            <p class="text-gray-600">Crie uma nova fatura para seu cliente</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('tenant.invoices.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Voltar à Lista
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

    <!-- Form -->
    <form method="POST" action="{{ route('tenant.invoices.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Cliente <span class="text-red-500">*</span>
                    </label>
                    <select id="client_id" 
                            name="client_id" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('client_id') border-red-500 @enderror">
                        <option value="">Selecione um cliente</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} - {{ $client->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Número da Fatura <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="invoice_number" 
                           name="invoice_number" 
                           value="{{ old('invoice_number', $nextInvoiceNumber) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('invoice_number') border-red-500 @enderror">
                    @error('invoice_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="issue_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Data de Emissão <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="issue_date" 
                           name="issue_date" 
                           value="{{ old('issue_date', date('Y-m-d')) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('issue_date') border-red-500 @enderror">
                    @error('issue_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Data de Vencimento <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="due_date" 
                           name="due_date" 
                           value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('due_date') border-red-500 @enderror">
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                        Moeda <span class="text-red-500">*</span>
                    </label>
                    <select id="currency" 
                            name="currency" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('currency') border-red-500 @enderror">
                        <option value="BRL" {{ old('currency', 'BRL') == 'BRL' ? 'selected' : '' }}>BRL - Real Brasileiro</option>
                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - Dólar Americano</option>
                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                    </select>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Items Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Itens da Fatura</h2>
                <button type="button" 
                        id="add-item" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Adicionar Item
                </button>
            </div>

            <div id="items-container">
                @if(old('items'))
                    @foreach(old('items') as $index => $item)
                        <div class="item-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                                <input type="text" 
                                       name="items[{{ $index }}][description]" 
                                       value="{{ $item['description'] ?? '' }}"
                                       placeholder="Descrição do item"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                                <input type="number" 
                                       name="items[{{ $index }}][quantity]" 
                                       value="{{ $item['quantity'] ?? '' }}"
                                       step="0.01" 
                                       min="0.01"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 item-quantity">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Preço Unitário</label>
                                <input type="number" 
                                       name="items[{{ $index }}][unit_price]" 
                                       value="{{ $item['unit_price'] ?? '' }}"
                                       step="0.01" 
                                       min="0"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 item-price">
                            </div>
                            <div class="flex items-end">
                                <button type="button" 
                                        class="remove-item bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md font-medium transition-colors duration-200">
                                    Remover
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="item-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 border border-gray-200 rounded-md">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <input type="text" 
                                   name="items[0][description]" 
                                   placeholder="Descrição do item"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                            <input type="number" 
                                   name="items[0][quantity]" 
                                   value="1"
                                   step="0.01" 
                                   min="0.01"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 item-quantity">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preço Unitário</label>
                            <input type="number" 
                                   name="items[0][unit_price]" 
                                   step="0.01" 
                                   min="0"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 item-price">
                        </div>
                        <div class="flex items-end">
                            <button type="button" 
                                    class="remove-item bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md font-medium transition-colors duration-200">
                                Remover
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Totals Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Totais</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tax_rate" class="block text-sm font-medium text-gray-700 mb-2">Taxa de Imposto (%)</label>
                    <input type="number" 
                           id="tax_rate" 
                           name="tax_rate" 
                           value="{{ old('tax_rate', 0) }}"
                           step="0.01" 
                           min="0" 
                           max="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Desconto</label>
                    <input type="number" 
                           id="discount_amount" 
                           name="discount_amount" 
                           value="{{ old('discount_amount', 0) }}"
                           step="0.01" 
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-md">
                <div class="flex justify-between items-center text-sm">
                    <span>Subtotal:</span>
                    <span id="subtotal-display">R$ 0,00</span>
                </div>
                <div class="flex justify-between items-center text-sm mt-2">
                    <span>Impostos:</span>
                    <span id="tax-display">R$ 0,00</span>
                </div>
                <div class="flex justify-between items-center text-sm mt-2">
                    <span>Desconto:</span>
                    <span id="discount-display">R$ 0,00</span>
                </div>
                <div class="flex justify-between items-center text-lg font-semibold mt-4 pt-4 border-t border-gray-300">
                    <span>Total:</span>
                    <span id="total-display">R$ 0,00</span>
                </div>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Observações e Termos</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="4"
                              placeholder="Observações adicionais sobre a fatura..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label for="terms" class="block text-sm font-medium text-gray-700 mb-2">Termos e Condições</label>
                    <textarea id="terms" 
                              name="terms" 
                              rows="4"
                              placeholder="Termos e condições de pagamento..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('terms') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <a href="{{ route('tenant.invoices.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-medium transition-colors duration-200 text-center">
                Cancelar
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                Criar Fatura
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ old('items') ? count(old('items')) : 1 }};

    // Add item functionality
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const newItem = document.createElement('div');
        newItem.className = 'item-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 border border-gray-200 rounded-md';
        newItem.innerHTML = `
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <input type="text" 
                       name="items[${itemIndex}][description]" 
                       placeholder="Descrição do item"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                <input type="number" 
                       name="items[${itemIndex}][quantity]" 
                       value="1"
                       step="0.01" 
                       min="0.01"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 item-quantity">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Preço Unitário</label>
                <input type="number" 
                       name="items[${itemIndex}][unit_price]" 
                       step="0.01" 
                       min="0"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 item-price">
            </div>
            <div class="flex items-end">
                <button type="button" 
                        class="remove-item bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md font-medium transition-colors duration-200">
                    Remover
                </button>
            </div>
        `;
        container.appendChild(newItem);
        itemIndex++;
        updateTotals();
    });

    // Remove item functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const itemRows = document.querySelectorAll('.item-row');
            if (itemRows.length > 1) {
                e.target.closest('.item-row').remove();
                updateTotals();
            } else {
                alert('Deve haver pelo menos um item na fatura.');
            }
        }
    });

    // Update totals when values change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity') || 
            e.target.classList.contains('item-price') || 
            e.target.id === 'tax_rate' || 
            e.target.id === 'discount_amount') {
            updateTotals();
        }
    });

    function updateTotals() {
        let subtotal = 0;
        
        // Calculate subtotal
        document.querySelectorAll('.item-row').forEach(function(row) {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            subtotal += quantity * price;
        });

        // Calculate tax
        const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
        const taxAmount = subtotal * (taxRate / 100);

        // Calculate discount
        const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;

        // Calculate total
        const total = subtotal + taxAmount - discountAmount;

        // Update display
        document.getElementById('subtotal-display').textContent = formatCurrency(subtotal);
        document.getElementById('tax-display').textContent = formatCurrency(taxAmount);
        document.getElementById('discount-display').textContent = formatCurrency(discountAmount);
        document.getElementById('total-display').textContent = formatCurrency(total);
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(amount);
    }

    // Initial calculation
    updateTotals();
});
</script>
@endsection