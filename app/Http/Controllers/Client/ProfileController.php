<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the client profile.
     */
    public function show()
    {
        $client = Auth::guard('client')->user();
        
        // Get client statistics
        $stats = [
            'total_documents' => $client->documents()->count(),
            'total_invoices' => $client->invoices()->count(),
            'total_messages' => $client->chatMessages()->count(),
            'member_since' => $client->created_at->format('M d, Y'),
            'last_login' => $client->last_login_at ? $client->last_login_at->format('M d, Y \a\t g:i A') : 'Never',
        ];
        
        return view('client.profile.show', compact('client', 'stats'));
    }
    
    /**
     * Show the form for editing the client profile.
     */
    public function edit()
    {
        $client = Auth::guard('client')->user();
        
        return view('client.profile.edit', compact('client'));
    }
    
    /**
     * Update the client profile.
     */
    public function update(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('clients')->ignore($client->id)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:2'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        
        try {
            $client->update($validated);
            
            return redirect()
                ->route('client.profile.show')
                ->with('success', 'Profile updated successfully.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update profile. Please try again.');
        }
    }
    
    /**
     * Show the form for changing password.
     */
    public function editPassword()
    {
        return view('client.profile.password');
    }
    
    /**
     * Update the client password.
     */
    public function updatePassword(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:client'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        
        try {
            $client->update([
                'password' => Hash::make($validated['password']),
            ]);
            
            return redirect()
                ->route('client.profile.show')
                ->with('success', 'Password updated successfully.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update password. Please try again.');
        }
    }
    
    /**
     * Show the form for notification preferences.
     */
    public function editNotifications()
    {
        $client = Auth::guard('client')->user();
        
        return view('client.profile.notifications', compact('client'));
    }
    
    /**
     * Update notification preferences.
     */
    public function updateNotifications(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $validated = $request->validate([
            'email_notifications' => ['boolean'],
            'sms_notifications' => ['boolean'],
            'invoice_notifications' => ['boolean'],
            'document_notifications' => ['boolean'],
            'message_notifications' => ['boolean'],
            'marketing_notifications' => ['boolean'],
        ]);
        
        try {
            // Store notification preferences in JSON format
            $preferences = [
                'email_notifications' => $request->boolean('email_notifications'),
                'sms_notifications' => $request->boolean('sms_notifications'),
                'invoice_notifications' => $request->boolean('invoice_notifications'),
                'document_notifications' => $request->boolean('document_notifications'),
                'message_notifications' => $request->boolean('message_notifications'),
                'marketing_notifications' => $request->boolean('marketing_notifications'),
            ];
            
            $client->update([
                'notification_preferences' => $preferences,
            ]);
            
            return redirect()
                ->route('client.profile.show')
                ->with('success', 'Notification preferences updated successfully.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update notification preferences. Please try again.');
        }
    }
    
    /**
     * Download client data (GDPR compliance).
     */
    public function downloadData()
    {
        $client = Auth::guard('client')->user();
        
        try {
            // Prepare client data
            $data = [
                'profile' => [
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'company' => $client->company,
                    'tax_id' => $client->tax_id,
                    'address' => $client->address,
                    'city' => $client->city,
                    'state' => $client->state,
                    'zip_code' => $client->zip_code,
                    'country' => $client->country,
                    'notes' => $client->notes,
                    'status' => $client->status,
                    'created_at' => $client->created_at->toISOString(),
                    'updated_at' => $client->updated_at->toISOString(),
                ],
                'documents' => $client->documents()->get()->map(function ($document) {
                    return [
                        'name' => $document->name,
                        'type' => $document->type,
                        'size' => $document->size,
                        'status' => $document->status,
                        'uploaded_at' => $document->created_at->toISOString(),
                    ];
                }),
                'invoices' => $client->invoices()->get()->map(function ($invoice) {
                    return [
                        'invoice_number' => $invoice->invoice_number,
                        'amount' => $invoice->amount,
                        'status' => $invoice->status,
                        'due_date' => $invoice->due_date->toISOString(),
                        'created_at' => $invoice->created_at->toISOString(),
                    ];
                }),
                'messages' => $client->chatMessages()->get()->map(function ($message) {
                    return [
                        'type' => $message->type,
                        'message' => $message->message,
                        'status' => $message->status,
                        'created_at' => $message->created_at->toISOString(),
                    ];
                }),
            ];
            
            $filename = 'client_data_' . $client->id . '_' . now()->format('Y-m-d') . '.json';
            
            return response()->json($data)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Type', 'application/json');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to download data. Please try again.');
        }
    }
    
    /**
     * Show account deletion confirmation.
     */
    public function deleteAccount()
    {
        return view('client.profile.delete');
    }
    
    /**
     * Delete client account (soft delete).
     */
    public function destroyAccount(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $request->validate([
            'password' => ['required', 'current_password:client'],
            'confirmation' => ['required', 'in:DELETE'],
        ]);
        
        try {
            // Soft delete the client account
            $client->update([
                'status' => 'deleted',
                'deleted_at' => now(),
            ]);
            
            // Log out the client
            Auth::guard('client')->logout();
            
            return redirect()
                ->route('client.login')
                ->with('success', 'Your account has been deleted successfully.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete account. Please try again.');
        }
    }
    
    /**
     * Get client activity log.
     */
    public function activityLog()
    {
        $client = Auth::guard('client')->user();
        
        // Get recent activities
        $activities = collect();
        
        // Add document activities
        $client->documents()->latest()->limit(10)->get()->each(function ($document) use ($activities) {
            $activities->push([
                'type' => 'document',
                'action' => 'uploaded',
                'description' => "Uploaded document: {$document->name}",
                'created_at' => $document->created_at,
                'icon' => 'fas fa-file-upload',
                'color' => 'primary',
            ]);
        });
        
        // Add invoice activities
        $client->invoices()->latest()->limit(10)->get()->each(function ($invoice) use ($activities) {
            $activities->push([
                'type' => 'invoice',
                'action' => 'created',
                'description' => "Invoice {$invoice->invoice_number} created",
                'created_at' => $invoice->created_at,
                'icon' => 'fas fa-file-invoice-dollar',
                'color' => 'success',
            ]);
        });
        
        // Add message activities
        $client->chatMessages()->latest()->limit(10)->get()->each(function ($message) use ($activities) {
            $activities->push([
                'type' => 'message',
                'action' => 'sent',
                'description' => "Message sent: " . \Str::limit($message->message, 50),
                'created_at' => $message->created_at,
                'icon' => 'fas fa-comment',
                'color' => 'info',
            ]);
        });
        
        // Sort by date and limit
        $activities = $activities->sortByDesc('created_at')->take(20);
        
        return view('client.profile.activity', compact('activities'));
    }
}