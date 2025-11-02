<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\ChatMessage;

class DashboardController extends Controller
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
     * Show the tenant dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get recent activities
        $recentDocuments = Document::latest()
            ->take(5)
            ->get();
            
        $recentClients = Client::latest()
            ->take(5)
            ->get();
            
        $recentInvoices = Invoice::latest()
            ->take(5)
            ->get();
            
        $recentMessages = ChatMessage::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('tenant.dashboard', compact(
            'stats',
            'recentDocuments',
            'recentClients', 
            'recentInvoices',
            'recentMessages'
        ));
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats()
    {
        return [
            'total_clients' => Client::count(),
            'total_invoices' => Invoice::count(),
            'total_documents' => Document::count(),
            'total_messages' => ChatMessage::count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            'active_clients' => Client::where('status', 'active')->count(),
            'documents_this_month' => Document::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'revenue_this_month' => Invoice::where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount'),
            'storage_used_mb' => Document::sum('file_size') / 1024 / 1024, // Convert bytes to MB
        ];
    }
}