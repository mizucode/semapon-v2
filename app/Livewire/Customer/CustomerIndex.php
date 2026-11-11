<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Manajemen Pelanggan', 'breadcrumbs' => [['label' => 'Pelanggan']]])]
class CustomerIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?string $editingCustomerId = null;

    // Form Fields
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'phone', 'email', 'address', 'notes', 'editingCustomerId']);
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function createCustomer(): void
    {
        $validated = $this->validate();

        $customer = Customer::create($validated);

        $this->showCreateModal = false;
        $this->reset(['name', 'phone', 'email', 'address', 'notes']);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Pelanggan Ditambahkan',
            'message' => "Pelanggan {$customer->name} berhasil didaftarkan.",
        ]);
    }

    public function editCustomer(string $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->editingCustomerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email ?? '';
        $this->address = $customer->address ?? '';
        $this->notes = $customer->notes ?? '';

        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function updateCustomer(): void
    {
        $validated = $this->validate();

        $customer = Customer::findOrFail($this->editingCustomerId);
        $customer->update($validated);

        $this->showEditModal = false;
        $this->reset(['name', 'phone', 'email', 'address', 'notes', 'editingCustomerId']);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Pelanggan Diperbarui',
            'message' => 'Data pelanggan berhasil diperbarui.',
        ]);
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->withCount('devices')
            ->latest()
            ->paginate(12);

        $totalCustomers = Customer::count();

        return view('livewire.customer.customer-index', [
            'customers' => $customers,
            'totalCustomers' => $totalCustomers,
        ]);
    }
}
