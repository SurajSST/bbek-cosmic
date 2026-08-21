<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\UploadSo;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with real system metrics and operational data.
     */
    public function index(): View
    {
        // Cache high-level counts for 30 seconds to make page loads instant
        $stats = Cache::remember('admin_dashboard_metrics', 30, function () {
            $totalSalesOrders = SalesOrder::count();
            $totalRevenue = SalesOrderItem::sum('total_price') ?? 0;
            $pendingOrders = SalesOrder::where('billed_status', 'pending')->count();
            $paidOrders = SalesOrder::where('billed_status', 'paid')->count();
            $billedOrders = SalesOrder::where('billed_status', 'billed')->count();

            $totalBills = Bill::count();
            $totalUploadSos = UploadSo::count();

            $totalUsers = User::count();
            $activeUsers = User::where('status', 'active')->count();
            $totalRoles = Role::count();
            $totalPermissions = Permission::count();

            return [
                'total_sales_orders' => $totalSalesOrders,
                'total_revenue' => $totalRevenue,
                'pending_orders' => $pendingOrders,
                'paid_orders' => $paidOrders,
                'billed_orders' => $billedOrders,
                'total_bills' => $totalBills,
                'total_upload_sos' => $totalUploadSos,
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_roles' => $totalRoles,
                'total_permissions' => $totalPermissions,
            ];
        });

        // Recent Sales Orders with items & creator
        $recentOrders = SalesOrder::with(['items', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        // Recent Activity Logs
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentActivities'));
    }
}
