<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\DocumentController;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Facades\Tenancy;

class N8nController extends Controller
{
    /**
     * Check if invoice number exists for a specific client.
     * This endpoint is called by N8N before creating an invoice.
     */
    public function checkInvoiceNumber(Request $request): JsonResponse
    {
        try {
            // Validar headers obrigatórios
            $tenantId = $request->header('X-Tenant-ID');
            $apiKey = $request->header('X-API-Key');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'X-Tenant-ID header is required'
                ], 400);
            }

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'X-API-Key header is required'
                ], 400);
            }

            // Verificar se o tenant existe
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                Log::warning('N8N check invoice: Tenant not found', [
                    'tenant_id' => $tenantId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], 404);
            }

            // Log da requisição recebida
            Log::info('N8N check invoice number request', [
                'tenant_id' => $tenantId,
                'client_id' => $request->get('client_id'),
                'invoice_number' => $request->get('invoice_number'),
                'ip' => $request->ip()
            ]);

            // Inicializar contexto do tenant
            Tenancy::initialize($tenant);

            // Criar uma nova instância do InvoiceController no contexto do tenant
            $invoiceController = new InvoiceController();
            
            // Chamar o método checkInvoiceNumber no contexto do tenant
            $response = $invoiceController->checkInvoiceNumber($request);

            // Finalizar contexto do tenant
            Tenancy::end();

            return $response;

        } catch (\Exception $e) {
            // Garantir que o contexto do tenant seja finalizado em caso de erro
            if (tenancy()->initialized) {
                Tenancy::end();
            }

            Log::error('N8N check invoice error', [
                'tenant_id' => $tenantId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle N8N webhook for invoice creation.
     * This endpoint receives data from N8N and forwards it to the appropriate tenant.
     */
    public function createInvoice(Request $request): JsonResponse
    {
        try {
            // Validar headers obrigatórios
            $tenantId = $request->header('X-Tenant-ID');
            $apiKey = $request->header('X-API-Key');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'X-Tenant-ID header is required'
                ], 400);
            }

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'X-API-Key header is required'
                ], 400);
            }

            // Verificar se o tenant existe
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                Log::warning('N8N webhook: Tenant not found', [
                    'tenant_id' => $tenantId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], 404);
            }

            // Log da requisição recebida
            Log::info('N8N webhook received for invoice creation', [
                'tenant_id' => $tenantId,
                'client_id' => $request->get('client_id'),
                'amount' => $request->get('amount'),
                'description' => $request->get('description'),
                'ip' => $request->ip()
            ]);

            // Inicializar contexto do tenant
            Tenancy::initialize($tenant);

            // Criar uma nova instância do InvoiceController no contexto do tenant
            $invoiceController = new InvoiceController();
            
            // Chamar o método apiCreateSimple no contexto do tenant
            $response = $invoiceController->apiCreateSimple($request);

            // Finalizar contexto do tenant
            Tenancy::end();

            return $response;

        } catch (\Exception $e) {
            // Garantir que o contexto do tenant seja finalizado em caso de erro
            if (tenancy()->initialized) {
                Tenancy::end();
            }

            Log::error('N8N webhook error for invoice creation', [
                'tenant_id' => $tenantId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle N8N general webhook for document processing.
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Validar headers obrigatórios
            $tenantId = $request->header('X-Tenant-ID');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'X-Tenant-ID header is required'
                ], 400);
            }

            // Verificar se o tenant existe
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                Log::warning('N8N webhook: Tenant not found', [
                    'tenant_id' => $tenantId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], 404);
            }

            // Log da requisição recebida
            Log::info('N8N webhook received for document processing', [
                'tenant_id' => $tenantId,
                'document_id' => $request->get('document_id'),
                'payload_size' => strlen(json_encode($request->all())),
                'ip' => $request->ip()
            ]);

            // Inicializar contexto do tenant
            Tenancy::initialize($tenant);

            // Processar dados do documento (OCR, AI, etc.)
            $result = $this->processDocumentData($request);

            // Finalizar contexto do tenant
            Tenancy::end();

            return response()->json([
                'success' => true,
                'message' => 'Document processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            // Garantir que o contexto do tenant seja finalizado em caso de erro
            if (tenancy()->initialized) {
                Tenancy::end();
            }

            Log::error('N8N webhook error for document processing', [
                'tenant_id' => $tenantId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process document data received from N8N.
     */
    private function processDocumentData(Request $request): array
    {
        $documentId = $request->get('document_id');
        $status = $request->get('status');
        $message = $request->get('message');
        $ocrData = $request->get('ocr_data');
        $aiAnalysis = $request->get('ai_analysis');
        $error = $request->get('error');
        
        // Dados específicos para criação de fatura
        $invoiceData = $request->get('invoice_data');
        $duplicateInvoiceDetected = $request->get('duplicate_invoice_detected', false);
        $duplicateDetails = $request->get('duplicate_details');

        Log::info('N8N webhook processing document data', [
            'document_id' => $documentId,
            'status' => $status,
            'message' => $message,
            'has_ocr_data' => !empty($ocrData),
            'has_ai_analysis' => !empty($aiAnalysis),
            'has_error' => !empty($error),
            'has_invoice_data' => !empty($invoiceData),
            'duplicate_detected' => $duplicateInvoiceDetected,
            'tenant_id' => tenant('id')
        ]);

        // Processar documento se ID fornecido
        if ($documentId) {
            $document = \App\Models\Document::find($documentId);
            if ($document) {
                // Determinar status final baseado na resposta do n8n
                $finalStatus = 'processed';
                
                if ($status === 'terminated' || $message === 'terminated') {
                    $finalStatus = 'processed';
                    Log::info('N8N processing terminated successfully', [
                        'document_id' => $documentId,
                        'tenant_id' => tenant('id')
                    ]);
                } elseif ($status === 'failed' || !empty($error)) {
                    $finalStatus = 'failed';
                    Log::error('N8N processing failed', [
                        'document_id' => $documentId,
                        'error' => $error,
                        'tenant_id' => tenant('id')
                    ]);
                } elseif ($status === 'processing') {
                    $finalStatus = 'processing';
                }

                // Atualizar documento com os resultados
                $metadata = array_merge($document->metadata ?? [], [
                    'processed_at' => now()->toISOString(),
                    'processed_by' => 'n8n',
                    'n8n_status' => $status,
                    'n8n_message' => $message
                ]);

                // Adicionar dados de OCR se disponíveis
                if (!empty($ocrData)) {
                    $metadata['ocr_data'] = $ocrData;
                    $document->ocr_text = is_string($ocrData) ? $ocrData : json_encode($ocrData);
                }

                // Adicionar análise de IA se disponível
                if (!empty($aiAnalysis)) {
                    $metadata['ai_analysis'] = $aiAnalysis;
                    $document->ai_analysis = $aiAnalysis;
                }

                // Adicionar dados de fatura se disponíveis
                if ($invoiceData) {
                    $metadata['invoice_data'] = $invoiceData;
                    
                    // Se detectou duplicata, adicionar detalhes
                    if ($duplicateInvoiceDetected) {
                        $metadata['duplicate_invoice'] = [
                            'detected' => true,
                            'details' => $duplicateDetails,
                            'detected_at' => now()->toISOString()
                        ];
                        
                        Log::warning('N8N detected duplicate invoice during processing', [
                            'document_id' => $documentId,
                            'duplicate_details' => $duplicateDetails,
                            'tenant_id' => tenant('id')
                        ]);
                    }
                }

                // Adicionar erro se houver
                if (!empty($error)) {
                    $metadata['error'] = $error;
                }

                $document->update([
                    'status' => $finalStatus,
                    'processed_at' => $finalStatus === 'processed' ? now() : null,
                    'metadata' => $metadata
                ]);

                Log::info('Document updated after N8N processing', [
                    'document_id' => $documentId,
                    'final_status' => $finalStatus,
                    'has_invoice_data' => !empty($invoiceData),
                    'duplicate_detected' => $duplicateInvoiceDetected,
                    'tenant_id' => tenant('id')
                ]);
            } else {
                Log::warning('Document not found for N8N processing', [
                    'document_id' => $documentId,
                    'tenant_id' => tenant('id')
                ]);
            }
        }

        return [
            'document_id' => $documentId,
            'status' => $status ?? 'unknown',
            'message' => $message,
            'processed_at' => now()->toISOString(),
            'ocr_data' => $ocrData,
            'ai_analysis' => $aiAnalysis,
            'invoice_data' => $invoiceData,
            'duplicate_detected' => $duplicateInvoiceDetected,
            'duplicate_details' => $duplicateDetails,
            'error' => $error
        ];
    }

    /**
     * Handle N8N error callback for duplicate invoices.
     * This endpoint is called when N8N detects a duplicate invoice and needs to remove the document.
     */
    public function handleDuplicateInvoice(Request $request): JsonResponse
    {
        try {
            // Validar headers obrigatórios
            $tenantId = $request->header('X-Tenant-ID');
            $apiKey = $request->header('X-API-Key');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'X-Tenant-ID header is required'
                ], 400);
            }

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'X-API-Key header is required'
                ], 400);
            }

            // Verificar se o tenant existe
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                Log::warning('N8N duplicate invoice: Tenant not found', [
                    'tenant_id' => $tenantId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], 404);
            }

            // Log da requisição recebida
            Log::info('N8N duplicate invoice removal request', [
                'tenant_id' => $tenantId,
                'document_id' => $request->get('document_id'),
                'invoice_number' => $request->get('invoice_number'),
                'client_id' => $request->get('client_id'),
                'ip' => $request->ip()
            ]);

            // Inicializar contexto do tenant
            Tenancy::initialize($tenant);

            $documentId = $request->get('document_id');
            $invoiceNumber = $request->get('invoice_number');
            $clientId = $request->get('client_id');

            if (!$documentId) {
                Tenancy::end();
                return response()->json([
                    'success' => false,
                    'message' => 'Document ID is required'
                ], 400);
            }

            // Buscar o documento
            $documentController = new DocumentController();
            $document = \App\Models\Tenant\Document::find($documentId);

            if (!$document) {
                Log::warning('Document not found for duplicate removal', [
                    'document_id' => $documentId,
                    'tenant_id' => $tenantId
                ]);

                Tenancy::end();
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Atualizar metadados do documento antes de remover
            $metadata = array_merge($document->metadata ?? [], [
                'removed_at' => now()->toISOString(),
                'removed_reason' => 'duplicate_invoice',
                'duplicate_invoice_number' => $invoiceNumber,
                'duplicate_client_id' => $clientId,
                'removed_by' => 'n8n_duplicate_detection'
            ]);

            $document->update([
                'status' => 'removed_duplicate',
                'metadata' => $metadata
            ]);

            // Remover o arquivo físico se existir
            if ($document->path && \Storage::exists($document->path)) {
                \Storage::delete($document->path);
                Log::info('Physical file removed for duplicate document', [
                    'document_id' => $documentId,
                    'file_path' => $document->path,
                    'tenant_id' => $tenantId
                ]);
            }

            // Remover o documento do banco de dados
            $document->delete();

            Log::info('Document removed due to duplicate invoice', [
                'document_id' => $documentId,
                'invoice_number' => $invoiceNumber,
                'client_id' => $clientId,
                'tenant_id' => $tenantId
            ]);

            // Finalizar contexto do tenant
            Tenancy::end();

            return response()->json([
                'success' => true,
                'message' => 'Document removed successfully due to duplicate invoice',
                'document_id' => $documentId,
                'invoice_number' => $invoiceNumber,
                'client_id' => $clientId,
                'removed_at' => now()->toISOString()
            ], 200);

        } catch (\Exception $e) {
            // Garantir que o contexto do tenant seja finalizado em caso de erro
            if (tenancy()->initialized) {
                Tenancy::end();
            }

            Log::error('N8N duplicate invoice removal error', [
                'tenant_id' => $tenantId ?? 'unknown',
                'document_id' => $request->get('document_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function aiChatWebhook(Request $request)
    {
        try {
            // Headers ou body como fallback
            $tenantId = $request->header('X-Tenant-ID') ?? $request->get('tenant_id');
            $messageId = $request->header('X-Message-ID') ?? $request->get('message_id');
        
            if (!$tenantId || !$messageId) {
                return response()->json([
                    'success' => false,
                    'message' => 'X-Tenant-ID/X-Message-ID headers or tenant_id/message_id body fields are required'
                ], 400);
            }
        
            // Verificar tenant
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                \Log::warning('N8N AI chat response: Tenant not found', [
                    'tenant_id' => $tenantId,
                    'message_id' => $messageId,
                    'ip' => $request->ip(),
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], 404);
            }
        
            // Texto da resposta pode vir como 'response' ou 'message'
            $responseText = $request->get('response') ?? $request->get('message') ?? 'Sorry, I could not process your message.';
        
            \Log::info('N8N AI chat response received', [
                'tenant_id' => $tenantId,
                'message_id' => $messageId,
                'response_message' => $responseText,
                'ip' => $request->ip()
            ]);
        
            // Inicializar tenancy
            Tenancy::initialize($tenant);
        
            // Buscar mensagem original
            $originalMessage = \App\Models\ChatMessage::find($messageId);
            if (!$originalMessage) {
                Tenancy::end();
                return response()->json([
                    'success' => false,
                    'message' => 'Original message not found'
                ], 404);
            }
        
            // Criar resposta da IA com fallback defensivo para enum
            try {
                $aiResponse = \App\Models\ChatMessage::create([
                    'user_id' => $originalMessage->user_id,
                    'client_id' => null,
                    'message' => $responseText,
                    'type' => \App\Models\ChatMessage::TYPE_AI_CONVERSATION,
                    'direction' => \App\Models\ChatMessage::DIRECTION_INBOUND,
                    'status' => \App\Models\ChatMessage::STATUS_DELIVERED,
                    'response_to' => $originalMessage->id,
                    'ai_confidence' => floatval($request->get('confidence', 0.0)),
                    'intent' => $request->get('intent'),
                    'entities' => is_array($request->get('entities')) ? $request->get('entities') : [],
                    'metadata' => [
                        'ai_model' => $request->get('ai_model'),
                        'processing_time' => $request->get('processing_time'),
                        'received_at' => now()->toISOString(),
                        'n8n_workflow_id' => $request->get('workflow_id'),
                    ],
                ]);
            } catch (\Illuminate\Database\QueryException $qe) {
                if (str_contains(strtolower($qe->getMessage()), 'data truncated for column') &&
                    str_contains($qe->getMessage(), 'type')) {
        
                    \Log::warning('AI chat response type fallback to text due to enum mismatch', [
                        'tenant_id' => $tenantId,
                        'original_message_id' => $messageId,
                        'error' => $qe->getMessage(),
                    ]);
        
                    $aiResponse = \App\Models\ChatMessage::create([
                        'user_id' => $originalMessage->user_id,
                        'client_id' => null,
                        'message' => $responseText,
                        'type' => \App\Models\ChatMessage::TYPE_TEXT,
                        'direction' => \App\Models\ChatMessage::DIRECTION_INBOUND,
                        'status' => \App\Models\ChatMessage::STATUS_DELIVERED,
                        'response_to' => $originalMessage->id,
                        'metadata' => [
                            'ai_model' => $request->get('ai_model'),
                            'processing_time' => $request->get('processing_time'),
                            'received_at' => now()->toISOString(),
                            'n8n_workflow_id' => $request->get('workflow_id'),
                            'fallback_reason' => 'enum type mismatch',
                        ],
                    ]);
                } else {
                    throw $qe;
                }
            }
        
            // Atualizar mensagem original
            $originalMessage->update([
                'status' => \App\Models\ChatMessage::STATUS_PROCESSED,
                'processed_at' => now(),
                'metadata' => array_merge($originalMessage->metadata ?? [], [
                    'ai_response_id' => $aiResponse->id,
                    'processed_at' => now()->toISOString(),
                ])
            ]);
        
            \Log::info('AI chat response processed successfully', [
                'tenant_id' => $tenantId,
                'original_message_id' => $messageId,
                'ai_response_id' => $aiResponse->id,
                'user_id' => $originalMessage->user_id,
            ]);
        
            Tenancy::end();
        
            return response()->json([
                'success' => true,
                'message' => 'AI response processed successfully',
                'data' => [
                    'original_message_id' => $originalMessage->id,
                    'ai_response_id' => $aiResponse->id,
                ]
            ]);
        
        } catch (\Exception $e) {
            if (tenancy()->initialized) {
                Tenancy::end();
            }
        
            \Log::error('N8N AI chat response error', [
                'tenant_id' => $tenantId ?? 'unknown',
                'message_id' => $messageId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
        
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }
}