<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the client dashboard.
     */
    public function index()
    {
        $client = Auth::guard('client')->user();
        
        // Get statistics
        $stats = $this->getClientStatistics($client);
        
        // Get recent activities
        $recentDocuments = $client->documents()
            ->latest()
            ->limit(5)
            ->get();
            
        $recentInvoices = $client->invoices()
            ->latest()
            ->limit(5)
            ->get();
            
        $recentMessages = $client->chatMessages()
            ->latest()
            ->limit(5)
            ->get();
            
        // Get monthly data for charts
        $monthlyData = $this->getMonthlyData($client);
        
        return view('client.dashboard', compact(
            'client',
            'stats',
            'recentDocuments',
            'recentInvoices',
            'recentMessages',
            'monthlyData'
        ));
    }
    
    /**
     * Get client statistics.
     */
    private function getClientStatistics($client)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Document statistics
        $totalDocuments = $client->documents()->count();
        $documentsThisMonth = $client->documents()
            ->where('created_at', '>=', $currentMonth)
            ->count();
        $documentsLastMonth = $client->documents()
            ->whereBetween('created_at', [$lastMonth, $currentMonth])
            ->count();
        $documentsGrowth = $documentsLastMonth > 0 
            ? (($documentsThisMonth - $documentsLastMonth) / $documentsLastMonth) * 100 
            : 0;
            
        // Invoice statistics
        $totalInvoices = $client->invoices()->count();
        $paidInvoices = $client->invoices()->where('status', 'paid')->count();
        $pendingInvoices = $client->invoices()->where('status', 'pending')->count();
        $overdueInvoices = $client->invoices()
            ->where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->count();
            
        // Financial statistics
        $totalAmount = $client->invoices()->sum('amount');
        $paidAmount = $client->invoices()->where('status', 'paid')->sum('amount');
        $pendingAmount = $client->invoices()->where('status', 'pending')->sum('amount');
        $overdueAmount = $client->invoices()
            ->where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->sum('amount');
            
        // Message statistics
        $totalMessages = $client->chatMessages()->count();
        $unreadMessages = $client->chatMessages()->where('status', 'unread')->count();
        $messagesThisMonth = $client->chatMessages()
            ->where('created_at', '>=', $currentMonth)
            ->count();
            
        // Document processing statistics
        $processingDocuments = $client->documents()->where('status', 'processing')->count();
        $processedDocuments = $client->documents()->where('status', 'processed')->count();
        $failedDocuments = $client->documents()->where('status', 'failed')->count();
        
        // Storage usage
        $storageUsed = $client->documents()->sum('size');
        $storageUsedMB = round($storageUsed / (1024 * 1024), 2);
        
        return [
            'total_documents' => $totalDocuments,
            'documents_this_month' => $documentsThisMonth,
            'documents_growth' => round($documentsGrowth, 1),
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'pending_invoices' => $pendingInvoices,
            'overdue_invoices' => $overdueInvoices,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $pendingAmount,
            'overdue_amount' => $overdueAmount,
            'total_messages' => $totalMessages,
            'unread_messages' => $unreadMessages,
            'messages_this_month' => $messagesThisMonth,
            'processing_documents' => $processingDocuments,
            'processed_documents' => $processedDocuments,
            'failed_documents' => $failedDocuments,
            'storage_used_mb' => $storageUsedMB,
            'payment_rate' => $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 1) : 0,
        ];
    }
    
    /**
     * Get monthly data for charts.
     */
    private function getMonthlyData($client)
    {
        $months = [];
        $documentsData = [];
        $invoicesData = [];
        $revenueData = [];
        
        // Get data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $months[] = $date->format('M Y');
            
            // Documents count
            $documentsData[] = $client->documents()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
                
            // Invoices count
            $invoicesData[] = $client->invoices()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
                
            // Revenue (paid invoices)
            $revenueData[] = $client->invoices()
                ->where('status', 'paid')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('amount');
        }
        
        return [
            'months' => $months,
            'documents' => $documentsData,
            'invoices' => $invoicesData,
            'revenue' => $revenueData,
        ];
    }
    
    /**
     * Get dashboard data via AJAX.
     */
    public function getData(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $type = $request->get('type', 'stats');
        
        switch ($type) {
            case 'stats':
                return response()->json($this->getClientStatistics($client));
                
            case 'recent_documents':
                $documents = $client->documents()
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($document) {
                        return [
                            'id' => $document->id,
                            'name' => $document->name,
                            'type' => $document->type,
                            'status' => $document->status,
                            'size' => number_format($document->size / 1024, 2) . ' KB',
                            'created_at' => $document->created_at->diffForHumans(),
                            'url' => route('client.documents.show', $document),
                        ];
                    });
                return response()->json($documents);
                
            case 'recent_invoices':
                $invoices = $client->invoices()
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($invoice) {
                        return [
                            'id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'amount' => '$' . number_format($invoice->amount, 2),
                            'status' => $invoice->status,
                            'due_date' => $invoice->due_date->format('M d, Y'),
                            'created_at' => $invoice->created_at->diffForHumans(),
                            'url' => route('client.invoices.show', $invoice),
                        ];
                    });
                return response()->json($invoices);
                
            case 'recent_messages':
                $messages = $client->chatMessages()
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'type' => $message->type,
                            'message' => \Str::limit($message->message, 100),
                            'status' => $message->status,
                            'created_at' => $message->created_at->diffForHumans(),
                            'url' => route('client.messages.show', $message),
                        ];
                    });
                return response()->json($messages);
                
            case 'monthly_data':
                return response()->json($this->getMonthlyData($client));
                
            default:
                return response()->json(['error' => 'Invalid data type'], 400);
        }
    }
    
    /**
     * Mark all notifications as read.
     */
    public function markNotificationsRead()
    {
        $client = Auth::guard('client')->user();
        
        $client->chatMessages()
            ->where('status', 'unread')
            ->update(['status' => 'read']);
            
        return response()->json(['success' => true]);
    }
    
    /**
     * Get client profile summary.
     */
    public function getProfileSummary()
    {
        $client = Auth::guard('client')->user();
        
        $summary = [
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'company' => $client->company,
            'status' => $client->status,
            'member_since' => $client->created_at->format('M d, Y'),
            'last_activity' => $client->updated_at->diffForHumans(),
            'total_documents' => $client->documents()->count(),
            'total_invoices' => $client->invoices()->count(),
            'total_messages' => $client->chatMessages()->count(),
        ];
        
        return response()->json($summary);
    }
}