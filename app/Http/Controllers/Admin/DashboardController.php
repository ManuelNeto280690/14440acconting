<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Middlewares are applied in routes (web.php) in Laravel 11


    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get basic statistics
        $stats = $this->getBasicStats();
        
        // Get chart data
        $chartData = $this->getChartData();
        
        // Get recent activities
        $recentTenants = $this->getRecentTenants();
        $recentSubscriptions = $this->getRecentSubscriptions();
        
        // Get revenue data
        $revenueData = $this->getRevenueData();
        
        // Get subscription status breakdown
        $subscriptionStats = $this->getSubscriptionStats();
        
        // Get plan popularity
        $planStats = $this->getPlanStats();

        return view('admin.dashboard', compact(
            'stats',
            'chartData',
            'recentTenants',
            'recentSubscriptions',
            'revenueData',
            'subscriptionStats',
            'planStats'
        ));
    }

    /**
     * Get basic dashboard statistics.
     */
    private function getBasicStats(): array
    {
        return [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::whereJsonContains('data->status', 'active')->count(),
            'total_plans' => Plan::count(),
            'active_plans' => Plan::where('is_active', true)->count(),
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'total_users' => User::count(),
            'monthly_revenue' => Subscription::where('status', 'active')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];
    }

    /**
     * Get chart data for dashboard graphs.
     */
    private function getChartData(): array
    {
        // Get tenant growth over last 12 months
        $tenantGrowth = [];
        $subscriptionGrowth = [];
        $revenueGrowth = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');

            // Tenant growth
            $tenantGrowth[] = [
                'month' => $monthLabel,
                'count' => Tenant::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count()
            ];

            // Subscription growth
            $subscriptionGrowth[] = [
                'month' => $monthLabel,
                'count' => Subscription::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count()
            ];

            // Revenue growth
            $revenueGrowth[] = [
                'month' => $monthLabel,
                'amount' => Subscription::where('status', 'active')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount')
            ];
        }

        return [
            'tenant_growth' => $tenantGrowth,
            'subscription_growth' => $subscriptionGrowth,
            'revenue_growth' => $revenueGrowth,
        ];
    }

    /**
     * Get recent tenants.
     */
    private function getRecentTenants()
    {
        return Tenant::with(['subscription.plan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get recent subscriptions.
     */
    private function getRecentSubscriptions()
    {
        return Subscription::with(['tenant', 'plan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get revenue data.
     */
    private function getRevenueData(): array
    {
        $currentMonth = now();
        $lastMonth = now()->subMonth();

        $currentMonthRevenue = Subscription::where('status', 'active')
            ->whereYear('created_at', $currentMonth->year)
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');

        $lastMonthRevenue = Subscription::where('status', 'active')
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('amount');

        $totalRevenue = Subscription::where('status', 'active')->sum('amount');
        $averageRevenue = Subscription::where('status', 'active')->avg('amount');

        // Calculate growth percentage
        $growthPercentage = 0;
        if ($lastMonthRevenue > 0) {
            $growthPercentage = (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        }

        return [
            'current_month' => $currentMonthRevenue,
            'last_month' => $lastMonthRevenue,
            'total' => $totalRevenue,
            'average' => $averageRevenue,
            'growth_percentage' => round($growthPercentage, 2),
        ];
    }

    /**
     * Get subscription statistics.
     */
    private function getSubscriptionStats(): array
    {
        return [
            'active' => Subscription::where('status', 'active')->count(),
            'canceled' => Subscription::where('status', 'canceled')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'pending' => Subscription::where('status', 'pending')->count(),
            'ending_soon' => Subscription::endingSoon(7)->count(),
            'on_trial' => Subscription::whereNotNull('trial_ends_at')
                                        ->where('trial_ends_at', '>', now())
                                        ->count(),
        ];
    }

    /**
     * Get plan statistics.
     */
    private function getPlanStats()
    {
        return Plan::withCount(['subscriptions', 'activeSubscriptions'])
            ->with(['subscriptions' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('active_subscriptions_count', 'desc')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->formatted_price,
                    'total_subscriptions' => $plan->subscriptions_count,
                    'active_subscriptions' => $plan->active_subscriptions_count,
                    'revenue' => $plan->subscriptions->sum('amount'),
                    'is_popular' => $plan->is_popular,
                ];
            });
    }

    /**
     * Get system health data.
     */
    private function getSystemHealth(): array
    {
        return [
            'database_status' => $this->checkDatabaseConnection(),
            'cache_status' => $this->checkCacheConnection(),
            'storage_usage' => $this->getStorageUsage(),
            'memory_usage' => $this->getMemoryUsage(),
        ];
    }

    /**
     * Check database connection.
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache connection.
     */
    private function checkCacheConnection(): bool
    {
        try {
            cache()->put('health_check', 'ok', 1);
            return cache()->get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get storage usage information.
     */
    private function getStorageUsage(): array
    {
        $storagePath = storage_path();
        $totalBytes = disk_total_space($storagePath);
        $freeBytes = disk_free_space($storagePath);
        $usedBytes = $totalBytes - $freeBytes;

        return [
            'total' => $this->formatBytes($totalBytes),
            'used' => $this->formatBytes($usedBytes),
            'free' => $this->formatBytes($freeBytes),
            'percentage' => round(($usedBytes / $totalBytes) * 100, 2),
        ];
    }

    /**
     * Get memory usage information.
     */
    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');

        return [
            'current' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes($memoryPeak),
            'limit' => $memoryLimit,
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
