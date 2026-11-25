<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\TechnicianProfile;
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
        $isTechnician = $context->isTechnician();
        $userId = auth()->id();
        $today = Carbon::today();

        $techProfile = $isTechnician
            ? TechnicianProfile::where('user_id', $userId)->first()
            : null;
        $hasCommission = $techProfile?->hasCommission() ?? false;

        $orderQuery = fn () => ServiceOrder::when($isTechnician, fn ($query) => $query->where('technician_id', $userId));

        $completedTodayScope = fn ($query) => $query->where('status', 'completed')
            ->where(function ($q) use ($today) {
                $q->whereDate('completed_at', $today)
                    ->orWhere(function ($sub) use ($today) {
                        $sub->whereNull('completed_at')->whereDate('updated_at', $today);
                    });
            });

        $completedMonthScope = fn ($query) => $query->where('status', 'completed')
            ->where(function ($q) use ($today) {
                $q->where(function ($sub) use ($today) {
                    $sub->whereMonth('completed_at', $today->month)
                        ->whereYear('completed_at', $today->year);
                })->orWhere(function ($sub) use ($today) {
                    $sub->whereNull('completed_at')
                        ->whereMonth('updated_at', $today->month)
                        ->whereYear('updated_at', $today->year);
                });
            });

        $activeOrdersCount = $orderQuery()->whereIn('status', [
            'received', 'diagnosing', 'waiting_approval', 'in_progress', 'waiting_parts', 'testing', 'pending',
        ])->count();

        $waitingActionCount = $isTechnician
            ? $orderQuery()->whereIn('status', ['received', 'diagnosing', 'waiting_approval', 'waiting_parts'])->count()
            : 0;

        $readyOrdersCount = $orderQuery()->where('status', 'ready_for_pickup')->count();

        $completedTodayCount = $completedTodayScope($orderQuery())->count();

        $completedMonthCount = $isTechnician
            ? $completedMonthScope($orderQuery())->count()
            : 0;

        // Owner & Admin: pendapatan dihitung dari order servis yang sudah selesai hari ini
        $revenueToday = $isTechnician
            ? 0.0
            : (float) $completedTodayScope(ServiceOrder::query())->sum('total_cost');

        // Teknisi Komisi: komisi dihitung dari order servis miliknya yang sudah selesai (Teknisi Karyawan = tanpa pendapatan)
        $commissionToday = 0.0;
        $commissionMonth = 0.0;
        if ($isTechnician && $hasCommission && $techProfile) {
            $commissionToday = (float) $completedTodayScope($orderQuery())
                ->get()
                ->sum(fn (ServiceOrder $order) => $techProfile->calculateOrderCommission($order));

            $commissionMonth = (float) $completedMonthScope($orderQuery())
                ->get()
                ->sum(fn (ServiceOrder $order) => $techProfile->calculateOrderCommission($order));
        }

        $lowStockCount = $isTechnician
            ? 0
            : Product::where('is_active', true)
                ->whereColumn('stock', '<=', 'min_stock')
                ->count();

        $overdueOrdersCount = $orderQuery()->whereNotIn('status', ['completed', 'cancelled', 'unrepairable'])
            ->whereNotNull('estimated_completion_at')
            ->where('estimated_completion_at', '<', now())
            ->count();

        $recentOrders = $orderQuery()->with(['customer', 'device', 'technician'])
            ->latest()
            ->take($isTechnician ? 8 : 6)
            ->get();

        $lowStockProducts = $isTechnician
            ? collect()
            : Product::where('is_active', true)
                ->whereColumn('stock', '<=', 'min_stock')
                ->take(5)
                ->get();

        return view('livewire.dashboard.tenant-dashboard', [
            'store' => $store,
            'isTechnician' => $isTechnician,
            'hasCommission' => $hasCommission,
            'activeOrdersCount' => $activeOrdersCount,
            'waitingActionCount' => $waitingActionCount,
            'readyOrdersCount' => $readyOrdersCount,
            'completedTodayCount' => $completedTodayCount,
            'completedMonthCount' => $completedMonthCount,
            'revenueToday' => $revenueToday,
            'commissionToday' => $commissionToday,
            'commissionMonth' => $commissionMonth,
            'lowStockCount' => $lowStockCount,
            'overdueOrdersCount' => $overdueOrdersCount,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
