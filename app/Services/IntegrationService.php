<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class IntegrationService
{
    /**
     * Send multiple documents to n8n webhook
     */
    public function sendDocumentsToN8n(array $documents, string $clientId): array
    {
        // Buscar integração n8n ativa
        $n8nIntegration = Integration::where('service_name', Integration::SERVICE_N8N)
            ->where('is_active', true)
            ->first();

        if (!$n8nIntegration) {
            Log::warning('No active n8n integration found', [
                'tenant_id' => tenant('id'),
                'client_id' => $clientId,
            ]);

            return [
                'success' => false,
                'total' => count($documents),
                'success_count' => 0,
                'failure_count' => count($documents),
                'error' => 'No active n8n integration configured',
                'results' => []
            ];
        }

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($documents as $document) {
            try {
                $result = $this->sendSingleDocumentToN8n($document, $clientId, $n8nIntegration);
                $results[] = $result;
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (Exception $e) {
                Log::error('Error sending document to n8n webhook', [
                    'document_id' => $document->id,
                    'client_id' => $clientId,
                    'error' => $e->getMessage(),
                    'integration_id' => $n8nIntegration->id,
                ]);
                
                $results[] = [
                    'success' => false,
                    'document_id' => $document->id,
                    'error' => $e->getMessage()
                ];
                $failureCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'total' => count($documents),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results
        ];
    }

    /**
     * Send a single document to n8n webhook
     */
    private function sendSingleDocumentToN8n(Document $document, string $clientId, Integration $integration): array
    {
        try {
            // Prepare the payload with binary content
            $filePath = Storage::disk('documents')->path($document->file_path);
            
            // Verificar se o arquivo existe
            if (!file_exists($filePath)) {
                Log::error('Document file not found for n8n webhook', [
                    'document_id' => $document->id,
                    'file_path' => $filePath,
                    'tenant_id' => tenant('id')
                ]);
                throw new \Exception('Document file not found: ' . $document->file_path);
            }

            // Ler o conteúdo do arquivo e converter para base64
            $fileContent = file_get_contents($filePath);
            $base64Content = base64_encode($fileContent);
            
            $payload = [
                'client_id' => $clientId,
                'document' => [
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
                ],
                'tenant_id' => tenant('id'),
                'timestamp' => now()->toISOString()
            ];

            Log::info('Sending document to n8n with binary content', [
                'document_id' => $document->id,
                'file_size' => $document->file_size,
                'content_size' => strlen($base64Content),
                'tenant_id' => tenant('id'),
            ]);

            // Send the request to n8n webhook
            $response = Http::timeout($integration->timeout_seconds ?? 30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-Key' => $integration->api_key,
                    'X-Tenant-ID' => tenant('id'),
                    'User-Agent' => '1440Accounting/1.0'
                ])
                ->post($integration->webhook_url, $payload);

            if ($response->successful()) {
                // Update document status to processing
                $document->update([
                    'status' => Document::STATUS_PROCESSING,
                    'metadata' => array_merge($document->metadata ?? [], [
                        'webhook_sent_at' => now()->toISOString(),
                        'webhook_response' => $response->json(),
                        'integration_id' => $integration->id,
                    ])
                ]);

                Log::info('Document sent to n8n webhook successfully', [
                    'document_id' => $document->id,
                    'client_id' => $clientId,
                    'response_status' => $response->status(),
                    'integration_id' => $integration->id,
                ]);

                return [
                    'success' => true,
                    'document_id' => $document->id,
                    'response' => $response->json(),
                    'status_code' => $response->status()
                ];
            } else {
                // Update document status to failed
                $document->update([
                    'status' => Document::STATUS_FAILED,
                    'metadata' => array_merge($document->metadata ?? [], [
                        'webhook_error_at' => now()->toISOString(),
                        'webhook_error' => [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ],
                        'integration_id' => $integration->id,
                    ])
                ]);

                Log::error('Failed to send document to n8n webhook', [
                    'document_id' => $document->id,
                    'client_id' => $clientId,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'integration_id' => $integration->id,
                ]);

                return [
                    'success' => false,
                    'document_id' => $document->id,
                    'error' => 'Webhook request failed',
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ];
            }

        } catch (Exception $e) {
            // Update document status to failed
            $document->update([
                'status' => Document::STATUS_FAILED,
                'metadata' => array_merge($document->metadata ?? [], [
                    'webhook_error_at' => now()->toISOString(),
                    'webhook_error' => $e->getMessage(),
                    'integration_id' => $integration->id,
                ])
            ]);

            throw $e;
        }
    }

    /**
     * Check if n8n webhook is available
     */
    public function testN8nConnection(): bool
    {
        $n8nIntegration = Integration::where('service_name', Integration::SERVICE_N8N)
            ->where('is_active', true)
            ->first();

        if (!$n8nIntegration) {
            return false;
        }

        try {
            $response = Http::timeout($n8nIntegration->timeout_seconds ?? 10)
                ->withHeaders([
                    'X-API-Key' => $n8nIntegration->api_key,
                    'X-Tenant-ID' => tenant('id')
                ])
                ->get($n8nIntegration->webhook_url);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Failed to test n8n connection', [
                'error' => $e->getMessage(),
                'integration_id' => $n8nIntegration->id,
            ]);
            return false;
        }
    }
}
