<?php

namespace App\Livewire\Technician;

use App\Models\StoreMembership;
use App\Models\TechnicianProfile;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Manajemen Teknisi', 'breadcrumbs' => [['label' => 'Teknisi']]])]
class TechnicianIndex extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showResetPasswordModal = false;

    public ?string $editingTechnicianId = null;

    // Form fields
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $specialization = 'All-Round';

    public string $commission_type = 'percentage'; // percentage, fixed, profit_split

    public float $commission_rate = 30.0;

    public string $notes = '';

    public string $new_password = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'specialization' => ['required', 'string', 'max:100'],
            'commission_type' => ['required', 'in:percentage,fixed,profit_split'],
            'commission_rate' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function openCreateModal(TenantContext $tenantContext): void
    {
        $store = $tenantContext->getStore();

        if (! $store->canAddTechnician()) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Batas Teknisi Tercapai',
                'message' => "Paket {$store->plan} Anda hanya mengizinkan maksimal {$store->getTechnicianLimit()} teknisi aktif. Silakan upgrade paket langganan Anda.",
            ]);

            return;
        }

        $this->reset(['name', 'email', 'phone', 'password', 'notes', 'editingTechnicianId']);
        $this->specialization = 'All-Round';
        $this->commission_type = 'percentage';
        $this->commission_rate = 30.0;
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function createTechnician(TenantContext $tenantContext): void
    {
        $store = $tenantContext->getStore();

        if (! $store->canAddTechnician()) {
            $this->addError('general', 'Batas teknisi aktif untuk paket toko Anda telah tercapai.');

            return;
        }

        $validated = $this->validate();

        DB::transaction(function () use ($store, $validated) {
            // Find or create user
            $user = User::where('email', $validated['email'])->first();

            if (! $user) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => $validated['password'],
                    'status' => 'active',
                ]);
            }

            // Verify membership in this store
            $membership = StoreMembership::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => 'technician',
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );

            // Create or update technician profile
            TechnicianProfile::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'user_id' => $user->id,
                ],
                [
                    'membership_id' => $membership->id,
                    'specialization' => $validated['specialization'],
                    'commission_type' => $validated['commission_type'],
                    'commission_rate' => $validated['commission_rate'],
                    'notes' => $validated['notes'] ?: null,
                ]
            );
        });

        $this->showCreateModal = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Teknisi Ditambahkan',
            'message' => "Akun teknisi {$validated['name']} berhasil dibuat dan siap bertugas.",
        ]);
    }

    public function editTechnician(string $id): void
    {
        $tech = TechnicianProfile::with('user', 'membership')->findOrFail($id);
        $this->editingTechnicianId = $tech->id;
        $this->name = $tech->user->name;
        $this->email = $tech->user->email;
        $this->phone = $tech->user->phone ?? '';
        $this->specialization = $tech->specialization ?? 'All-Round';
        $this->commission_type = $tech->commission_type;
        $this->commission_rate = (float) $tech->commission_rate;
        $this->notes = $tech->notes ?? '';

        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function updateTechnician(): void
    {
        $tech = TechnicianProfile::with('user')->findOrFail($this->editingTechnicianId);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['required', 'string', 'max:100'],
            'commission_type' => ['required', 'in:percentage,fixed,profit_split'],
            'commission_rate' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $tech->user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        $tech->update([
            'specialization' => $validated['specialization'],
            'commission_type' => $validated['commission_type'],
            'commission_rate' => $validated['commission_rate'],
            'notes' => $validated['notes'] ?: null,
        ]);

        $this->showEditModal = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Teknisi Diperbarui',
            'message' => 'Data dan skema komisi teknisi berhasil disimpan.',
        ]);
    }

    public function toggleStatus(string $id): void
    {
        $tech = TechnicianProfile::with('membership')->findOrFail($id);
        $newStatus = $tech->membership->status === 'active' ? 'inactive' : 'active';

        $tech->membership->update(['status' => $newStatus]);

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Status Teknisi Berubah',
            'message' => 'Status keanggotaan teknisi kini '.($newStatus === 'active' ? 'Aktif' : 'Nonaktif').'.',
        ]);
    }

    public function openResetPasswordModal(string $id): void
    {
        $this->editingTechnicianId = $id;
        $this->new_password = '';
        $this->resetValidation();
        $this->showResetPasswordModal = true;
    }

    public function resetPassword(): void
    {
        $this->validate([
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        $tech = TechnicianProfile::with('user')->findOrFail($this->editingTechnicianId);
        $tech->user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->showResetPasswordModal = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Kata Sandi Direset',
            'message' => "Kata sandi untuk teknisi {$tech->user->name} berhasil diperbarui.",
        ]);
    }

    public function render(TenantContext $tenantContext)
    {
        $store = $tenantContext->getStore();
        $technicians = TechnicianProfile::with('user', 'membership')->latest()->get();
        $activeCount = $store ? $store->activeTechniciansCount() : 0;
        $limit = $store ? $store->getTechnicianLimit() : 1;

        return view('livewire.technician.technician-index', [
            'technicians' => $technicians,
            'activeCount' => $activeCount,
            'limit' => $limit,
            'canAdd' => $store ? $store->canAddTechnician() : false,
        ]);
    }
}
