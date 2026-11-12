<?php

namespace App\Livewire\Dashboard;

use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceOrder;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TenantDashboard extends Component
{
    public function render(): View
    {
        $context = app(TenantContext::class);
        $store = $context->getStore();
        $today = Carbon::today();

        $activeOrdersCount = ServiceOrder::whereIn('status', [
            'received', 'diagnosing', 'waiting_approval', 'in_progress', 'waiting_parts', 'testing', 'pending',
        ])->count();

        $readyOrdersCount = ServiceOrder::where('status', 'ready_for_pickup')->count();

        $completedTodayCount = ServiceOrder::where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->count();

        $revenueToday = Payment::where('status', 'success')
            ->whereDate('created_at', $today)
            ->sum('amount');

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        $overdueOrdersCount = ServiceOrder::whereNotIn('status', ['completed', 'cancelled', 'unrepairable'])
            ->whereNotNull('estimated_completion_at')
            ->where('estimated_completion_at', '<', now())
            ->count();

        $recentOrders = ServiceOrder::with(['customer', 'device', 'technician'])
            ->latest()
            ->take(6)
            ->get();

        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->take(5)
            ->get();

        return view('livewire.dashboard.tenant-dashboard', [
            'store' => $store,
            'activeOrdersCount' => $activeOrdersCount,
            'readyOrdersCount' => $readyOrdersCount,
            'completedTodayCount' => $completedTodayCount,
            'revenueToday' => (float) $revenueToday,
            'lowStockCount' => $lowStockCount,
            'overdueOrdersCount' => $overdueOrdersCount,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
