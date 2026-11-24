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

    public string $store_code = '';

    public int $default_warranty_days = 30;

    public string $warranty_terms = '';

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
            $this->store_code = $store->getStoreCode();

            $this->receipt_header = $store->getSetting('receipt_header', $store->name);
            $this->receipt_footer = $store->getSetting('receipt_footer', 'Terima kasih atas kunjungan Anda.');
            $this->default_warranty_days = (int) $store->getSetting('default_warranty_days', 30);
            $this->warranty_terms = (string) $store->getSetting('warranty_terms', "1. Garansi berlaku sejak tanggal pengambilan unit sesuai masa yang tertera pada nota.\n2. Garansi hanya mencakup sparepart dan jenis perbaikan yang sama.\n3. Segel garansi toko wajib dalam keadaan utuh (tidak rusak, robek, atau pernah dibongkar).\n4. Garansi tidak berlaku jika terjadi kelalaian pemakaian (terkena air/cairan, jatuh, pecah, terbakar, atau korsleting).\n5. Wajib membawa nota servis fisik atau menunjukkan bukti tracking resmi saat klaim garansi.");
            $this->thermal_paper_width = (int) $store->getSetting('thermal_paper_width', 58);
        }
    }

    public function updateStore(TenantContext $tenantContext): void
    {
        $store = $tenantContext->getStore();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'store_code' => ['required', 'string', 'min:2', 'max:8', 'regex:/^[A-Za-z0-9]+$/'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'receipt_header' => ['nullable', 'string', 'max:255'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'default_warranty_days' => ['required', 'integer', 'min:0', 'max:365'],
            'warranty_terms' => ['nullable', 'string', 'max:2000'],
            'thermal_paper_width' => ['required', 'in:58,80'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB Max
        ], [
            'store_code.regex' => 'Kode toko hanya boleh berisi huruf dan angka tanpa spasi atau simbol.',
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
            'store_code' => strtoupper($validated['store_code']),
            'receipt_header' => $validated['receipt_header'],
            'receipt_footer' => $validated['receipt_footer'],
            'default_warranty_days' => (int) $validated['default_warranty_days'],
            'warranty_terms' => $validated['warranty_terms'] ?? '',
            'thermal_paper_width' => (int) $validated['thermal_paper_width'],
        ]);

        $store->save();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Pengaturan Disimpan',
            'message' => 'Profil dan preferensi toko Anda berhasil diperbarui.',
        ]);
    }

    public function loadDefaultWarrantyTerms(): void
    {
        $this->warranty_terms = "1. Garansi berlaku sejak tanggal pengambilan unit sesuai masa yang tertera pada nota.\n2. Garansi hanya mencakup sparepart dan jenis perbaikan yang sama.\n3. Segel garansi toko wajib dalam keadaan utuh (tidak rusak, robek, atau pernah dibongkar).\n4. Garansi tidak berlaku jika terjadi kelalaian pemakaian (terkena air/cairan, jatuh, pecah, terbakar, atau korsleting).\n5. Wajib membawa nota servis fisik atau menunjukkan bukti tracking resmi saat klaim garansi.";
    }

    public function render(TenantContext $tenantContext)
    {
        return view('livewire.store.store-settings', [
            'store' => $tenantContext->getStore(),
        ]);
    }
}
