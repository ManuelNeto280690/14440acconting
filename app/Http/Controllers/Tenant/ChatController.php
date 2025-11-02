<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:tenant', 'tenant']);
    }

    /**
     * Display a listing of chat messages.
     */
    public function index(Request $request): View
    {
        $query = ChatMessage::with(['client', 'user']);

        // Filter by client if specified
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('message', 'like', "%{$search}%");
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $messages = $query->paginate(20)->withQueryString();

        // Get clients for filter dropdown
        $clients = Client::select('id', 'name', 'email')->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_messages' => ChatMessage::count(),
            'unread_messages' => ChatMessage::where('status', 'unread')->count(),
            'pending_messages' => ChatMessage::where('status', 'pending')->count(),
            'today_messages' => ChatMessage::whereDate('created_at', today())->count(),
        ];

        return view('tenant.chat.index', compact('messages', 'clients', 'stats'));
    }

    /**
     * Display the modern chat interface.
     */
    public function conversation(Request $request, Client $client = null): View
    {
        // Se não foi especificado um cliente, pegar o primeiro disponível
        if (!$client) {
            $client = Client::where('status', 'active')->first();
            if (!$client) {
                return redirect()->route('tenant.clients.create')
                    ->with('error', 'Nenhum cliente ativo encontrado. Crie um cliente primeiro.');
            }
        }

        // Buscar conversas (clientes com mensagens)
        $conversations = Client::select('clients.*')
            ->selectRaw('
                (SELECT COUNT(*) FROM chat_messages WHERE client_id = clients.id AND direction = "inbound" AND status != "read") as unread_count,
                (SELECT message FROM chat_messages WHERE client_id = clients.id ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT direction FROM chat_messages WHERE client_id = clients.id ORDER BY created_at DESC LIMIT 1) as last_message_direction,
                (SELECT created_at FROM chat_messages WHERE client_id = clients.id ORDER BY created_at DESC LIMIT 1) as last_message_at
            ')
            ->whereExists(function ($query) {
                $query->select('id')
                    ->from('chat_messages')
                    ->whereColumn('client_id', 'clients.id');
            })
            ->orWhere('id', $client->id) // Incluir o cliente atual mesmo sem mensagens
            ->where('status', 'active')
            ->orderByRaw('COALESCE(last_message_at, "1970-01-01 00:00:00") DESC')
            ->get();

        // Buscar mensagens do cliente atual
        $messages = $client->chatMessages()
            ->with(['user'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Todos os clientes para o modal de nova conversa
        $allClients = Client::select('id', 'name', 'email')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('tenant.chat.conversation', compact('client', 'conversations', 'messages', 'allClients'));
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'message' => 'required|string|max:1000',
            'type' => 'sometimes|string|in:text,image,document,audio,system',
        ]);

        try {
            $message = ChatMessage::create([
                'user_id' => auth()->id(),
                'client_id' => $request->client_id,
                'message' => $request->message,
                'type' => $request->get('type', ChatMessage::TYPE_TEXT),
                'direction' => ChatMessage::DIRECTION_OUTBOUND,
                'status' => ChatMessage::STATUS_DELIVERED,
            ]);

            Log::info('Chat message sent', [
                'message_id' => $message->id,
                'client_id' => $request->client_id,
                'user_id' => auth()->id(),
            ]);

            // Se for uma requisição AJAX, retornar JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mensagem enviada com sucesso',
                    'data' => $message->load(['client', 'user'])
                ]);
            }

            // Se for uma requisição normal, redirecionar
            return redirect()->back()->with('success', 'Mensagem enviada com sucesso');

        } catch (\Exception $e) {
            Log::error('Failed to send chat message', [
                'error' => $e->getMessage(),
                'client_id' => $request->client_id,
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao enviar mensagem'
                ], 500);
            }

            return redirect()->back()->with('error', 'Erro ao enviar mensagem');
        }
    }

    /**
     * Get messages for a specific client (AJAX)
     */
    public function getMessages(Request $request, $clientId)
    {
        try {
            $query = ChatMessage::where('client_id', $clientId)
                ->with(['user', 'client'])
                ->orderBy('created_at', 'asc');

            // If 'after' parameter is provided, get only newer messages
            if ($request->has('after')) {
                $query->where('id', '>', $request->after);
            }

            $messages = $query->get()->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'type' => $message->type,
                    'direction' => $message->direction,
                    'status' => $message->status,
                    'created_at' => $message->created_at->toISOString(),
                    'ai_confidence' => $message->ai_confidence,
                    'user' => $message->user ? [
                        'name' => $message->user->name,
                        'avatar' => $message->user->avatar
                    ] : null,
                    'attachments' => $message->metadata['attachments'] ?? []
                ];
            });

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar mensagens'
            ], 500);
        }
    }

    /**
     * Get unread message counts for all clients
     */
    public function getUnreadCounts()
    {
        try {
            $counts = ChatMessage::where('direction', 'inbound')
                ->where('status', 'pending')
                ->groupBy('client_id')
                ->selectRaw('client_id, count(*) as count')
                ->pluck('count', 'client_id')
                ->toArray();

            return response()->json([
                'success' => true,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting unread counts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar contadores'
            ], 500);
        }
    }

    /**
     * Upload files for chat
     */
    public function uploadFiles(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:10240', // 10MB max
            'client_id' => 'required|exists:clients,id'
        ]);

        try {
            $client = Client::findOrFail($request->client_id);
            $attachments = [];

            foreach ($request->file('files') as $file) {
                $path = $file->store('chat-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => Storage::url($path),
                    'type' => $this->getFileType($file->getMimeType()),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }

            // Create message with attachments
            $message = ChatMessage::create([
                'user_id' => auth()->id(),
                'client_id' => $client->id,
                'message' => 'Arquivo(s) enviado(s)',
                'type' => ChatMessage::TYPE_DOCUMENT,
                'direction' => ChatMessage::DIRECTION_OUTBOUND,
                'status' => ChatMessage::STATUS_DELIVERED,
                'metadata' => [
                    'attachments' => $attachments
                ]
            ]);

            Log::info('Files uploaded in chat', [
                'message_id' => $message->id,
                'client_id' => $client->id,
                'files_count' => count($attachments)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo(s) enviado(s) com sucesso',
                'attachments' => $attachments
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading chat files: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar arquivo(s)'
            ], 500);
        }
    }

    /**
     * Get file type from mime type
     */
    private function getFileType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif ($mimeType === 'application/pdf') {
            return 'pdf';
        } elseif (in_array($mimeType, [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])) {
            return 'excel';
        } elseif (in_array($mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return 'word';
        } else {
            return 'document';
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, $clientId)
    {
        try {
            ChatMessage::where('client_id', $clientId)
                ->where('direction', 'inbound')
                ->where('status', 'pending')
                ->update(['status' => 'read']);

            return response()->json([
                'success' => true,
                'message' => 'Mensagens marcadas como lidas'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking messages as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar mensagens como lidas'
            ], 500);
        }
    }

    /**
     * Display the AI chat interface for tenant users.
     */
    public function aiChat(Request $request): View
    {
        // Buscar mensagens de IA do usuário atual
        $messages = ChatMessage::where('user_id', auth()->id())
            ->where('type', ChatMessage::TYPE_AI_CONVERSATION)
            ->orderBy('created_at', 'asc')
            ->get();

        // Estatísticas do chat com IA
        $stats = [
            'total_messages' => ChatMessage::where('user_id', auth()->id())
                ->where('type', ChatMessage::TYPE_AI_CONVERSATION)
                ->count(),
            'today_messages' => ChatMessage::where('user_id', auth()->id())
                ->where('type', ChatMessage::TYPE_AI_CONVERSATION)
                ->whereDate('created_at', today())
                ->count(),
            'ai_responses' => ChatMessage::where('user_id', auth()->id())
                ->where('type', ChatMessage::TYPE_AI_CONVERSATION)
                ->where('direction', 'inbound')
                ->count(),
            'user_messages' => ChatMessage::where('user_id', auth()->id())
                ->where('type', ChatMessage::TYPE_AI_CONVERSATION)
                ->where('direction', 'outbound')
                ->count(),
        ];

        return view('tenant.chat.ai', compact('messages', 'stats'));
    }

    /**
     * Send message to AI via n8n webhook.
     */
    public function sendToAI(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Inserção com fallback de enum (mantido para robustez)
            try {
                $userMessage = ChatMessage::create([
                    'user_id' => auth()->id(),
                    'client_id' => null,
                    'message' => $request->message,
                    'type' => ChatMessage::TYPE_AI_CONVERSATION,
                    'direction' => ChatMessage::DIRECTION_OUTBOUND,
                    'status' => ChatMessage::STATUS_DELIVERED,
                    'metadata' => [
                        'sent_at' => now()->toISOString(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ],
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), "Data truncated for column 'type'")) {
                    \Log::warning('AI chat type enum mismatch, falling back to text', [
                        'tenant_id' => tenant('id'),
                        'user_id' => auth()->id(),
                        'error' => $e->getMessage(),
                    ]);
                    $userMessage = ChatMessage::create([
                        'user_id' => auth()->id(),
                        'client_id' => null,
                        'message' => $request->message,
                        'type' => ChatMessage::TYPE_TEXT,
                        'direction' => ChatMessage::DIRECTION_OUTBOUND,
                        'status' => ChatMessage::STATUS_DELIVERED,
                        'metadata' => [
                            'sent_at' => now()->toISOString(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ],
                    ]);
                } else {
                    throw $e;
                }
            }

            // Buscar integração n8n ativa por tenant
            $integration = \App\Models\Integration::where('service_name', 'n8n')
                ->where('is_active', true)
                ->first();

            // Fallback para .env (services.n8n.ai_chat.*) caso não exista integração no banco
            $webhookUrl = $integration->webhook_url ?? config('services.n8n.ai_chat.webhook_url');
            $webhookSecret = $integration->webhook_secret ?? config('services.n8n.ai_chat.webhook_secret');
            $timeout = config('services.n8n.ai_chat.timeout', 30);
            $maxRetries = config('services.n8n.ai_chat.max_retries', 3);
            $retryDelay = config('services.n8n.ai_chat.retry_delay', 1);

            if (empty($webhookUrl)) {
                // Sem integração e sem fallback -> erro claro
                $userMessage->update([
                    'status' => ChatMessage::STATUS_FAILED,
                    'metadata' => array_merge($userMessage->metadata ?? [], [
                        'error' => 'AI integration not configured',
                        'checked_at' => now()->toISOString(),
                    ]),
                ]);

                \Log::warning('AI integration not configured for tenant and no env fallback', [
                    'tenant_id' => tenant('id'),
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Integração de IA (n8n) não configurada para este tenant.',
                ], 400);
            }

            // Enviar para n8n (POST), com HMAC
            $payload = [
                'tenant_id' => tenant('id'),
                'user_id' => auth()->id(),
                'message_id' => $userMessage->id,
                'message' => $request->message,
                'type' => 'ai_chat',
                'timestamp' => now()->toISOString(),
                'user_name' => auth()->user()->name,
                'user_email' => auth()->user()->email,
            ];

            $webhookSuccess = false;
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout($timeout)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                            'X-Tenant-ID' => tenant('id'),
                            'X-Message-ID' => $userMessage->id,
                            'X-Webhook-Signature' => hash_hmac('sha256', json_encode($payload), $webhookSecret ?? ''),
                        ])
                        ->post($webhookUrl, $payload);

                    if ($response->successful()) {
                        $webhookSuccess = true;

                        $userMessage->update([
                            'status' => ChatMessage::STATUS_PROCESSING,
                            'metadata' => array_merge($userMessage->metadata ?? [], [
                                'webhook_sent_at' => now()->toISOString(),
                                'webhook_response_status' => $response->status(),
                            ]),
                        ]);

                        \Log::info('AI chat message sent to n8n successfully', [
                            'message_id' => $userMessage->id,
                            'tenant_id' => tenant('id'),
                            'attempt' => $attempt,
                        ]);

                        break;
                    }
                } catch (\Throwable $ex) {
                    \Log::warning('Error sending AI message to n8n', [
                        'error' => $ex->getMessage(),
                        'attempt' => $attempt,
                        'tenant_id' => tenant('id'),
                    ]);
                }

                usleep($retryDelay * 1_000_000);
            }

            if (!$webhookSuccess) {
                $userMessage->update(['status' => ChatMessage::STATUS_FAILED]);
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar a mensagem para a IA.',
                ], 502);
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::error('Error in AI chat send message', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar a mensagem para a IA.',
            ], 500);
        }
    }

    /**
     * Get AI conversation messages for current user.
     */
    public function getAIMessages(Request $request)
    {
        try {
            $query = ChatMessage::where('user_id', auth()->id())
                ->where('type', ChatMessage::TYPE_AI_CONVERSATION)
                ->orderBy('created_at', 'asc');

            // If 'after' parameter is provided, get only newer messages
            if ($request->has('after')) {
                $query->where('id', '>', $request->after);
            }

            $messages = $query->get()->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'type' => $message->type,
                    'direction' => $message->direction,
                    'status' => $message->status,
                    'created_at' => $message->created_at->toISOString(),
                    'ai_confidence' => $message->ai_confidence,
                    'user' => $message->user ? [
                        'name' => $message->user->name,
                        'avatar' => $message->user->avatar
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting AI messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar mensagens'
            ], 500);
        }
    }
}