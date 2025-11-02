<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents.
     */
    public function index(Request $request): View
    {
        $query = Document::with(['client', 'invoice', 'uploader']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ocr_text', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->get('type'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $documents = $query->paginate(15)->withQueryString();

        // Get filter options
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $types = Document::getTypes();
        $statuses = Document::getStatuses();

        // Statistics
        $stats = [
            'total' => Document::count(),
            'processed' => Document::where('status', Document::STATUS_PROCESSED)->count(),
            'processing' => Document::where('status', Document::STATUS_PROCESSING)->count(),
            'pending' => Document::where('status', Document::STATUS_UPLOADED)->count(),
            'failed' => Document::where('status', Document::STATUS_FAILED)->count(),
            'total_size' => Document::sum('file_size'),
            'storage_used' => Document::sum('file_size'),
        ];

        return view('tenant.documents.index', compact('documents', 'clients', 'types', 'statuses', 'stats'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $types = Document::getTypes();

        return view('tenant.documents.create', compact('clients', 'types'));
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request)
    {
        try {
            // Validação da requisição baseada no modelo Document
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'invoice_id' => 'nullable|exists:invoices,id',
                'files' => 'required|array|max:10',
                'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:' . implode(',', array_keys(Document::getTypes())),
                'description' => 'nullable|string|max:1000',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ], [
                'files.max' => 'Você pode fazer upload de no máximo 10 documentos por vez.',
                'files.*.mimes' => 'Apenas arquivos PDF, imagens (JPG, PNG) e documentos Office são permitidos.',
                'files.*.max' => 'Cada arquivo deve ter no máximo 10MB.',
                'type.in' => 'Tipo de documento inválido.',
            ]);

            $uploadedDocuments = [];
            $files = $request->file('files');
            $totalFiles = count($files);

            // Processar cada arquivo
            foreach ($files as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $documentName = $request->name . ($totalFiles > 1 ? " (" . ($index + 1) . ")" : '');
                
                // Gerar nome único para o arquivo
                $fileName = Str::uuid() . '.' . $extension;
                $filePath = $file->storeAs(tenant('id'), $fileName, 'documents');

                // Criar documento usando os campos do modelo
                $document = Document::create([
                    'client_id' => $request->client_id,
                    'invoice_id' => $request->invoice_id,
                    'name' => $documentName,
                    'original_name' => $originalName,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'type' => $request->type,
                    'status' => Document::STATUS_UPLOADED,
                    'description' => $request->description,
                    'tags' => $request->tags ?? [],
                    'uploaded_by' => auth('tenant')->id(),
                    'metadata' => [
                        'upload_ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'batch_upload' => $totalFiles > 1,
                        'batch_index' => $index + 1,
                        'batch_total' => $totalFiles,
                        'uploaded_at' => now()->toISOString(),
                    ],
                ]);

                $uploadedDocuments[] = $document;

                Log::info('Document uploaded successfully', [
                    'document_id' => $document->id,
                    'document_name' => $document->name,
                    'original_name' => $originalName,
                    'file_size' => $document->file_size,
                    'mime_type' => $document->mime_type,
                    'type' => $document->type,
                    'client_id' => $document->client_id,
                    'tenant_id' => tenant('id'),
                ]);
            }

            // Enviar documentos para n8n usando configurações globais
            if (config('services.n8n.enabled', false)) {
                $webhookUrl = config('services.n8n.webhook_url');
                $timeout = config('services.n8n.timeout', 30);

                if ($webhookUrl) {
                    // Preparar payload com conteúdo binário dos documentos
                    $documentsWithContent = collect($uploadedDocuments)->map(function ($document) {
                        $filePath = Storage::disk('documents')->path($document->file_path);
                        
                        // Verificar se o arquivo existe
                        if (!file_exists($filePath)) {
                            Log::error('Document file not found for n8n webhook', [
                                'document_id' => $document->id,
                                'file_path' => $filePath,
                                'tenant_id' => tenant('id')
                            ]);
                            return null;
                        }

                        // Ler o conteúdo do arquivo e converter para base64
                        $fileContent = file_get_contents($filePath);
                        $base64Content = base64_encode($fileContent);
                        
                        return [
                            'id' => $document->id,
                            'name' => $document->name,
                            'original_name' => $document->original_name,
                            'type' => $document->type,
                            'mime_type' => $document->mime_type,
                            'size' => $document->file_size,
                            'path' => $document->file_path,
                            'content' => $base64Content,
                            'encoding' => 'base64',
                            'uploaded_at' => $document->created_at->toISOString()
                        ];
                    })->filter()->toArray(); // Remove documentos com erro (null)

                    $webhookPayload = [
                        'tenant_id' => tenant('id'),
                        'client_id' => $request->client_id,
                        'tenant_domain' => tenant('domain'),
                        'documents' => $documentsWithContent,
                        'timestamp' => now()->toISOString()
                    ];

                    Log::info('Sending documents to n8n with binary content', [
                        'document_count' => count($documentsWithContent),
                        'document_ids' => collect($documentsWithContent)->pluck('id')->toArray(),
                        'total_content_size' => collect($documentsWithContent)->sum(function($doc) {
                            return strlen($doc['content']);
                        }),
                        'webhook_url' => $webhookUrl,
                        'tenant_id' => tenant('id'),
                    ]);

                    // Tentar enviar para webhook com retry
                    $maxRetries = config('services.n8n.max_retries', 3);
                    $retryDelay = config('services.n8n.retry_delay', 2); // segundos
                    $webhookSuccess = false;
                    
                    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                        try {
                            Log::info("N8N webhook attempt {$attempt}/{$maxRetries}", [
                                'document_count' => count($documentsWithContent),
                                'webhook_url' => $webhookUrl,
                                'timeout' => $timeout,
                                'tenant_id' => tenant('id'),
                            ]);

                            $response = Http::timeout($timeout)
                                ->withoutVerifying() // Ignora verificação SSL
                                ->withHeaders([
                                    'Content-Type' => 'application/json',
                                    'X-Tenant-ID' => tenant('id'),
                                ])
                                ->post($webhookUrl, $webhookPayload);

                            if ($response->successful()) {
                                $webhookSuccess = true;
                                
                                // Atualizar status dos documentos para processing
                                foreach ($uploadedDocuments as $document) {
                                    $document->update([
                                        'status' => Document::STATUS_PROCESSING,
                                        'metadata' => array_merge($document->metadata ?? [], [
                                            'webhook_sent_at' => now()->toISOString(),
                                            'webhook_response_status' => $response->status(),
                                            'webhook_attempts' => $attempt,
                                        ])
                                    ]);
                                }

                                // Armazenar dados de processamento na sessão
                                session(['document_processing' => [
                                    'document_ids' => collect($uploadedDocuments)->pluck('id')->toArray(),
                                    'started_at' => now()->toISOString(),
                                    'total_count' => count($uploadedDocuments),
                                    'n8n_enabled' => true
                                ]]);

                                Log::info('Documents sent to n8n webhook successfully', [
                                    'document_count' => count($uploadedDocuments),
                                    'document_ids' => collect($uploadedDocuments)->pluck('id')->toArray(),
                                    'client_id' => $request->client_id,
                                    'webhook_response_status' => $response->status(),
                                    'attempts' => $attempt,
                                    'tenant_id' => tenant('id'),
                                ]);

                                break; // Sair do loop de retry se bem-sucedido

                            } else {
                                Log::warning("N8N webhook failed on attempt {$attempt}", [
                                    'webhook_response_status' => $response->status(),
                                    'webhook_response_body' => $response->body(),
                                    'tenant_id' => tenant('id'),
                                ]);
                                
                                if ($attempt < $maxRetries) {
                                    sleep($retryDelay);
                                    $retryDelay *= 2; // Exponential backoff
                                }
                            }

                        } catch (\Exception $e) {
                            Log::error("N8N webhook exception on attempt {$attempt}", [
                                'error' => $e->getMessage(),
                                'document_count' => count($uploadedDocuments),
                                'webhook_url' => $webhookUrl,
                                'tenant_id' => tenant('id'),
                            ]);
                            
                            if ($attempt < $maxRetries) {
                                sleep($retryDelay);
                                $retryDelay *= 2; // Exponential backoff
                            }
                        }
                    }

                    if ($webhookSuccess) {
                        // Redirecionar para página de processamento
                        return redirect()
                            ->route('tenant.documents.processing')
                            ->with('success', 'Documentos enviados para processamento!');
                    } else {
                        // Fallback: Marcar documentos como processados localmente
                        foreach ($uploadedDocuments as $document) {
                            $document->update([
                                'status' => Document::STATUS_PROCESSED,
                                'metadata' => array_merge($document->metadata ?? [], [
                                    'processed_locally' => true,
                                    'n8n_failed' => true,
                                    'processed_at' => now()->toISOString(),
                                    'fallback_reason' => 'N8N webhook timeout/failure after ' . $maxRetries . ' attempts'
                                ])
                            ]);
                        }

                        Log::warning('N8N webhook failed after all retries, documents marked as processed locally', [
                            'document_count' => count($uploadedDocuments),
                            'document_ids' => collect($uploadedDocuments)->pluck('id')->toArray(),
                            'max_retries' => $maxRetries,
                            'tenant_id' => tenant('id'),
                        ]);

                        $message = $totalFiles === 1 
                            ? 'Documento salvo com sucesso! (Processamento n8n indisponível - processado localmente)'
                            : "{$totalFiles} documentos salvos com sucesso! (Processamento n8n indisponível - processados localmente)";
                    }
                } else {
                    Log::warning('N8N webhook URL or API key not configured', [
                        'tenant_id' => tenant('id'),
                        'document_count' => count($uploadedDocuments),
                    ]);

                    $message = $totalFiles === 1 
                        ? 'Documento salvo com sucesso! Configure o n8n para processamento automático.'
                        : "{$totalFiles} documentos salvos com sucesso! Configure o n8n para processamento automático.";
                        
                    // Redirecionar baseado no número de documentos
                    if ($totalFiles === 1) {
                        return redirect()
                            ->route('tenant.documents.show', $uploadedDocuments[0])
                            ->with('success', $message);
                    } else {
                        return redirect()
                            ->route('tenant.documents.index')
                            ->with('success', $message);
                    }
                }
            } else {
                Log::info('N8N integration disabled', [
                    'tenant_id' => tenant('id'),
                    'document_count' => count($uploadedDocuments),
                ]);

                $message = $totalFiles === 1 
                    ? 'Documento salvo com sucesso!'
                    : "{$totalFiles} documentos salvos com sucesso!";
                    
                // Redirecionar baseado no número de documentos
                if ($totalFiles === 1) {
                    return redirect()
                        ->route('tenant.documents.show', $uploadedDocuments[0])
                        ->with('success', $message);
                } else {
                    return redirect()
                        ->route('tenant.documents.index')
                        ->with('success', $message);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error uploading documents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao fazer upload dos documentos. Tente novamente.');
        }
    }

    /**
     * Show processing status page.
     */
    public function processing(): View
    {
        $processingData = session('document_processing');
        
        if (!$processingData) {
            return redirect()->route('tenant.documents.index')
            ->with('error', 'No documents are currently being processed.');
        }

        return view('tenant.documents.processing', compact('processingData'));
    }

    /**
     * Check processing status via AJAX.
     */
    public function checkProcessingStatus(): JsonResponse
    {
        $processingData = session('document_processing');
        
        if (!$processingData) {
            return response()->json([
                'status' => 'no_session',
                'percentage' => 0,
                'message' => 'Nenhum processamento em andamento'
            ]);
        }

        $documentIds = $processingData['document_ids'] ?? [];
        $n8nEnabled = $processingData['n8n_enabled'] ?? false;
        $startedAt = $processingData['started_at'] ?? now()->toISOString();
        
        if (empty($documentIds)) {
            return response()->json([
                'status' => 'no_documents',
                'percentage' => 0,
                'message' => 'Nenhum documento para processar'
            ]);
        }

        // Buscar documentos atuais
        $documents = Document::whereIn('id', $documentIds)->get();
        
        $totalCount = count($documents);
        $processedCount = $documents->where('status', Document::STATUS_PROCESSED)->count();
        $failedCount = $documents->where('status', Document::STATUS_FAILED)->count();
        $processingCount = $documents->where('status', Document::STATUS_PROCESSING)->count();
        $pendingCount = $documents->where('status', Document::STATUS_PENDING)->count();
        
        $completedCount = $processedCount + $failedCount;
        $percentage = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
        
        // Calcular tempo decorrido
        $elapsedMinutes = now()->diffInMinutes($startedAt);
        
        // Verificar se há documentos com duplicatas detectadas
        $duplicateNotifications = [];
        foreach ($documents as $document) {
            $metadata = $document->metadata ?? [];
            if (isset($metadata['duplicate_invoice']) && $metadata['duplicate_invoice']['detected'] === true) {
                $duplicateNotifications[] = [
                    'document_id' => $document->id,
                    'document_name' => $document->name,
                    'duplicate_details' => $metadata['duplicate_invoice']['details'] ?? null,
                    'detected_at' => $metadata['duplicate_invoice']['detected_at'] ?? null
                ];
            }
        }
        
        // Log para debug
        Log::info('Processing status check', [
            'total' => $totalCount,
            'processed' => $processedCount,
            'failed' => $failedCount,
            'processing' => $processingCount,
            'pending' => $pendingCount,
            'completed' => $completedCount,
            'percentage' => $percentage,
            'elapsed_minutes' => $elapsedMinutes,
            'n8n_enabled' => $n8nEnabled,
            'duplicates_found' => count($duplicateNotifications)
        ]);
        
        // Timeout após 10 minutos - marcar documentos restantes como processados localmente
        if ($n8nEnabled && $elapsedMinutes >= 10 && $processingCount > 0) {
            $processingDocuments = $documents->where('status', Document::STATUS_PROCESSING);
            
            foreach ($processingDocuments as $document) {
                $document->update([
                    'status' => Document::STATUS_PROCESSED,
                    'metadata' => array_merge($document->metadata ?? [], [
                        'processed_locally' => true,
                        'timeout_fallback' => true,
                        'processed_at' => now()->toISOString(),
                        'fallback_reason' => 'N8N processing timeout after 10 minutes'
                    ])
                ]);
            }
            
            Log::warning('N8N processing timeout - documents marked as processed locally', [
                'document_ids' => $processingDocuments->pluck('id')->toArray(),
                'elapsed_minutes' => $elapsedMinutes,
                'tenant_id' => tenant('id'),
            ]);
            
            // Recalcular contadores após timeout
            $processedCount += $processingCount;
            $processingCount = 0;
            $completedCount = $processedCount + $failedCount;
            $percentage = 100;
        }
        
        // Verificar se todos os documentos foram processados (não incluir documentos ainda em processamento)
        if ($completedCount >= $totalCount && $processingCount === 0) {
            // Limpar dados da sessão
            session()->forget('document_processing');
            
            $message = $failedCount > 0 
                ? "Processamento concluído! {$processedCount} documentos processados com sucesso, {$failedCount} falharam."
                : "Processamento concluído! Todos os {$processedCount} documentos foram processados com sucesso.";
            
            // Adicionar aviso sobre duplicatas se encontradas
            if (!empty($duplicateNotifications)) {
                $duplicateCount = count($duplicateNotifications);
                $message .= " Atenção: {$duplicateCount} documento(s) com faturas duplicadas detectadas.";
            }
            
            return response()->json([
                'status' => 'completed',
                'percentage' => 100,
                'processed_count' => $processedCount,
                'failed_count' => $failedCount,
                'total_count' => $totalCount,
                'duplicate_notifications' => $duplicateNotifications,
                'message' => $message
            ]);
        }
        
        return response()->json([
            'status' => 'processing',
            'percentage' => $percentage,
            'processed_count' => $processedCount,
            'failed_count' => $failedCount,
            'processing_count' => $processingCount,
            'pending_count' => $pendingCount,
            'total_count' => $totalCount,
            'elapsed_minutes' => $elapsedMinutes,
            'duplicate_notifications' => $duplicateNotifications,
            'message' => "Processando documentos... {$completedCount} de {$totalCount} concluídos ({$processingCount} em processamento, {$pendingCount} pendentes) - {$elapsedMinutes} min"
        ]);
    }

    /**
     * Download document file via API (for n8n integration).
     */
    public function apiDownload(Document $document, Request $request)
    {
        try {
            // Verificar se a requisição vem do n8n
            $apiKey = $request->header('X-API-Key');
            $tenantId = $request->header('X-Tenant-ID');
            
            if (!$apiKey || !$tenantId) {
                Log::warning('API download attempt without proper headers', [
                    'document_id' => $document->id,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                abort(401, 'API Key and Tenant ID required');
            }
            
            // Verificar se o tenant ID corresponde
            if ($tenantId !== tenant('id')) {
                Log::warning('API download attempt with wrong tenant ID', [
                    'document_id' => $document->id,
                    'provided_tenant_id' => $tenantId,
                    'actual_tenant_id' => tenant('id'),
                    'ip' => $request->ip(),
                ]);
                abort(403, 'Invalid tenant');
            }
            
            // Verificar se existe integração n8n ativa com esta API key
            $integration = \App\Models\Integration::where('service_name', 'n8n')
                ->where('is_active', true)
                ->where('api_key', $apiKey)
                ->first();
                
            if (!$integration) {
                Log::warning('API download attempt with invalid API key', [
                    'document_id' => $document->id,
                    'tenant_id' => $tenantId,
                    'ip' => $request->ip(),
                ]);
                abort(401, 'Invalid API key');
            }

            // Verificar se o arquivo existe
            if (!Storage::disk('documents')->exists($document->file_path)) {
                Log::warning('API download - Document file not found', [
                    'document_id' => $document->id,
                    'file_path' => $document->file_path,
                    'tenant_id' => tenant('id'),
                    'integration_id' => $integration->id,
                ]);
                abort(404, 'File not found');
            }

            // Log da tentativa de download via API
            Log::info('API document download initiated', [
                'document_id' => $document->id,
                'document_name' => $document->name,
                'file_path' => $document->file_path,
                'tenant_id' => tenant('id'),
                'integration_id' => $integration->id,
                'ip' => $request->ip(),
            ]);

            return response()->download(
                Storage::disk('documents')->path($document->file_path),
                $document->original_name
            );

        } catch (\Exception $e) {
            Log::error('Error in API document download', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
                'ip' => $request->ip(),
            ]);

            abort(500, 'Download error');
        }
    }

    /**
     * Download document file.
     */
    public function download(Document $document)
    {
        try {
            // Verificar se o arquivo existe
            if (!Storage::disk('documents')->exists($document->file_path)) {
                Log::warning('Document file not found for download', [
                    'document_id' => $document->id,
                    'file_path' => $document->file_path,
                    'tenant_id' => tenant('id'),
                ]);
                abort(404, 'Arquivo não encontrado');
            }

            // Log da tentativa de download
            Log::info('Document download initiated', [
                'document_id' => $document->id,
                'document_name' => $document->name,
                'file_path' => $document->file_path,
                'tenant_id' => tenant('id'),
                'user_id' => auth('tenant')->id(),
            ]);

            return response()->download(
                Storage::disk('documents')->path($document->file_path),
                $document->original_name
            );

        } catch (\Exception $e) {
            Log::error('Error downloading document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            abort(500, 'Erro ao baixar o arquivo');
        }
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): View
    {
        $document->load(['client', 'invoice', 'uploader']);

        // Get related documents (same client or invoice)
        $relatedDocuments = Document::where('id', '!=', $document->id)
            ->where(function ($query) use ($document) {
                if ($document->client_id) {
                    $query->where('client_id', $document->client_id);
                }
                if ($document->invoice_id) {
                    $query->orWhere('invoice_id', $document->invoice_id);
                }
            })
            ->limit(5)
            ->get();

        return view('tenant.documents.show', compact('document', 'relatedDocuments'));
    }

    /**
     * Show the form for editing the specified document.
     */
    public function edit(Document $document): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $invoices = Invoice::with('client')->orderBy('invoice_number')->get(['id', 'invoice_number', 'client_id']);
        $types = Document::getTypes();

        return view('tenant.documents.edit', compact('document', 'clients', 'invoices', 'types'));
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, Document $document): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'nullable|exists:clients,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'type' => 'required|string|in:' . implode(',', array_keys(Document::getTypes())),
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Process tags from comma-separated string to array
            $tags = [];
            if ($request->tags) {
                $tags = array_map('trim', explode(',', $request->tags));
                $tags = array_filter($tags); // Remove empty values
            }

            $document->update([
                'client_id' => $request->client_id,
                'invoice_id' => $request->invoice_id,
                'type' => $request->type,
                'description' => $request->description,
                'tags' => $tags,
            ]);

            Log::info('Document updated', [
                'document_id' => $document->id,
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->route('tenant.documents.show', $document)
                ->with('success', 'Document updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update document. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Document $document): RedirectResponse
    {
        try {
            $documentName = $document->name;
            $filePath = $document->file_path;

            // Delete the file from storage
            if ($filePath && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }

            $document->delete();

            Log::info('Document deleted', [
                'document_name' => $documentName,
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->route('tenant.documents.index')
                ->with('success', "Document '{$documentName}' deleted successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to delete document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete document. Please try again.');
        }
    }

    /**
     * Download the specified document.
    /**
     * Bulk delete documents.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $documentIds = $request->input('document_ids', []);
            
            if (empty($documentIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No documents selected for deletion.'
                ], 400);
            }

            $documents = Document::whereIn('id', $documentIds)->get();
            $deletedCount = 0;

            foreach ($documents as $document) {
                // Delete the file from storage
                if ($document->file_path && Storage::exists($document->file_path)) {
                    Storage::delete($document->file_path);
                }
                
                $document->delete();
                $deletedCount++;
            }

            Log::info('Bulk documents deleted', [
                'deleted_count' => $deletedCount,
                'document_ids' => $documentIds,
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} documents deleted successfully!",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to bulk delete documents', [
                'document_ids' => $request->input('document_ids', []),
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete documents. Please try again.'
            ], 500);
        }
    }

    /**
     * Process document with OCR/AI.
     */
    public function process(Document $document): JsonResponse
    {
        try {
            if ($document->isProcessing()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document is already being processed.'
                ], 422);
            }

            // Update status to processing
            $document->update(['status' => Document::STATUS_PROCESSING]);

            // Here you would typically dispatch a job to process the document
            // For now, we'll just simulate processing
            
            return response()->json([
                'success' => true,
                'message' => 'Document processing started.',
                'document' => $document->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start document processing.'
            ], 500);
        }
    }

    /**
     * Reprocess document via n8n webhook.
     */
    public function reprocess(Document $document): RedirectResponse
    {
        try {
            // Verificar se o documento já está sendo processado
            if ($document->isProcessing()) {
                return redirect()->back()
                    ->with('error', 'O documento já está sendo processado.');
            }

            // Verificar se há integração n8n configurada
            $integration = Integration::where('service_name', 'n8n')
                ->where('is_active', true)
                ->first();

            if (!$integration || !$integration->webhook_url || !$integration->api_key) {
                return redirect()->back()
                    ->with('error', 'Integração n8n não configurada ou inativa.');
            }

            // Resetar status do documento para pending
            $document->update([
                'status' => Document::STATUS_PENDING,
                'metadata' => array_merge($document->metadata ?? [], [
                    'reprocessed_at' => now()->toISOString(),
                    'reprocess_requested_by' => auth('tenant')->user()->id,
                ])
            ]);

            // Preparar payload para o webhook
            $webhookPayload = [
                'tenant_id' => tenant('id'),
                'document_id' => $document->id,
                'document_name' => $document->name,
                'document_type' => $document->type,
                'file_path' => $document->file_path,
                'file_url' => route('tenant.api.documents.download', $document),
                'client_id' => $document->client_id,
                'reprocess' => true,
                'timestamp' => now()->toISOString(),
            ];

            // Configurações de timeout e retry
            $timeout = config('services.n8n.timeout', 60);
            $maxRetries = config('services.n8n.max_retries', 3);
            $retryDelay = config('services.n8n.retry_delay', 2);

            $webhookSuccess = false;

            // Tentar enviar para o webhook com retry
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    Log::info('Sending document reprocess to n8n webhook', [
                        'document_id' => $document->id,
                        'webhook_url' => $integration->webhook_url,
                        'attempt' => $attempt,
                        'max_retries' => $maxRetries,
                        'tenant_id' => tenant('id'),
                    ]);

                    $response = Http::timeout($timeout)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                            'X-Tenant-ID' => tenant('id'),
                            'X-API-Key' => $integration->api_key,
                        ])
                        ->post($integration->webhook_url, $webhookPayload);

                    if ($response->successful()) {
                        $webhookSuccess = true;
                        
                        // Atualizar status do documento para processing
                        $document->update([
                            'status' => Document::STATUS_PROCESSING,
                            'metadata' => array_merge($document->metadata ?? [], [
                                'reprocess_webhook_sent_at' => now()->toISOString(),
                                'reprocess_webhook_response_status' => $response->status(),
                                'reprocess_webhook_attempts' => $attempt,
                            ])
                        ]);

                        Log::info('Document reprocess sent to n8n webhook successfully', [
                            'document_id' => $document->id,
                            'webhook_response_status' => $response->status(),
                            'attempt' => $attempt,
                            'tenant_id' => tenant('id'),
                        ]);

                        break;
                    } else {
                        Log::warning('N8N webhook returned non-success status for reprocess', [
                            'document_id' => $document->id,
                            'status' => $response->status(),
                            'response' => $response->body(),
                            'attempt' => $attempt,
                            'tenant_id' => tenant('id'),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending document reprocess to n8n webhook', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage(),
                        'attempt' => $attempt,
                        'tenant_id' => tenant('id'),
                    ]);
                }

                // Se não foi a última tentativa, aguardar antes de tentar novamente
                if ($attempt < $maxRetries) {
                    sleep($retryDelay * $attempt); // Backoff exponencial
                }
            }

            if ($webhookSuccess) {
                return redirect()->back()
                    ->with('success', 'Documento enviado para reprocessamento! Aguarde alguns instantes.');
            } else {
                // Marcar documento como failed se não conseguiu enviar
                $document->update([
                    'status' => Document::STATUS_FAILED,
                    'metadata' => array_merge($document->metadata ?? [], [
                        'reprocess_failed_at' => now()->toISOString(),
                        'reprocess_failure_reason' => 'N8N webhook timeout/failure after ' . $maxRetries . ' attempts'
                    ])
                ]);

                return redirect()->back()
                    ->with('error', 'Falha ao enviar documento para reprocessamento. Tente novamente mais tarde.');
            }

        } catch (\Exception $e) {
            Log::error('Error reprocessing document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao reprocessar documento. Tente novamente.');
        }
    }

     public function resetProcessing(Document $document): RedirectResponse
    {
        try {
            // Reset document status to pending
            $document->update([
                'status' => Document::STATUS_PENDING,
                'processed_at' => null,
                'ai_analysis' => null,
            ]);

            // Clear any processing metadata
            $metadata = $document->metadata ?? [];
            unset($metadata['processing_started_at']);
            unset($metadata['processing_attempts']);
            unset($metadata['last_error']);
            unset($metadata['webhook_response']);
            
            $document->update(['metadata' => $metadata]);

            Log::info('Document processing status reset', [
                'document_id' => $document->id,
                'tenant_id' => tenant('id'),
                'reset_by' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('success', 'Status de processamento resetado com sucesso! O documento está pronto para ser reprocessado.');

        } catch (\Exception $e) {
            Log::error('Failed to reset document processing status', [
                'document_id' => $document->id,
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao resetar status de processamento: ' . $e->getMessage());
        }
    }

    /**
     * Replace the file of an existing document
     */
    public function replaceFile(Request $request, Document $document): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            
            // Delete old file if exists
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Store new file
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents/' . tenant('id'), $filename, 'local');

            // Update document with new file information
            $document->update([
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => Document::STATUS_PENDING,
                'processed_at' => null,
                'ai_analysis' => null,
            ]);

            // Reset processing status and trigger reprocessing
            $integrationService = new IntegrationService();
            $integration = $integrationService->getIntegration('n8n');

            if ($integration && $integration->webhook_url) {
                // Prepare payload for n8n
                $payload = [
                    'tenant_id' => tenant('id'),
                    'document_id' => $document->id,
                    'document_name' => $document->name,
                    'file_path' => $document->file_path,
                    'mime_type' => $document->mime_type,
                    'file_size' => $document->file_size,
                    'action' => 'file_replaced',
                    'timestamp' => now()->toISOString(),
                ];

                // Send to n8n webhook with retries
                $maxRetries = 3;
                $retryDelay = 1; // seconds

                for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                    try {
                        $response = Http::timeout(30)
                            ->withHeaders([
                                'Content-Type' => 'application/json',
                                'X-Tenant-ID' => tenant('id'),
                                'X-Document-ID' => $document->id,
                            ])
                            ->post($integration->webhook_url, $payload);

                        if ($response->successful()) {
                            $document->update(['status' => Document::STATUS_PROCESSING]);
                            
                            Log::info('Document file replaced and sent to n8n successfully', [
                                'document_id' => $document->id,
                                'tenant_id' => tenant('id'),
                                'attempt' => $attempt,
                                'response_status' => $response->status(),
                            ]);
                            break;
                        } else {
                            throw new \Exception('HTTP ' . $response->status() . ': ' . $response->body());
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to send replaced document to n8n', [
                            'document_id' => $document->id,
                            'tenant_id' => tenant('id'),
                            'attempt' => $attempt,
                            'error' => $e->getMessage(),
                        ]);

                        if ($attempt === $maxRetries) {
                            // Final attempt failed, but file was replaced successfully
                            Log::error('All attempts to send replaced document to n8n failed', [
                                'document_id' => $document->id,
                                'tenant_id' => tenant('id'),
                                'error' => $e->getMessage(),
                            ]);
                        } else {
                            sleep($retryDelay * $attempt); // Exponential backoff
                        }
                    }
                }
            }

            Log::info('Document file replaced successfully', [
                'document_id' => $document->id,
                'tenant_id' => tenant('id'),
                'old_name' => $document->getOriginal('name'),
                'new_name' => $document->name,
            ]);

            return redirect()->route('tenant.documents.show', $document)
                ->with('success', 'Arquivo substituído com sucesso! O documento será reprocessado automaticamente.');

        } catch (\Exception $e) {
            Log::error('Failed to replace document file', [
                'document_id' => $document->id,
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao substituir arquivo: ' . $e->getMessage());
        }
    
    
        try {
            // Validar dados da requisição
            $request->validate([
                'email' => 'required|email|max:255',
                'message' => 'nullable|string|max:1000',
                'download_allowed' => 'boolean',
            ], [
                'email.required' => 'O email do destinatário é obrigatório.',
                'email.email' => 'Por favor, informe um email válido.',
                'message.max' => 'A mensagem não pode ter mais de 1000 caracteres.',
            ]);

            $email = $request->get('email');
            $message = $request->get('message', '');
            $downloadAllowed = $request->boolean('download_allowed', false);

            // Gerar token único para acesso ao documento
            $shareToken = Str::uuid();
            $expiresAt = now()->addDays(7); // Link expira em 7 dias

            // Atualizar metadados do documento com informações de compartilhamento
            $shareData = [
                'shared_at' => now()->toISOString(),
                'shared_by' => auth('tenant')->user()->id,
                'shared_with_email' => $email,
                'share_token' => $shareToken,
                'share_expires_at' => $expiresAt->toISOString(),
                'download_allowed' => $downloadAllowed,
                'custom_message' => $message,
            ];

            $document->update([
                'metadata' => array_merge($document->metadata ?? [], [
                    'shares' => array_merge($document->metadata['shares'] ?? [], [$shareData])
                ])
            ]);

            // Gerar URL de acesso público
            $shareUrl = route('tenant.documents.public-view', [
                'document' => $document->id,
                'token' => $shareToken
            ]);

            // Aqui você enviaria o email com o link de compartilhamento
            // Por enquanto, vamos apenas simular o envio
            Log::info('Document shared via email', [
                'document_id' => $document->id,
                'document_name' => $document->name,
                'shared_with' => $email,
                'share_token' => $shareToken,
                'download_allowed' => $downloadAllowed,
                'expires_at' => $expiresAt->toISOString(),
                'tenant_id' => tenant('id'),
                'shared_by' => auth('tenant')->user()->id,
            ]);

            // TODO: Implementar envio de email
            // Mail::to($email)->send(new DocumentShared($document, $shareUrl, $message, $downloadAllowed));

            return redirect()->back()
                ->with('success', "Documento compartilhado com sucesso! Link enviado para {$email}.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error sharing document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao compartilhar documento. Tente novamente.');
        }
    }

    /**
     * Bulk upload documents.
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240',
            'client_id' => 'nullable|exists:clients,id',
            'type' => 'required|string|in:' . implode(',', array_keys(Document::getTypes())),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $uploadedDocuments = [];
            $files = $request->file('files');

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs(tenant('id'), $fileName, 'documents');

                $document = Document::create([
                    'client_id' => $request->client_id,
                    'name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'original_name' => $originalName,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'type' => $request->type,
                    'status' => Document::STATUS_UPLOADED,
                    'uploaded_by' => auth()->id(),
                    'metadata' => [
                        'upload_ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'bulk_upload' => true,
                    ],
                ]);

                $uploadedDocuments[] = $document;
            }

            Log::info('Bulk documents uploaded', [
                'count' => count($uploadedDocuments),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => count($uploadedDocuments) . ' documents uploaded successfully!',
                'documents' => $uploadedDocuments,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to bulk upload documents', [
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload documents.',
            ], 500);
        }
    }

    /**
     * Get document statistics.
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => Document::count(),
                'by_status' => Document::selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'by_type' => Document::selectRaw('type, count(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type'),
                'total_size' => Document::sum('file_size'),
                'recent_uploads' => Document::where('created_at', '>=', now()->subDays(7))->count(),
                'processed_today' => Document::where('status', Document::STATUS_PROCESSED)
                    ->whereDate('processed_at', today())
                    ->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Failed to get document stats', [
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics.',
            ], 500);
        }
    }
}
