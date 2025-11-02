<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClientController extends Controller
{
    /**
     * Display the client's own information.
     */
    public function show(): View
    {
        $client = Auth::guard('client')->user();
        
        // Get client statistics
        $stats = [
            'total_documents' => $client->documents()->count(),
            'pending_documents' => $client->documents()->where('status', 'pending')->count(),
            'processed_documents' => $client->documents()->where('status', 'processed')->count(),
            'total_invoices' => $client->invoices()->count(),
            'paid_invoices' => $client->invoices()->where('status', 'paid')->count(),
            'pending_invoices' => $client->invoices()->where('status', 'pending')->count(),
            'overdue_invoices' => $client->invoices()->where('status', 'overdue')->count(),
            'total_messages' => $client->chatMessages()->count(),
            'member_since' => $client->created_at->format('M d, Y'),
            'last_login' => $client->last_login_at ? $client->last_login_at->format('M d, Y \a\t g:i A') : 'Never',
        ];
        
        return view('client.show', compact('client', 'stats'));
    }

    /**
     * Show the form for editing the client's information.
     */
    public function edit(): View
    {
        $client = Auth::guard('client')->user();
        
        return view('client.edit', compact('client'));
    }

    /**
     * Update the client's information.
     */
    public function update(Request $request): RedirectResponse
    {
        $client = Auth::guard('client')->user();
        
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
        ]);

        return redirect()->route('client.show')
            ->with('success', 'Your information has been updated successfully.');
    }

    /**
     * Show the form for changing password.
     */
    public function showChangePasswordForm(): View
    {
        return view('client.change-password');
    }

    /**
     * Update the client's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $client = Auth::guard('client')->user();

        // Verify current password
        if (!Hash::check($request->current_password, $client->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $client->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('client.show')
            ->with('success', 'Your password has been updated successfully.');
    }

    /**
     * Display client's documents.
     */
    public function documents(Request $request): View
    {
        $client = Auth::guard('client')->user();
        
        $query = $client->documents()->with(['uploader']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
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

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $documents = $query->paginate(12)->withQueryString();

        // Statistics
        $stats = [
            'total_documents' => $client->documents()->count(),
            'pending_documents' => $client->documents()->where('status', 'pending')->count(),
            'processing_documents' => $client->documents()->where('status', 'processing')->count(),
            'processed_documents' => $client->documents()->where('status', 'processed')->count(),
            'failed_documents' => $client->documents()->where('status', 'failed')->count(),
        ];

        return view('client.documents.index', compact('documents', 'stats'));
    }

    /**
     * Display client's invoices.
     */
    public function invoices(Request $request): View
    {
        $client = Auth::guard('client')->user();
        
        $query = $client->invoices();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $invoices = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_invoices' => $client->invoices()->count(),
            'paid_invoices' => $client->invoices()->where('status', 'paid')->count(),
            'pending_invoices' => $client->invoices()->where('status', 'pending')->count(),
            'overdue_invoices' => $client->invoices()->where('status', 'overdue')->count(),
            'total_amount' => $client->invoices()->sum('total_amount'),
            'paid_amount' => $client->invoices()->where('status', 'paid')->sum('total_amount'),
            'pending_amount' => $client->invoices()->where('status', 'pending')->sum('total_amount'),
        ];

        return view('client.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Display client's chat messages.
     */
    public function messages(Request $request): View
    {
        $client = Auth::guard('client')->user();
        
        $query = $client->chatMessages()->with(['user']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('message', 'like', "%{$search}%");
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $messages = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total_messages' => $client->chatMessages()->count(),
            'user_messages' => $client->chatMessages()->where('type', 'user')->count(),
            'bot_messages' => $client->chatMessages()->where('type', 'bot')->count(),
            'system_messages' => $client->chatMessages()->where('type', 'system')->count(),
        ];

        return view('client.messages.index', compact('messages', 'stats'));
    }
}