<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IntegrationController extends Controller
{
    /**
     * Display a listing of integrations.
     */
    public function index(Request $request): View
    {
        $query = Integration::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('service_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by service
        if ($request->filled('service')) {
            $query->where('service_name', $request->get('service'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'with_errors') {
                $query->whereNotNull('error_message');
            }
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $integrations = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => Integration::count(),
            'active' => Integration::active()->count(),
            'inactive' => Integration::where('is_active', false)->count(),
            'with_errors' => Integration::withErrors()->count(),
            'syncing' => Integration::where('sync_status', Integration::SYNC_STATUS_SYNCING)->count(),
        ];

        // Get service types for the filter dropdown
        $serviceTypes = Integration::getServiceTypes();

        return view('tenant.integrations.index', compact('integrations', 'stats', 'serviceTypes'));
    }

    /**
     * Show the form for creating a new integration.
     */
    public function create(): View
    {
        $serviceTypes = Integration::getServiceTypes();
        return view('tenant.integrations.create', compact('serviceTypes'));
    }

    /**
     * Store a newly created integration.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'service_name' => 'required|string|in:' . implode(',', array_keys(Integration::getServiceTypes())),
            'description' => 'nullable|string|max:1000',
            'api_key' => 'required|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'webhook_url' => 'required|url|max:500',
            'webhook_secret' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'max_retries' => 'nullable|integer|min:1|max:10',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:1000',
            'timeout_seconds' => 'nullable|integer|min:5|max:300',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $integration = Integration::create([
                'name' => $request->name,
                'service_name' => $request->service_name,
                'description' => $request->description,
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret,
                'webhook_url' => $request->webhook_url,
                'webhook_secret' => $request->webhook_secret,
                'settings' => $request->settings ?? [],
                'is_active' => $request->boolean('is_active', true),
                'max_retries' => $request->max_retries ?? 3,
                'rate_limit_per_minute' => $request->rate_limit_per_minute ?? 60,
                'timeout_seconds' => $request->timeout_seconds ?? 30,
                'sync_status' => Integration::SYNC_STATUS_IDLE,
            ]);

            Log::info('Integration created', [
                'integration_id' => $integration->id,
                'service_name' => $integration->service_name,
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->route('tenant.integrations.show', $integration)
                ->with('success', 'Integration created successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to create integration', [
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create integration. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified integration.
     */
    public function show(Integration $integration): View
    {
        $integration->load(['documents', 'invoices']); // If relationships exist

        // Get recent activity/logs for this integration
        $recentActivity = $this->getRecentActivity($integration);

        return view('tenant.integrations.show', compact('integration', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified integration.
     */
    public function edit(Integration $integration): View
    {
        $serviceTypes = Integration::getServiceTypes();
        return view('tenant.integrations.edit', compact('integration', 'serviceTypes'));
    }

    /**
     * Update the specified integration.
     */
    public function update(Request $request, Integration $integration): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'service_name' => 'required|string|in:' . implode(',', array_keys(Integration::getServiceTypes())),
            'description' => 'nullable|string|max:1000',
            'api_key' => 'nullable|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'webhook_url' => 'required|url|max:500',
            'webhook_secret' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'max_retries' => 'nullable|integer|min:1|max:10',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:1000',
            'timeout_seconds' => 'nullable|integer|min:5|max:300',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $updateData = [
                'name' => $request->name,
                'service_name' => $request->service_name,
                'description' => $request->description,
                'webhook_url' => $request->webhook_url,
                'webhook_secret' => $request->webhook_secret,
                'settings' => $request->settings ?? [],
                'is_active' => $request->boolean('is_active', true),
                'max_retries' => $request->max_retries ?? 3,
                'rate_limit_per_minute' => $request->rate_limit_per_minute ?? 60,
                'timeout_seconds' => $request->timeout_seconds ?? 30,
            ];

            // Only update API credentials if provided
            if ($request->filled('api_key')) {
                $updateData['api_key'] = $request->api_key;
            }
            if ($request->filled('api_secret')) {
                $updateData['api_secret'] = $request->api_secret;
            }

            $integration->update($updateData);

            Log::info('Integration updated', [
                'integration_id' => $integration->id,
                'service_name' => $integration->service_name,
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->route('tenant.integrations.show', $integration)
                ->with('success', 'Integration updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update integration', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update integration. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified integration.
     */
    public function destroy(Integration $integration): RedirectResponse
    {
        try {
            $integrationName = $integration->name;
            $integration->delete();

            Log::info('Integration deleted', [
                'integration_name' => $integrationName,
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->route('tenant.integrations.index')
                ->with('success', "Integration '{$integrationName}' deleted successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to delete integration', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete integration. Please try again.');
        }
    }

    /**
     * Test integration connection.
     */
    public function testConnection(Integration $integration): JsonResponse
    {
        try {
            $isConnected = $integration->testConnection();

            if ($isConnected) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection test successful!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed. Please check your configuration.',
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Integration connection test failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle integration active status.
     */
    public function toggleStatus(Integration $integration): JsonResponse
    {
        try {
            $integration->update(['is_active' => !$integration->is_active]);

            $status = $integration->is_active ? 'activated' : 'deactivated';

            Log::info("Integration {$status}", [
                'integration_id' => $integration->id,
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Integration {$status} successfully!",
                'is_active' => $integration->is_active,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to toggle integration status', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update integration status.',
            ], 500);
        }
    }

    /**
     * Force sync integration.
     */
    public function forceSync(Integration $integration): JsonResponse
    {
        try {
            if ($integration->isSyncing()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integration is already syncing.',
                ], 400);
            }

            $integration->markSyncStarted();

            // Here you would dispatch a job to handle the actual sync
            // dispatch(new SyncIntegrationJob($integration));

            Log::info('Integration sync started', [
                'integration_id' => $integration->id,
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sync started successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start integration sync', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start sync.',
            ], 500);
        }
    }

    /**
     * Reset retry count for failed integration.
     */
    public function resetRetry(Integration $integration): JsonResponse
    {
        try {
            $integration->resetRetryCount();

            Log::info('Integration retry count reset', [
                'integration_id' => $integration->id,
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Retry count reset successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reset integration retry count', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset retry count.',
            ], 500);
        }
    }

    /**
     * Get integration statistics.
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => Integration::count(),
                'active' => Integration::active()->count(),
                'inactive' => Integration::where('is_active', false)->count(),
                'with_errors' => Integration::withErrors()->count(),
                'syncing' => Integration::where('sync_status', Integration::SYNC_STATUS_SYNCING)->count(),
                'by_service' => Integration::selectRaw('service_name, count(*) as count')
                    ->groupBy('service_name')
                    ->pluck('count', 'service_name'),
                'by_status' => Integration::selectRaw('sync_status, count(*) as count')
                    ->groupBy('sync_status')
                    ->pluck('count', 'sync_status'),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Failed to get integration stats', [
                'error' => $e->getMessage(),
                'tenant_id' => tenant('id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics.',
            ], 500);
        }
    }

    /**
     * Get recent activity for an integration.
     */
    private function getRecentActivity(Integration $integration): array
    {
        // This would typically fetch from a logs table or activity log
        // For now, return mock data
        return [
            [
                'action' => 'Sync completed',
                'status' => 'success',
                'timestamp' => $integration->last_sync_at,
                'details' => 'Successfully synced 15 documents',
            ],
            [
                'action' => 'Configuration updated',
                'status' => 'info',
                'timestamp' => $integration->updated_at,
                'details' => 'Webhook URL updated',
            ],
        ];
    }
}
