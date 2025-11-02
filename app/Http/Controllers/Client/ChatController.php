<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Display the AI chat interface for clients
     */
    public function aiChat(): View
    {
        $client = Auth::guard('client')->user();
        
        // Get AI chat messages for this client
        $messages = ChatMessage::where('client_id', $client->id)
            ->where('type', 'ai_chat')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        // Calculate statistics
        $totalMessages = ChatMessage::where('client_id', $client->id)
            ->where('type', 'ai_chat')
            ->count();

        $todayMessages = ChatMessage::where('client_id', $client->id)
            ->where('type', 'ai_chat')
            ->whereDate('created_at', today())
            ->count();

        $userMessages = ChatMessage::where('client_id', $client->id)
            ->where('type', 'ai_chat')
            ->where('direction', 'outbound')
            ->count();

        $aiResponses = ChatMessage::where('client_id', $client->id)
            ->where('type', 'ai_chat')
            ->where('direction', 'inbound')
            ->count();

        return view('client.chat.ai', compact(
            'messages',
            'totalMessages',
            'todayMessages',
            'userMessages',
            'aiResponses'
        ));
    }

    /**
     * Send message to AI via n8n webhook
     */
    public function sendToAI(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $client = Auth::guard('client')->user();
        $messageId = Str::uuid();

        try {
            // Create the outbound message record
            $chatMessage = ChatMessage::create([
                'id' => $messageId,
                'client_id' => $client->id,
                'message' => $request->message,
                'type' => 'ai_chat',
                'direction' => 'outbound',
                'status' => 'pending',
                'metadata' => [
                    'user_type' => 'client',
                    'user_name' => $client->name,
                    'user_email' => $client->email,
                    'timestamp' => now()->toISOString(),
                ],
            ]);

            // Get AI chat webhook configuration
            $webhookUrl = config('services.n8n.ai_chat.webhook_url');
            $webhookSecret = config('services.n8n.ai_chat.webhook_secret');
            $timeout = config('services.n8n.ai_chat.timeout', 30);
            $maxRetries = config('services.n8n.ai_chat.max_retries', 2);
            $retryDelay = config('services.n8n.ai_chat.retry_delay', 1);

            if (!$webhookUrl) {
                throw new \Exception('AI Chat webhook URL not configured');
            }

            // Prepare webhook payload
            $payload = [
                'message_id' => $messageId,
                'tenant_id' => tenant('id'),
                'client_id' => $client->id,
                'user_type' => 'client',
                'user_name' => $client->name,
                'user_email' => $client->email,
                'message' => $request->message,
                'timestamp' => now()->toISOString(),
                'context' => [
                    'conversation_history' => $this->getRecentConversationHistory($client->id),
                ],
            ];

            // Send to n8n with retry logic
            $response = null;
            $lastException = null;

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $headers = [
                        'Content-Type' => 'application/json',
                        'X-Tenant-ID' => tenant('id'),
                        'X-Message-ID' => $messageId,
                    ];

                    if ($webhookSecret) {
                        $signature = hash_hmac('sha256', json_encode($payload), $webhookSecret);
                        $headers['X-Webhook-Signature'] = $signature;
                    }

                    $response = Http::timeout($timeout)
                        ->withHeaders($headers)
                        ->post($webhookUrl, $payload);

                    if ($response->successful()) {
                        break;
                    }

                    throw new \Exception("HTTP {$response->status()}: {$response->body()}");
                } catch (\Exception $e) {
                    $lastException = $e;
                    
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        continue;
                    }
                }
            }

            if (!$response || !$response->successful()) {
                throw $lastException ?? new \Exception('Failed to send message to AI');
            }

            // Update message status
            $chatMessage->update([
                'status' => 'sent',
                'metadata' => array_merge($chatMessage->metadata ?? [], [
                    'webhook_response' => $response->json(),
                    'sent_at' => now()->toISOString(),
                ]),
            ]);

            Log::info('AI chat message sent successfully', [
                'message_id' => $messageId,
                'client_id' => $client->id,
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent to AI successfully',
                'message_id' => $messageId,
                'chat_message' => $chatMessage->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send AI chat message', [
                'message_id' => $messageId,
                'client_id' => $client->id,
                'tenant_id' => tenant('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update message status to failed
            if (isset($chatMessage)) {
                $chatMessage->update([
                    'status' => 'failed',
                    'metadata' => array_merge($chatMessage->metadata ?? [], [
                        'error' => $e->getMessage(),
                        'failed_at' => now()->toISOString(),
                    ]),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message to AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get AI chat messages for polling
     */
    public function getAIMessages(Request $request): JsonResponse
    {
        $client = Auth::guard('client')->user();
        $lastMessageId = $request->get('last_message_id');

        $query = ChatMessage::where('client_id', $client->id)
            ->where('type', 'ai_chat')
            ->orderBy('created_at', 'desc');

        // If last_message_id is provided, get only newer messages
        if ($lastMessageId) {
            $lastMessage = ChatMessage::find($lastMessageId);
            if ($lastMessage) {
                $query->where('created_at', '>', $lastMessage->created_at);
            }
        } else {
            // Get last 50 messages
            $query->limit(50);
        }

        $messages = $query->get()->reverse()->values();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'count' => $messages->count(),
        ]);
    }

    /**
     * Get recent conversation history for context
     */
    private function getRecentConversationHistory(string $clientId, int $limit = 10): array
    {
        $messages = ChatMessage::where('client_id', $clientId)
            ->where('type', 'ai_chat')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return $messages->map(function ($message) {
            return [
                'role' => $message->direction === 'outbound' ? 'user' : 'assistant',
                'content' => $message->message,
                'timestamp' => $message->created_at->toISOString(),
            ];
        })->toArray();
    }
}