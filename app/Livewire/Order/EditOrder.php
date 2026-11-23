<?php

namespace App\Livewire\Order;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\DeviceCatalog;
use App\Models\ServiceOrder;
use App\Services\ImageOptimizer;
use App\Services\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class EditOrder extends Component
{
    use WithFileUploads;

    public ServiceOrder $order;

    // Photos
    public $camera_photo;

    public $gallery_photos = [];

    public array $device_photos = [];

    public array $existing_photos = [];

    // Customer Selection / Creation
    public ?string $customer_id = null;

    public string $customer_name = '';

    public string $customer_phone = '';

    public string $customer_email = '';

    public string $customer_address = '';

    public bool $is_new_customer = false;

    public string $customer_mode = 'existing'; // 'existing' or 'new'

    public string $customer_search = '';

    // Device Selection / Creation
    public ?string $customer_device_id = null;

    public ?string $selected_catalog_id = null;

    public string $device_catalog_search = '';

    public bool $show_catalog_dropdown = false;

    public array $suggested_colors = [];

    public array $suggested_variants = [];

    public string $device_brand = '';

    public string $device_model = '';

    public string $device_color = '';

    public string $device_variant = '';

    public string $device_specs = '';

    public string $device_imei = '';

    public string $passcode = '';

    // Complaints & Conditions
    public string $complaints = '';

    public string $accessories_included = 'Unit HP Saja';

    public array $physical_condition = [
        'screen_condition' => 'Normal',
        'lcd_condition' => 'Normal',
        'body_condition' => 'Mulus',
        'camera_condition' => 'Normal',
        'buttons_condition' => 'Normal',
        'charging_condition' => 'Normal',
    ];

    public function mount(string $id): void
    {
        $this->order = ServiceOrder::with(['customer', 'device'])->findOrFail($id);

        if ($this->order->customer) {
            $this->customer_id = $this->order->customer->id;
            $this->customer_name = $this->order->customer->name;
            $this->customer_phone = $this->order->customer->phone;
            $this->customer_email = $this->order->customer->email ?? '';
            $this->customer_address = $this->order->customer->address ?? '';
            $this->is_new_customer = false;
            $this->customer_mode = 'existing';
        } else {
            $this->is_new_customer = true;
            $this->customer_mode = 'new';
        }

        if ($this->order->device) {
            $device = $this->order->device;
            $this->customer_device_id = $device->id;
            $this->device_brand = $device->brand;
            $this->device_model = $device->model;
            $this->device_color = $device->color ?? '';
            $this->device_variant = $device->variant ?? '';
            $this->device_specs = $device->specs ?? '';
            $this->device_imei = $device->imei ?? '';
            $this->passcode = $this->order->passcode ?: ($device->passcode ?? '');
            $this->device_catalog_search = "{$device->brand} {$device->model}";
        } else {
            $this->passcode = $this->order->passcode ?? '';
        }

        $this->complaints = (string) ($this->order->complaints ?? '');
        $this->accessories_included = (string) ($this->order->accessories_included ?? 'Unit HP Saja');

        if (is_array($this->order->physical_condition) && ! empty($this->order->physical_condition)) {
            $this->physical_condition = array_merge($this->physical_condition, $this->order->physical_condition);
        }

        $this->existing_photos = is_array($this->order->photos) ? $this->order->photos : [];
    }

    public function setCustomerMode(string $mode): void
    {
        $this->customer_mode = $mode;
        if ($mode === 'new') {
            $this->is_new_customer = true;
            $this->customer_id = null;
        } else {
            $this->is_new_customer = false;
        }
    }

    public function updatedCustomerMode(string $value): void
    {
        $this->setCustomerMode($value);
    }

    public function updatedIsNewCustomer(bool $value): void
    {
        $this->customer_mode = $value ? 'new' : 'existing';
    }

    public function updatedCustomerPhone(string $value): void
    {
        $normalized = self::normalizePhoneNumber($value);
        if ($this->customer_phone !== $normalized) {
            $this->customer_phone = $normalized;
        }
    }

    public static function normalizePhoneNumber(?string $phone): string
    {
        if (! $phone) {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (empty($digits)) {
            return '';
        }

        if (str_starts_with($digits, '628')) {
            $digits = '08'.substr($digits, 3);
        } elseif (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '08'.substr($digits, 1);
        } elseif (strlen($digits) >= 2 && str_starts_with($digits, '0') && $digits[1] !== '8') {
            $digits = '08'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '0')) {
            $digits = '08'.$digits;
        }

        if (strlen($digits) > 16) {
            $digits = substr($digits, 0, 16);
        }

        return $digits;
    }

    public function selectCustomer(string $id): void
    {
        $customer = Customer::with('devices')->findOrFail($id);
        $this->customer_id = $customer->id;
        $this->customer_name = $customer->name;
        $this->customer_phone = $customer->phone;
        $this->customer_email = $customer->email ?? '';
        $this->customer_address = $customer->address ?? '';
        $this->is_new_customer = false;
        $this->customer_mode = 'existing';
        $this->customer_search = '';

        if ($customer->devices->isNotEmpty()) {
            $this->selectDevice($customer->devices->first()->id);
        }
    }

    public function selectDevice(string $deviceId): void
    {
        $device = CustomerDevice::findOrFail($deviceId);
        $this->customer_device_id = $device->id;
        $this->device_brand = $device->brand;
        $this->device_model = $device->model;
        $this->device_color = $device->color ?? '';
        $this->device_variant = $device->variant ?? '';
        $this->device_specs = $device->specs ?? '';
        $this->device_imei = $device->imei ?? '';
        $this->passcode = $device->passcode ?? '';
    }

    public function updatedDeviceCatalogSearch(): void
    {
        $this->show_catalog_dropdown = true;
    }

    public function selectCatalogDevice(string $catalogId): void
    {
        $catalog = DeviceCatalog::findOrFail($catalogId);
        $this->selected_catalog_id = $catalog->id;
        $this->device_brand = $catalog->brand;
        $this->device_model = $catalog->model_name;
        $this->device_specs = $catalog->specs_summary ?? ($catalog->chipset ? 'Chipset: '.$catalog->chipset : '');
        $this->suggested_colors = $catalog->default_colors ?? [];
        $this->suggested_variants = $catalog->default_variants ?? [];
        $this->device_catalog_search = "{$catalog->brand} {$catalog->model_name}";
        $this->show_catalog_dropdown = false;
    }

    public function pickSuggestedColor(string $color): void
    {
        $this->device_color = $color;
    }

    public function pickSuggestedVariant(string $variant): void
    {
        $this->device_variant = $variant;
    }

    public function clearCatalogSelection(): void
    {
        $this->selected_catalog_id = null;
        $this->device_catalog_search = '';
        $this->suggested_colors = [];
        $this->suggested_variants = [];
        $this->show_catalog_dropdown = false;
    }

    public function updatedCameraPhoto(): void
    {
        $this->validate([
            'camera_photo' => 'image|max:10240',
        ]);

        if ($this->camera_photo) {
            $this->device_photos[] = $this->camera_photo;
            $this->camera_photo = null;
        }
    }

    public function updatedGalleryPhotos(): void
    {
        $this->validate([
            'gallery_photos.*' => 'image|max:10240',
        ]);

        foreach ($this->gallery_photos as $photo) {
            $this->device_photos[] = $photo;
        }
        $this->gallery_photos = [];
    }

    public function removeDevicePhoto(int $index): void
    {
        if (isset($this->device_photos[$index])) {
            unset($this->device_photos[$index]);
            $this->device_photos = array_values($this->device_photos);
        }
    }

    public function removeExistingPhoto(int $index): void
    {
        if (isset($this->existing_photos[$index])) {
            unset($this->existing_photos[$index]);
            $this->existing_photos = array_values($this->existing_photos);
        }
    }

    public function switchToNewCustomer(): void
    {
        $this->reset(['customer_id', 'customer_name', 'customer_phone', 'customer_email', 'customer_address', 'customer_device_id', 'device_brand', 'device_model', 'device_color', 'device_variant', 'device_specs', 'device_imei', 'passcode']);
        $this->is_new_customer = true;
        $this->customer_mode = 'new';
        $this->customer_search = '';
    }

    public function updateOrder(TenantContext $tenantContext): void
    {
        $rules = [
            'complaints' => ['required', 'string', 'max:1000'],
            'accessories_included' => ['nullable', 'string', 'max:255'],
            'passcode' => ['nullable', 'string', 'max:100'],
            'device_imei' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->is_new_customer) {
            $this->customer_phone = self::normalizePhoneNumber($this->customer_phone);
            $rules['customer_name'] = ['required', 'string', 'max:255'];
            $rules['customer_phone'] = ['required', 'string', 'regex:/^08[0-9]{8,14}$/'];
        }

        if (! $this->customer_device_id) {
            $rules['device_brand'] = ['required', 'string', 'max:60'];
            $rules['device_model'] = ['required', 'string', 'max:100'];
        }

        $this->validate($rules, [
            'customer_phone.regex' => 'Format nomor WhatsApp / HP harus diawali 08 dan terdiri dari 10-16 digit angka.',
            'device_brand.required' => 'Merek / Brand smartphone wajib diisi.',
            'device_model.required' => 'Model / Tipe HP wajib diisi.',
        ]);

        $store = $tenantContext->getStore() ?: $this->order->store;

        if (! $store) {
            $user = auth()->user();
            $membership = $user?->memberships()->with('store')->where('status', 'active')->first();
            $store = $membership?->store ?? $user?->ownedStores()->first();
            if ($store) {
                $tenantContext->setStore($store, $membership);
            }
        }

        if (! $store) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Toko Tidak Ditemukan',
                'message' => 'Silakan pilih atau buka ulang toko Anda.',
            ]);

            return;
        }

        DB::transaction(function () use ($store) {
            // 1. Resolve or Update Customer
            if ($this->is_new_customer || ! $this->customer_id) {
                $customer = Customer::create([
                    'store_id' => $store->id,
                    'name' => $this->customer_name,
                    'phone' => $this->customer_phone,
                    'email' => $this->customer_email ?: null,
                    'address' => $this->customer_address ?: null,
                ]);
                $this->customer_id = $customer->id;
            } else {
                $customer = Customer::findOrFail($this->customer_id);
                $customer->update([
                    'name' => $this->customer_name ?: $customer->name,
                    'phone' => $this->customer_phone ?: $customer->phone,
                    'email' => $this->customer_email ?: $customer->email,
                    'address' => $this->customer_address ?: $customer->address,
                ]);
            }

            // 2. Resolve or Update Device
            if (! $this->customer_device_id) {
                $device = $customer->devices()->create([
                    'store_id' => $store->id,
                    'brand' => $this->device_brand,
                    'model' => $this->device_model,
                    'color' => $this->device_color ?: null,
                    'variant' => $this->device_variant ?: null,
                    'specs' => $this->device_specs ?: null,
                    'imei' => $this->device_imei ?: null,
                    'passcode' => $this->passcode ?: null,
                ]);
                $this->customer_device_id = $device->id;
            } else {
                $device = CustomerDevice::findOrFail($this->customer_device_id);
                $device->update([
                    'brand' => $this->device_brand ?: $device->brand,
                    'model' => $this->device_model ?: $device->model,
                    'color' => $this->device_color ?: $device->color,
                    'variant' => $this->device_variant ?: $device->variant,
                    'specs' => $this->device_specs ?: $device->specs,
                    'imei' => $this->device_imei ?: $device->imei,
                    'passcode' => $this->passcode ?: $device->passcode,
                ]);
            }

            // 3. Process & Store Photos with automatic compression & resizing
            $storedPhotos = $this->existing_photos;
            foreach ($this->device_photos as $photo) {
                if ($photo instanceof UploadedFile) {
                    $path = ImageOptimizer::optimizeAndStore($photo, 'orders/'.$store->id, 'public');
                    $storedPhotos[] = $path;
                }
            }

            // 4. Update Service Order
            $this->order->update([
                'customer_id' => $customer->id,
                'customer_device_id' => $device->id,
                'complaints' => $this->complaints,
                'physical_condition' => $this->physical_condition,
                'photos' => array_values($storedPhotos),
                'accessories_included' => $this->accessories_included,
                'passcode' => $this->passcode ?: $device->passcode,
            ]);

            // 5. Create Order History
            $this->order->histories()->create([
                'store_id' => $store->id,
                'user_id' => Auth::id(),
                'from_status' => $this->order->status,
                'to_status' => $this->order->status,
                'notes' => 'Data order, perangkat, atau keluhan diperbarui melalui halaman edit order.',
            ]);
        });

        session()->flash('success', "Perubahan pada Order #{$this->order->order_number} berhasil disimpan.");
        session()->flash('toast', [
            'type' => 'success',
            'title' => 'Order Berhasil Diperbarui!',
            'message' => "Perubahan pada Order #{$this->order->order_number} berhasil disimpan.",
        ]);

        $this->redirect(route('orders.show', $this->order->id), navigate: true);
    }

    public function render(TenantContext $tenantContext)
    {
        $matchingCustomers = collect();
        if ($this->customer_mode === 'existing' && strlen(trim($this->customer_search)) >= 2) {
            $rawTerm = trim($this->customer_search);
            $normalizedTerm = self::normalizePhoneNumber($rawTerm);

            $matchingCustomers = Customer::with('devices')
                ->where(function ($q) use ($rawTerm, $normalizedTerm) {
                    $q->where('phone', 'like', "%{$rawTerm}%")
                        ->orWhere('name', 'like', "%{$rawTerm}%");
                    if (! empty($normalizedTerm)) {
                        $q->orWhere('phone', 'like', "%{$normalizedTerm}%");
                    }
                })
                ->latest()
                ->take(6)
                ->get();
        }

        $duplicateCustomer = null;
        if ($this->customer_mode === 'new' && strlen(trim($this->customer_phone)) >= 5) {
            $duplicateCustomer = Customer::where('phone', $this->customer_phone)
                ->when($this->customer_id, fn ($q) => $q->where('id', '!=', $this->customer_id))
                ->first();
        }

        $storeId = $tenantContext->getStoreId() ?: $this->order->store_id;
        $allCatalogDevices = DeviceCatalog::availableFor($storeId)
            ->select(['id', 'brand', 'model_name', 'series', 'specs_summary', 'chipset', 'is_verified', 'default_colors', 'default_variants'])
            ->orderBy('is_verified', 'desc')
            ->orderBy('brand')
            ->orderBy('model_name')
            ->get();

        $catalogSuggestions = collect();
        if (strlen(trim($this->device_catalog_search)) >= 2) {
            $query = trim($this->device_catalog_search);
            $catalogSuggestions = $allCatalogDevices->filter(function ($item) use ($query) {
                return str_contains(strtolower($item->model_name), strtolower($query))
                    || str_contains(strtolower($item->brand), strtolower($query))
                    || str_contains(strtolower($item->series ?? ''), strtolower($query))
                    || str_contains(strtolower($item->chipset ?? ''), strtolower($query));
            })->take(8)->values();
        }

        $existingCustomers = Customer::with('devices')->latest()->take(8)->get();
        $recentCustomers = $existingCustomers->take(5);

        return view('livewire.order.edit-order', [
            'existingCustomers' => $existingCustomers,
            'recentCustomers' => $recentCustomers,
            'matchingCustomers' => $matchingCustomers,
            'duplicateCustomer' => $duplicateCustomer,
            'catalogSuggestions' => $catalogSuggestions,
            'allCatalogDevices' => $allCatalogDevices,
        ])->layoutData([
            'title' => "Edit Order #{$this->order->order_number}",
            'breadcrumbs' => [
                ['label' => 'Order Servis', 'url' => route('orders.index')],
                ['label' => '#'.$this->order->order_number, 'url' => route('orders.show', $this->order->id)],
                ['label' => 'Edit Order'],
            ],
        ]);
    }
}
