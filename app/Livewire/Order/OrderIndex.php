<?php

namespace App\Livewire\Order;

use App\Models\ServiceOrder;
use App\Models\TechnicianProfile;
use App\Services\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Daftar Order Servis', 'breadcrumbs' => [['label' => 'Order Servis']]])]
class OrderIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $technicianFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTechnicianFilter(): void
    {
        $this->resetPage();
    }

    public function render(TenantContext $tenantContext)
    {
        $technicians = TechnicianProfile::with('user')->get();

        $orders = ServiceOrder::with(['customer', 'device', 'technician'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', function ($cq) {
                            $cq->where('name', 'like', "%{$this->search}%")
                                ->orWhere('phone', 'like', "%{$this->search}%");
                        })
                        ->orWhereHas('device', function ($dq) {
                            $dq->where('brand', 'like', "%{$this->search}%")
                                ->orWhere('model', 'like', "%{$this->search}%")
                                ->orWhere('imei', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active_queue') {
                    $query->whereIn('status', ['received', 'diagnosing', 'waiting_approval', 'in_progress', 'waiting_parts', 'testing', 'pending']);
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->when($this->technicianFilter, fn ($q) => $q->where('technician_id', $this->technicianFilter))
            ->latest()
            ->paginate(12);

        $now = now();
        $thisMonthCount = ServiceOrder::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastMonthCount = ServiceOrder::whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        $monthGrowth = $lastMonthCount > 0
            ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1)
            : ($thisMonthCount > 0 ? 100 : 0);

        $completedToday = ServiceOrder::where('status', 'completed')->whereDate('completed_at', today())->count();
        $completedYesterday = ServiceOrder::where('status', 'completed')->whereDate('completed_at', today()->subDay())->count();
        $completedGrowth = $completedYesterday > 0
            ? round((($completedToday - $completedYesterday) / $completedYesterday) * 100, 1)
            : ($completedToday > 0 ? 100 : 0);

        $stats = [
            'total_active' => ServiceOrder::whereIn('status', ['received', 'diagnosing', 'waiting_approval', 'in_progress', 'waiting_parts', 'testing', 'pending'])->count(),
            'waiting_pickup' => ServiceOrder::where('status', 'ready_for_pickup')->count(),
            'completed_today' => $completedToday,
            'completed_growth' => $completedGrowth,
            'total_month' => $thisMonthCount,
            'month_growth' => $monthGrowth,
        ];

        return view('livewire.order.order-index', [
            'orders' => $orders,
            'technicians' => $technicians,
            'stats' => $stats,
        ]);
    }
}
