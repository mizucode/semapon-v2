<?php

namespace App\Livewire\Store;

use App\Services\TenantContext;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app', ['title' => 'Pengaturan Toko', 'breadcrumbs' => [['label' => 'Pengaturan Toko']]])]
class StoreSettings extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $receipt_header = '';

    public string $receipt_footer = '';

    public int $default_warranty_days = 30;

    public int $thermal_paper_width = 58;

    public $logo;

    public ?string $current_logo_path = null;

    public function mount(TenantContext $tenantContext): void
    {
        $store = $tenantContext->getStore();

        if ($store) {
            $this->name = $store->name;
            $this->phone = $store->phone ?? '';
            $this->email = $store->email ?? '';
            $this->address = $store->address ?? '';
            $this->current_logo_path = $store->logo_path;

            $this->receipt_header = $store->getSetting('receipt_header', $store->name);
            $this->receipt_footer = $store->getSetting('receipt_footer', 'Terima kasih atas kunjungan Anda.');
            $this->default_warranty_days = (int) $store->getSetting('default_warranty_days', 30);
            $this->thermal_paper_width = (int) $store->getSetting('thermal_paper_width', 58);
        }
    }

    public function updateStore(TenantContext $tenantContext): void
    {
        $store = $tenantContext->getStore();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'receipt_header' => ['nullable', 'string', 'max:255'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'default_warranty_days' => ['required', 'integer', 'min:0', 'max:365'],
            'thermal_paper_width' => ['required', 'in:58,80'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB Max
        ]);

        if ($this->logo) {
            // Tenant isolated storage directory: stores/{store_id}/logo.ext
            $logoPath = $this->logo->store("stores/{$store->id}", 'public');
            $store->logo_path = $logoPath;
            $this->current_logo_path = $logoPath;
        }

        $store->name = $validated['name'];
        $store->phone = $validated['phone'];
        $store->email = $validated['email'];
        $store->address = $validated['address'];

        $store->settings = array_merge($store->settings ?? [], [
            'receipt_header' => $validated['receipt_header'],
            'receipt_footer' => $validated['receipt_footer'],
            'default_warranty_days' => $validated['default_warranty_days'],
            'thermal_paper_width' => (int) $validated['thermal_paper_width'],
        ]);

        $store->save();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Pengaturan Disimpan',
            'message' => 'Profil dan preferensi toko Anda berhasil diperbarui.',
        ]);
    }

    public function render(TenantContext $tenantContext)
    {
        return view('livewire.store.store-settings', [
            'store' => $tenantContext->getStore(),
        ]);
    }
}
