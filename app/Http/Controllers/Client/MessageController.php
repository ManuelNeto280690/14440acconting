<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
            'type' => 'nullable|string|in:support,general,billing,technical',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
            'subject' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $client = Auth::user();
            
            $message = ChatMessage::create([
                'client_id' => $client->id,
                'message' => $request->message,
                'type' => $request->type ?: 'general',
                'priority' => $request->priority ?: 'normal',
                'subject' => $request->subject,
                'status' => 'sent',
                'sender_type' => 'client',
                'meta' => [
                    'sent_at' => now()->toISOString(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            ]);

            // Log message activity
            activity()
                ->performedOn($message)
                ->causedBy($client)
                ->withProperties(['action' => 'send'])
                ->log('Message sent');

            // Trigger notification to admin/support (in real app, this would be a job)
            $this->notifySupport($message);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'chat_message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified message.
     */
    public function show(ChatMessage $message)
    {
        // Ensure the message belongs to the authenticated client
        if ($message->client_id !== Auth::id()) {
            abort(403, 'Unauthorized access to message');
        }

        // Mark as read if it's unread
        if ($message->status === 'unread') {
            $message->update([
                'status' => 'read',
                'read_at' => now()
            ]);
        }

        return view('client.messages.show', compact('message'));
    }

    /**
     * Mark the specified message as read.
     */
    public function markAsRead(ChatMessage $message)
    {
        // Ensure the message belongs to the authenticated client
        if ($message->client_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to message'
            ], 403);
        }

        try {
            $message->update([
                'status' => 'read',
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark message as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all messages as read for the authenticated client.
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $client = Auth::user();
            
            $updatedCount = ChatMessage::where('client_id', $client->id)
                ->where('status', 'unread')
                ->update([
                    'status' => 'read',
                    'read_at' => now()
                ]);

            // Log bulk read activity
            activity()
                ->causedBy($client)
                ->withProperties([
                    'action' => 'mark_all_read',
                    'count' => $updatedCount
                ])
                ->log('All messages marked as read');

            return response()->json([
                'success' => true,
                'message' => "Marked {$updatedCount} messages as read",
                'count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified message from storage.
     */
    public function destroy(ChatMessage $message)
    {
        // Ensure the message belongs to the authenticated client
        if ($message->client_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to message'
            ], 403);
        }

        try {
            // Log deletion activity
            activity()
                ->performedOn($message)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Message deleted');

            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify support team about new message (placeholder for real implementation).
     */
    private function notifySupport(ChatMessage $message): void
    {
        // In a real implementation, this would dispatch a job to notify support
        // dispatch(new NotifySupportJob($message));
        
        // For now, we'll just log it
        \Log::info('Support notification sent for message: ' . $message->id);
    }
}