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
                    $query->whereIn('status', ['received', 'checking', 'waiting_approval', 'waiting_parts', 'in_progress', 'testing']);
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->when($this->technicianFilter, fn ($q) => $q->where('technician_id', $this->technicianFilter))
            ->latest()
            ->paginate(12);

        $stats = [
            'total_active' => ServiceOrder::whereIn('status', ['received', 'checking', 'waiting_approval', 'waiting_parts', 'in_progress', 'testing'])->count(),
            'waiting_pickup' => ServiceOrder::where('status', 'ready_for_pickup')->count(),
            'completed_today' => ServiceOrder::where('status', 'picked_up')->whereDate('picked_up_at', today())->count(),
        ];

        return view('livewire.order.order-index', [
            'orders' => $orders,
            'technicians' => $technicians,
            'stats' => $stats,
        ]);
    }
}
