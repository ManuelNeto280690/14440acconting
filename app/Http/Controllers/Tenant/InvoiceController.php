<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:tenant');
        $this->middleware('tenant');
    }

    /**
     * API: Check if invoice number exists for a specific client
     */
    public function checkInvoiceNumber(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|uuid|exists:clients,id',
            'invoice_number' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentTenantId = $this->getCurrentTenantId();
        
        Log::info('Checking invoice number for client', [
            'tenant_id' => $currentTenantId,
            'client_id' => $request->client_id,
            'invoice_number' => $request->invoice_number,
        ]);

        // Verificar se já existe uma fatura com este número para este cliente
        $existingInvoice = Invoice::where('client_id', $request->client_id)
            ->where('invoice_number', $request->invoice_number)
            ->first();

        if ($existingInvoice) {
            Log::warning('Invoice number already exists for client', [
                'tenant_id' => $currentTenantId,
                'client_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'existing_invoice_id' => $existingInvoice->id,
            ]);

            return response()->json([
                'status' => 'duplicate',
                'message' => 'DUPLICATE_INVOICE_DETECTED',
                'invoice_number' => $request->invoice_number,
                'client_id' => $request->client_id,
                'duplicate' => true
            ], 200);
        }

        Log::info('Invoice number is available for client', [
            'tenant_id' => $currentTenantId,
            'client_id' => $request->client_id,
            'invoice_number' => $request->invoice_number,
        ]);

        return response()->json([
            'status' => 'available',
            'message' => 'AVAILABLE',
            'invoice_number' => $request->invoice_number,
            'client_id' => $request->client_id,
            'duplicate' => false
        ]);
    }

    /**
     * API: Create invoice with simplified payload (for N8N integration).
     */


    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): View
    {
        $query = Invoice::with(['client']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('issue_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('issue_date', '<=', $request->get('date_to'));
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $invoices = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => Invoice::count(),
            'draft' => Invoice::where('status', Invoice::STATUS_DRAFT)->count(),
            'pending' => Invoice::where('status', Invoice::STATUS_PENDING)->count(),
            'paid' => Invoice::where('status', Invoice::STATUS_PAID)->count(),
            'overdue' => Invoice::where('status', Invoice::STATUS_OVERDUE)->count(),
            'total_amount' => Invoice::where('status', '!=', Invoice::STATUS_CANCELLED)->sum('total_amount'),
            'paid_amount' => Invoice::where('status', Invoice::STATUS_PAID)->sum('total_amount'),
        ];

        $clients = Client::orderBy('name')->get(['id', 'name']);
        $statuses = [
            Invoice::STATUS_DRAFT => 'Rascunho',
            Invoice::STATUS_PENDING => 'Pendente',
            Invoice::STATUS_SENT => 'Enviada',
            Invoice::STATUS_PAID => 'Paga',
            Invoice::STATUS_OVERDUE => 'Vencida',
            Invoice::STATUS_CANCELLED => 'Cancelada',
        ];

        return view('tenant.invoices.index', compact('invoices', 'stats', 'clients', 'statuses'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'email', 'phone']);
        $nextInvoiceNumber = $this->generateInvoiceNumber();

        return view('tenant.invoices.create', compact('clients', 'nextInvoiceNumber'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|string|max:50',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'currency' => 'required|string|max:3',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = ($request->tax_rate ?? 0) * $subtotal / 100;
            $discountAmount = $request->discount_amount ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Create invoice
            $invoice = Invoice::create([
                'client_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'currency' => $request->currency,
                'subtotal' => $subtotal,
                'tax_rate' => $request->tax_rate ?? 0,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => Invoice::STATUS_DRAFT,
                'notes' => $request->notes,
                'terms' => $request->terms,
                'items' => $request->items,
            ]);

            DB::commit();

            return redirect()->route('tenant.invoices.show', $invoice)
                ->with('success', 'Fatura criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create invoice', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao criar fatura. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load('client');
        
        // Calculate days until due date
        $daysUntilDue = null;
        if ($invoice->due_date) {
            $daysUntilDue = now()->diffInDays($invoice->due_date, false);
        }
        
        return view('tenant.invoices.show', compact('invoice', 'daysUntilDue'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'email', 'phone']);
        return view('tenant.invoices.edit', compact('invoice', 'clients'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|string|max:50',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'currency' => 'required|string|max:3',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = ($request->tax_rate ?? 0) * $subtotal / 100;
            $discountAmount = $request->discount_amount ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Update invoice
            $invoice->update([
                'client_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'currency' => $request->currency,
                'subtotal' => $subtotal,
                'tax_rate' => $request->tax_rate ?? 0,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'terms' => $request->terms,
                'items' => $request->items,
            ]);

            DB::commit();

            return redirect()->route('tenant.invoices.show', $invoice)
                ->with('success', 'Fatura atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao atualizar fatura. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        try {
            $invoice->delete();
            
            return redirect()->route('tenant.invoices.index')
                ->with('success', 'Fatura excluída com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Failed to delete invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao excluir fatura. Tente novamente.');
        }
    }

    /**
     * Generate next invoice number.
     */
   
    /**
     * Send invoice to client.
     */
    public function send(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            return redirect()->back()
                ->with('error', 'Fatura já foi paga.');
        }

        try {
            $invoice->update(['status' => Invoice::STATUS_SENT]);

            // Here you would implement email sending logic
            // For now, just log the action
            Log::info('Invoice sent to client', [
                'tenant_id' => tenant('id'),
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('success', 'Fatura enviada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to send invoice', [
                'tenant_id' => tenant('id'),
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao enviar fatura: ' . $e->getMessage());
        }
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Request $request, Invoice $invoice): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'paid_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $invoice->update([
                'status' => Invoice::STATUS_PAID,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'paid_date' => $request->paid_date,
            ]);

            Log::info('Invoice marked as paid', [
                'tenant_id' => tenant('id'),
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'payment_method' => $request->payment_method,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('success', 'Fatura marcada como paga!');

        } catch (\Exception $e) {
            Log::error('Failed to mark invoice as paid', [
                'tenant_id' => tenant('id'),
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao marcar fatura como paga: ' . $e->getMessage());
        }
    }

    /**
     * Download invoice as PDF.
     */
    public function download(Invoice $invoice): RedirectResponse
    {
        try {
            // Here you would implement PDF generation logic
            // For now, just log the action
            Log::info('Invoice download requested', [
                'tenant_id' => tenant('id'),
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('info', 'Funcionalidade de download em desenvolvimento.');

        } catch (\Exception $e) {
            Log::error('Failed to download invoice', [
                'tenant_id' => tenant('id'),
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao baixar fatura: ' . $e->getMessage());
        }
    }

    /**
     * Get invoice statistics for API.
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => Invoice::count(),
                'by_status' => [
                    'draft' => Invoice::where('status', Invoice::STATUS_DRAFT)->count(),
                    'pending' => Invoice::where('status', Invoice::STATUS_PENDING)->count(),
                    'sent' => Invoice::where('status', Invoice::STATUS_SENT)->count(),
                    'paid' => Invoice::where('status', Invoice::STATUS_PAID)->count(),
                    'overdue' => Invoice::where('status', Invoice::STATUS_OVERDUE)->count(),
                    'cancelled' => Invoice::where('status', Invoice::STATUS_CANCELLED)->count(),
                ],
                'amounts' => [
                    'total' => Invoice::where('status', '!=', Invoice::STATUS_CANCELLED)->sum('total_amount'),
                    'paid' => Invoice::where('status', Invoice::STATUS_PAID)->sum('total_amount'),
                    'pending' => Invoice::whereIn('status', [
                        Invoice::STATUS_PENDING,
                        Invoice::STATUS_SENT,
                        Invoice::STATUS_OVERDUE
                    ])->sum('total_amount'),
                ],
                'recent' => Invoice::with('client:id,name')
                    ->latest()
                    ->take(5)
                    ->get(['id', 'invoice_number', 'client_id', 'total_amount', 'status', 'created_at']),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Failed to get invoice stats', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json(['error' => 'Failed to get statistics'], 500);
        }
    }

    /**
     * Generate next invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}{$month}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('INV-%s%s%04d', $year, $month, $nextNumber);
    }

    /**
     * Get status label in Portuguese.
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            Invoice::STATUS_DRAFT => 'Rascunho',
            Invoice::STATUS_PENDING => 'Pendente',
            Invoice::STATUS_SENT => 'Enviada',
            Invoice::STATUS_PAID => 'Paga',
            Invoice::STATUS_OVERDUE => 'Vencida',
            Invoice::STATUS_CANCELLED => 'Cancelada',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get current tenant ID helper method.
     */
    private function getCurrentTenantId(): ?string
    {
        return tenant('id');
    }

     //API
     public function apiCreateSimple(Request $request)
    {
      //dd( $request->all());

        // Log dos dados recebidos do n8n
        Log::info('N8N Invoice API - Request received', [
            'tenant_id' => tenant() ? tenant('id') : 'unknown',
            'headers' => [
                'X-Tenant-ID' => $request->header('X-Tenant-ID'),
                'X-API-Key' => $request->header('X-API-Key') ? 'present' : 'missing',
                'Content-Type' => $request->header('Content-Type'),
                'User-Agent' => $request->header('User-Agent'),
            ],
            'payload' => $request->all(),
            'document_id' => $request->document_id,
            'client_id' => $request->client_id,
            'items_count' => is_array($request->items) ? count($request->items) : 0,
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'timestamp' => now()->toISOString(),
        ]);

        // Validar API Key
        $apiKey = $request->header('X-API-Key');
        if (!$apiKey || $apiKey !== config('app.api_key')) {
            Log::warning('N8N Invoice API - Invalid API key', [
                'provided_key' => $apiKey ? 'present' : 'missing',
                'expected_key' => config('app.api_key') ? 'configured' : 'not_configured',
                'tenant_id' => tenant() ? tenant('id') : 'unknown',
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        // Validar Tenant ID
        $tenantId = $request->header('X-Tenant-ID');
        $currentTenantId = tenant() ? tenant('id') : null;
        if (!$tenantId || $tenantId !== $currentTenantId) {
            Log::warning('N8N Invoice API - Invalid tenant ID', [
                'provided_tenant_id' => $tenantId,
                'current_tenant_id' => $currentTenantId,
                'tenant_initialized' => tenant() ? 'yes' : 'no',
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid tenant ID'
            ], 403);
        }

     /*   $validator = Validator::make($request->all(), [
            'client_id' => 'required|uuid|exists:clients,id',
            'document_id' => 'nullable|uuid|exists:documents,id',
            'due_days' => 'nullable|integer|min:1|max:365',
            //'currency' => 'nullable|string|size:3',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.total' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            Log::error('N8N Invoice API - Validation failed', [
                'tenant_id' => $currentTenantId,
                'validation_errors' => $validator->errors()->toArray(),
                'payload' => $request->all(),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Log::info('N8N Invoice API - Validation passed', [
            'tenant_id' => $currentTenantId,
            'validated_data' => $validator->validated(),
            'ip' => $request->ip(),
        ]);*/

        try {
            DB::beginTransaction();

            // Usar o número da fatura fornecido pelo n8n
            $invoiceNumber = $request->invoice_number;

            // Calcular datas
            $issueDate = now()->format('Y-m-d');
            $dueDays = $request->get('due_days', 30);
            $dueDate = now()->addDays($dueDays)->format('Y-m-d');

            // Calcular valores
            $amount = $request->total_amount;
            $taxRate = $request->get('tax_rate', 0) / 100;
            $taxAmount = $amount * $taxRate;
            $totalAmount = $amount + $taxAmount;

            // Criar item único baseado na descrição
           // Processar itens que vêm do n8n
                $subtotals = 0;
                $items = [];

                // Processar itens que vêm como array JSON do n8n
                $requestItems = $request->items;

                // Se items vem como string JSON, decodificar
                if (is_string($requestItems)) {
                    $requestItems = json_decode($requestItems, true);
                }

                foreach ($requestItems as $item) {
                    $quantity = floatval($item['quantity']);
                    $unitPrice = floatval($item['unit_price']);
                    $itemTotal = $quantity * $unitPrice;
                    $subtotals += $itemTotal;
                    
                    $items[] = [
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $itemTotal,
                    ];
                }

            $invoice = Invoice::create([
                'client_id' => $request->client_id,
                'invoice_number' => $request->invoice_number,
                'document_id' => $request->document_id,
                'status' => Invoice::STATUS_PENDING,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'subtotal' => $amount,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'currency' => 'USD',
                'notes' => $request->notes,
                'terms' => $request->terms,
                'items' => $items,
                'metadata' => [
                    'created_via' => 'api_simple',
                    'api_request_ip' => $request->ip(),
                    'api_user_agent' => $request->userAgent(),
                    'reference' => $request->reference,
                    'created_at' => now()->toISOString(),
                ]
            ]);

            DB::commit();

            Log::info('Simple invoice created via API', [
                'tenant_id' => $currentTenantId,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'amount' => $amount,
                'total_amount' => $invoice->total_amount,
                'reference' => $request->reference,
                'api_ip' => $request->ip(),
            ]);

            // Carregar relacionamentos para resposta
            $invoice->load(['client']);

            return response()->json([
                'success' => true,
                'message' => 'Simple invoice created successfully',
                'data' => [
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'status' => $invoice->status,
                        'client' => [
                            'id' => $invoice->client->id,
                            'name' => $invoice->client->name,
                            'email' => $invoice->client->email,
                        ],
                        'description' => $request->description,
                        'amount' => $amount,
                        'tax_amount' => $invoice->tax_amount,
                        'total_amount' => $invoice->total_amount,
                        'currency' => $invoice->currency,
                        'issue_date' => $invoice->issue_date->format('Y-m-d'),
                        'due_date' => $invoice->due_date->format('Y-m-d'),
                        'due_days' => $dueDays,
                        'reference' => $request->reference,
                        'created_at' => $invoice->created_at->toISOString(),
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create simple invoice via API', [
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'api_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }
}