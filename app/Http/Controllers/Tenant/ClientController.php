<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:tenant', 'tenant']);
    }

    /**
     * Display a listing of clients.
     */
    public function index(Request $request)
    {
        $query = Client::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $clients = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('status', 'active')->count(),
            'inactive_clients' => Client::where('status', 'inactive')->count(),
            'clients_this_month' => Client::whereMonth('created_at', now()->month)->count(),
        ];

        return view('tenant.clients.index', compact('clients', 'stats'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        return view('tenant.clients.create');
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        // Gerar senha automática segura
        $generatedPassword = Str::random(12) . rand(10, 99);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            // Remover validação de password obrigatória
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $client = Client::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
            'country' => $request->country,
            'tax_id' => $request->tax_id,
            'notes' => $request->notes,
            'status' => $request->status,
            'password' => Hash::make($generatedPassword), // Usar senha gerada automaticamente
            'created_by' => Auth::id(),
        ]);

        // Opcional: Enviar e-mail com credenciais de acesso
        try {
            // Aqui você pode implementar o envio de e-mail com as credenciais
            // Mail::to($client->email)->send(new ClientCredentialsEmail($client, $generatedPassword));
        } catch (\Exception $e) {
            \Log::warning('Failed to send client credentials email', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('tenant.clients.show', $client)
            ->with('success', 'Client created successfully with auto-generated password.');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        // Load relationships
        $client->load(['documents', 'invoices', 'chatMessages']);

        // Get client statistics
        $stats = [
            'total_documents' => $client->documents()->count(),
            'total_invoices' => $client->invoices()->count(),
            'total_messages' => $client->chatMessages()->count(),
            'pending_invoices' => $client->invoices()->where('status', 'pending')->count(),
            'paid_invoices' => $client->invoices()->where('status', 'paid')->count(),
            'overdue_invoices' => $client->invoices()->where('status', 'overdue')->count(),
            'total_revenue' => $client->invoices()->where('status', 'paid')->sum('total_amount'),
            'pending_amount' => $client->invoices()->where('status', 'pending')->sum('total_amount'),
        ];

        // Recent activity
        $recentDocuments = $client->documents()->latest()->take(5)->get();
        $recentInvoices = $client->invoices()->latest()->take(5)->get();
        $recentMessages = $client->chatMessages()->latest()->take(5)->get();

        return view('tenant.clients.show', compact('client', 'stats', 'recentDocuments', 'recentInvoices', 'recentMessages'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        return view('tenant.clients.edit', compact('client'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $client->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
            'country' => $request->country,
            'tax_id' => $request->tax_id,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('tenant.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        // Check if client has any invoices or documents
        if ($client->invoices()->count() > 0 || $client->documents()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete client with existing invoices or documents. Please archive the client instead.');
        }

        $client->delete();

        return redirect()->route('tenant.clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    /**
     * Archive the specified client.
     */
    public function archive(Client $client)
    {
        $client->update(['status' => Client::STATUS_ARCHIVED]);

        return redirect()->back()
            ->with('success', 'Client archived successfully.');
    }

    /**
     * Restore the specified client.
     */
    public function restore(Client $client)
    {
        $client->update(['status' => Client::STATUS_ACTIVE]);

        return redirect()->back()
            ->with('success', 'Client restored successfully.');
    }

    /**
     * Export clients to CSV.
     */
    public function export(Request $request)
    {
        $query = Client::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $clients = $query->get();

        $filename = 'clients_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($clients) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'Company', 'Address', 
                'City', 'State', 'ZIP Code', 'Country', 'Tax ID', 
                'Status', 'Created At', 'Updated At'
            ]);

            // CSV data
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->id,
                    $client->name,
                    $client->email,
                    $client->phone,
                    $client->company,
                    $client->address,
                    $client->city,
                    $client->state,
                    $client->zip_code,
                    $client->country,
                    $client->tax_id,
                    $client->status,
                    $client->created_at->format('Y-m-d H:i:s'),
                    $client->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}