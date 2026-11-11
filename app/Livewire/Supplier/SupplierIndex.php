<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier;
use App\Services\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SupplierIndex extends Component
{
    use WithPagination;

    public string $search = '';

    // Modal state
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $editingSupplierId = null;

    // Form fields
    public string $name = '';

    public string $contact_person = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'contact_person', 'phone', 'email', 'address', 'editingSupplierId']);
        $this->isEditing = false;
        $this->is_active = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->editingSupplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->contact_person = $supplier->contact_person ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->email = $supplier->email ?? '';
        $this->address = $supplier->address ?? '';
        $this->is_active = $supplier->is_active;

        $this->isEditing = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveSupplier(TenantContext $tenantContext): void
    {
        $this->validate();

        $store = $tenantContext->getStore();

        if ($this->isEditing && $this->editingSupplierId) {
            $supplier = Supplier::findOrFail($this->editingSupplierId);
            $supplier->update([
                'name' => $this->name,
                'contact_person' => $this->contact_person ?: null,
                'phone' => $this->phone ?: null,
                'email' => $this->email ?: null,
                'address' => $this->address ?: null,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Supplier Diperbarui',
                'message' => "Data supplier '{$this->name}' berhasil disimpan.",
            ]);
        } else {
            Supplier::create([
                'store_id' => $store->id,
                'name' => $this->name,
                'contact_person' => $this->contact_person ?: null,
                'phone' => $this->phone ?: null,
                'email' => $this->email ?: null,
                'address' => $this->address ?: null,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Supplier Ditambahkan',
                'message' => "Supplier '{$this->name}' berhasil didaftarkan.",
            ]);
        }

        $this->showModal = false;
    }

    public function render()
    {
        $query = Supplier::query();

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('contact_person', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        $suppliers = $query->latest()->paginate(15);

        return view('livewire.supplier.supplier-index', [
            'suppliers' => $suppliers,
        ])->layoutData([
            'title' => 'Data Supplier & Pemasok',
            'breadcrumbs' => [
                ['label' => 'Supplier & Pembelian'],
            ],
        ]);
    }
}
