<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CustomerDetail extends Component
{
    public Customer $customer;

    public bool $showAddDeviceModal = false;

    // Device Form Fields
    public string $brand = '';

    public string $model = '';

    public string $color = '';

    public string $imei = '';

    public string $passcode = '';

    public string $device_notes = '';

    public function mount(string $id): void
    {
        $this->customer = Customer::with('devices')->findOrFail($id);
    }

    protected function rules(): array
    {
        return [
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'imei' => ['nullable', 'string', 'max:30'],
            'passcode' => ['nullable', 'string', 'max:100'],
            'device_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function openAddDeviceModal(): void
    {
        $this->reset(['brand', 'model', 'color', 'imei', 'passcode', 'device_notes']);
        $this->resetValidation();
        $this->showAddDeviceModal = true;
    }

    public function addDevice(): void
    {
        $this->validate();

        $device = $this->customer->devices()->create([
            'store_id' => $this->customer->store_id,
            'brand' => $this->brand,
            'model' => $this->model,
            'color' => $this->color ?: null,
            'imei' => $this->imei ?: null,
            'passcode' => $this->passcode ?: null,
            'notes' => $this->device_notes ?: null,
        ]);

        $this->showAddDeviceModal = false;
        $this->customer->load('devices');

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Perangkat Ditambahkan',
            'message' => "Perangkat {$device->device_name} berhasil ditautkan ke pelanggan.",
        ]);
    }

    public function deleteDevice(string $deviceId): void
    {
        $device = $this->customer->devices()->findOrFail($deviceId);
        $device->delete();

        $this->customer->load('devices');

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Perangkat Dihapus',
            'message' => 'Data perangkat berhasil dihapus dari profil pelanggan.',
        ]);
    }

    public function render()
    {
        return view('livewire.customer.customer-detail')
            ->layoutData([
                'title' => "Detail Pelanggan — {$this->customer->name}",
                'breadcrumbs' => [
                    ['label' => 'Pelanggan', 'url' => route('customers.index')],
                    ['label' => $this->customer->name],
                ],
            ]);
    }
}
