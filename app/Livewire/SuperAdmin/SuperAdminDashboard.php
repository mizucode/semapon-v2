<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Plan;
use App\Models\Store;
use App\Services\SuperAdminService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SuperAdminDashboard extends Component
{
    use WithPagination;

    public string $search = '';

    public string $planFilter = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPlanFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleSuspend(string $storeId, SuperAdminService $adminService): void
    {
        $store = Store::withoutGlobalScopes()->findOrFail($storeId);

        if ($store->status === 'suspended') {
            $adminService->reactivateStore($store);
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Tenant Diaktifkan',
                'message' => "Toko '{$store->name}' berhasil diaktifkan kembali.",
            ]);
        } else {
            $adminService->suspendStore($store, 'Ditangguhkan oleh Super Admin');
            $this->dispatch('toast', [
                'type' => 'info',
                'title' => 'Tenant Ditangguhkan',
                'message' => "Toko '{$store->name}' telah dinonaktifkan.",
            ]);
        }
    }

    public function render(SuperAdminService $adminService)
    {
        $overview = $adminService->getPlatformOverview();

        $query = Store::withoutGlobalScopes()->with(['owner']);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%")
                    ->orWhereHas('owner', function ($oq) {
                        $oq->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    });
            });
        }

        if (! empty($this->planFilter)) {
            $query->where('plan', $this->planFilter);
        }

        if (! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $tenants = $query->latest()->paginate(15);
        $plans = Plan::all();

        return view('livewire.super-admin.super-admin-dashboard', [
            'overview' => $overview,
            'tenants' => $tenants,
            'plans' => $plans,
        ])->layoutData([
            'title' => 'Super Admin Platform Control',
            'breadcrumbs' => [
                ['label' => 'Super Admin Platform'],
            ],
        ]);
    }
}
