<?php

namespace App\Livewire\Order;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\ServiceOrder;
use App\Models\TechnicianProfile;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app', ['title' => 'Terima Servis Baru', 'breadcrumbs' => [['label' => 'Order', 'url' => '/orders'], ['label' => 'Penerimaan Servis']]])]
class CreateOrder extends Component
{
    use WithFileUploads;

    // Photos
    public $camera_photo;

    public $gallery_photos = [];

    public array $device_photos = [];

    // Customer Selection / Creation
    public ?string $customer_id = null;

    public string $customer_name = '';

    public string $customer_phone = '';

    public string $customer_email = '';

    public string $customer_address = '';

    public bool $is_new_customer = true;

    public string $customer_mode = 'new'; // 'existing' or 'new'

    public string $customer_search = '';

    // Device Selection / Creation
    public ?string $customer_device_id = null;

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

    // Estimation & Assignment
    public ?string $technician_id = null;

    public bool $is_cost_undetermined = true;

    public string $estimated_cost = '0';

    public string $down_payment = '0';

    public bool $is_completion_undetermined = true;

    public string $estimated_completion_at = '';

    public function mount(): void
    {
        // Default is undetermined (Menunggu Diagnosa)
    }

    public function updatedIsCostUndetermined(bool $value): void
    {
        if ($value) {
            $this->estimated_cost = '0';
        }
    }

    public function updatedEstimatedCost(mixed $value): void
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        $this->estimated_cost = $clean === '' ? '0' : $clean;
        if ((int) $this->estimated_cost > 0) {
            $this->is_cost_undetermined = false;
        }
    }

    public function updatedDownPayment(mixed $value): void
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        $this->down_payment = $clean === '' ? '0' : $clean;
    }

    public function updatedIsCompletionUndetermined(bool $value): void
    {
        if ($value) {
            $this->estimated_completion_at = '';
        } else {
            if (empty($this->estimated_completion_at)) {
                $this->estimated_completion_at = now()->addDays(2)->format('Y-m-d\TH:i');
            }
        }
    }

    public function updatedEstimatedCompletionAt(mixed $value): void
    {
        if (! empty($value)) {
            $this->is_completion_undetermined = false;
        }
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

    public function switchToNewCustomer(): void
    {
        $this->reset(['customer_id', 'customer_name', 'customer_phone', 'customer_email', 'customer_address', 'customer_device_id', 'device_brand', 'device_model', 'device_color', 'device_variant', 'device_specs', 'device_imei', 'passcode']);
        $this->is_new_customer = true;
        $this->customer_mode = 'new';
        $this->customer_search = '';
    }

    public function storeOrder(TenantContext $tenantContext): void
    {
        if ($this->is_cost_undetermined) {
            $this->estimated_cost = 0;
        } else {
            $cleanCost = preg_replace('/[^0-9]/', '', (string) $this->estimated_cost);
            $this->estimated_cost = $cleanCost === '' ? 0 : (float) $cleanCost;
        }

        $cleanDp = preg_replace('/[^0-9]/', '', (string) $this->down_payment);
        $this->down_payment = $cleanDp === '' ? 0 : (float) $cleanDp;

        $rules = [
            'complaints' => ['required', 'string', 'max:1000'],
            'accessories_included' => ['nullable', 'string', 'max:255'],
            'passcode' => ['nullable', 'string', 'max:100'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'estimated_cost' => $this->is_cost_undetermined ? ['nullable', 'numeric', 'min:0'] : ['required', 'numeric', 'min:0'],
            'down_payment' => ['required', 'numeric', 'min:0'],
            'estimated_completion_at' => $this->is_completion_undetermined ? ['nullable'] : ['nullable', 'date'],
        ];

        if ($this->is_new_customer) {
            $this->customer_phone = self::normalizePhoneNumber($this->customer_phone);
            $rules['customer_name'] = ['required', 'string', 'max:255'];
            $rules['customer_phone'] = ['required', 'string', 'regex:/^08[0-9]{8,14}$/'];
            $rules['device_brand'] = ['required', 'string', 'max:60'];
            $rules['device_model'] = ['required', 'string', 'max:100'];
        }

        $this->validate($rules, [
            'customer_phone.regex' => 'Format nomor WhatsApp / HP harus diawali 08 dan terdiri dari 10-16 digit angka.',
        ]);

        $store = $tenantContext->getStore();

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

        $order = DB::transaction(function () use ($store) {
            // 1. Resolve or Create Customer
            if ($this->is_new_customer || ! $this->customer_id) {
                $customer = Customer::create([
                    'store_id' => $store->id,
                    'name' => $this->customer_name,
                    'phone' => $this->customer_phone,
                    'email' => $this->customer_email ?: null,
                    'address' => $this->customer_address ?: null,
                ]);
            } else {
                $customer = Customer::findOrFail($this->customer_id);
                if ($this->customer_address && ! $customer->address) {
                    $customer->update(['address' => $this->customer_address]);
                }
            }

            // 2. Resolve or Create Device
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
            } else {
                $device = CustomerDevice::findOrFail($this->customer_device_id);
                // Update variant/specs if provided
                if ($this->device_variant || $this->device_specs) {
                    $device->update([
                        'variant' => $this->device_variant ?: $device->variant,
                        'specs' => $this->device_specs ?: $device->specs,
                    ]);
                }
            }

            // Process & Store Photos
            $storedPhotos = [];
            foreach ($this->device_photos as $photo) {
                if ($photo instanceof TemporaryUploadedFile) {
                    $path = $photo->store('orders/'.$store->id, 'public');
                    $storedPhotos[] = $path;
                }
            }

            // 3. Create Service Order
            $paymentStatus = 'unpaid';
            if ($this->down_payment > 0) {
                $paymentStatus = (! $this->is_cost_undetermined && $this->down_payment >= $this->estimated_cost && $this->estimated_cost > 0)
                    ? 'paid'
                    : 'partially_paid';
            }

            $order = ServiceOrder::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'customer_device_id' => $device->id,
                'status' => 'received',
                'complaints' => $this->complaints,
                'physical_condition' => $this->physical_condition,
                'photos' => $storedPhotos,
                'accessories_included' => $this->accessories_included,
                'passcode' => $this->passcode ?: $device->passcode,
                'is_cost_undetermined' => $this->is_cost_undetermined,
                'estimated_cost' => $this->is_cost_undetermined ? 0 : $this->estimated_cost,
                'total_cost' => $this->is_cost_undetermined ? 0 : $this->estimated_cost,
                'down_payment' => $this->down_payment,
                'paid_amount' => $this->down_payment,
                'payment_status' => $paymentStatus,
                'technician_id' => $this->technician_id ?: null,
                'created_by' => Auth::id(),
                'estimated_completion_at' => (! $this->is_completion_undetermined && $this->estimated_completion_at) ? date('Y-m-d H:i:s', strtotime($this->estimated_completion_at)) : null,
            ]);

            // 4. Create Initial Order History
            $order->histories()->create([
                'store_id' => $store->id,
                'user_id' => Auth::id(),
                'from_status' => null,
                'to_status' => 'received',
                'notes' => 'Unit HP diterima di konter.',
            ]);

            // 5. Increment customer orders count
            $customer->increment('total_orders_count');

            return $order;
        });

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Nota Servis Diterbitkan!',
            'message' => "Order #{$order->order_number} berhasil dibuat. Siap dicetak atau dikirim link tracking ke pelanggan.",
        ]);

        $this->redirect(route('orders.show', $order->id), navigate: true);
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
            $duplicateCustomer = Customer::where('phone', $this->customer_phone)->first();
        }

        $existingCustomers = Customer::with('devices')->latest()->take(8)->get();
        $recentCustomers = $existingCustomers->take(5);
        $technicians = TechnicianProfile::with('user')->whereHas('membership', fn ($q) => $q->where('status', 'active'))->get();

        return view('livewire.order.create-order', [
            'existingCustomers' => $existingCustomers,
            'recentCustomers' => $recentCustomers,
            'matchingCustomers' => $matchingCustomers,
            'duplicateCustomer' => $duplicateCustomer,
            'technicians' => $technicians,
        ]);
    }
}
