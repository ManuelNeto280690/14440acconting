<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt,csv,xlsx,xls',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $client = Auth::user();
            
            // Generate unique filename
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents/' . $client->id, $filename, 'local');
            
            // Create document record
            $document = Document::create([
                'client_id' => $client->id,
                'name' => $request->name ?: $file->getClientOriginalName(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'type' => $request->type ?: $this->getDocumentType($file->getMimeType()),
                'description' => $request->description,
                'status' => 'uploaded',
                'meta' => [
                    'uploaded_at' => now()->toISOString(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            ]);

            // Trigger webhook for processing (if configured)
            $this->triggerProcessingWebhook($document);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => $document->load('client')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        // Ensure the document belongs to the authenticated client
        if ($document->client_id !== Auth::id()) {
            abort(403, 'Unauthorized access to document');
        }

        return view('client.documents.show', compact('document'));
    }

    /**
     * Download the specified document.
     */
    public function download(Document $document)
    {
        // Ensure the document belongs to the authenticated client
        if ($document->client_id !== Auth::id()) {
            abort(403, 'Unauthorized access to document');
        }

        if (!Storage::exists($document->path)) {
            abort(404, 'Document file not found');
        }

        // Log download activity
        activity()
            ->performedOn($document)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'download'])
            ->log('Document downloaded');

        return Storage::download($document->path, $document->original_name);
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document)
    {
        // Ensure the document belongs to the authenticated client
        if ($document->client_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to document'
            ], 403);
        }

        try {
            // Delete file from storage
            if (Storage::exists($document->path)) {
                Storage::delete($document->path);
            }

            // Log deletion activity
            activity()
                ->performedOn($document)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Document deleted');

            // Delete database record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document type based on MIME type.
     */
    private function getDocumentType(string $mimeType): string
    {
        $typeMap = [
            'application/pdf' => 'pdf',
            'application/msword' => 'document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'document',
            'image/jpeg' => 'image',
            'image/jpg' => 'image',
            'image/png' => 'image',
            'text/plain' => 'text',
            'text/csv' => 'spreadsheet',
            'application/vnd.ms-excel' => 'spreadsheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'spreadsheet',
        ];

        return $typeMap[$mimeType] ?? 'other';
    }

    /**
     * Trigger processing webhook for the document.
     */
    private function triggerProcessingWebhook(Document $document): void
    {
        // This would typically dispatch a job to send webhook
        // For now, we'll just update the status to processing
        $document->update(['status' => 'processing']);
        
        // In a real implementation, you would:
        // dispatch(new ProcessDocumentJob($document));
    }
}