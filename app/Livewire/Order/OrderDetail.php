<?php

namespace App\Livewire\Order;

use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\TechnicianProfile;
use App\Services\FinanceService;
use App\Services\InventoryService;
use App\Services\TenantContext;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrderDetail extends Component
{
    public ServiceOrder $order;

    // Modals
    public bool $showAddItemModal = false;

    public bool $showPaymentModal = false;

    public bool $showAssignTechModal = false;

    public bool $showUpdateStatusModal = false;

    // Status update fields
    public string $new_status = '';

    public string $status_notes = '';

    public const STATUS_OPTIONS = [
        'received' => [
            'label' => 'Menunggu Diagnosa',
            'badge' => 'Menunggu Diagnosa',
            'color' => 'blue',
            'description' => 'Unit baru masuk bengkel dan menunggu giliran diagnosa oleh teknisi.',
        ],
        'diagnosing' => [
            'label' => 'Sedang Diagnosa / Pemeriksaan',
            'badge' => 'Sedang Diagnosa',
            'color' => 'purple',
            'description' => 'Teknisi sedang membongkar dan memeriksa sumber kerusakan.',
        ],
        'waiting_approval' => [
            'label' => 'Tunggu Konfirmasi Biaya',
            'badge' => 'Tunggu Konfirmasi',
            'color' => 'amber',
            'description' => 'Hasil diagnosa selesai, menunggu persetujuan biaya dari pelanggan.',
        ],
        'in_progress' => [
            'label' => 'Dalam Pengerjaan Servis',
            'badge' => 'Dalam Pengerjaan',
            'color' => 'indigo',
            'description' => 'Teknisi aktif melakukan perbaikan atau pergantian sparepart.',
        ],
        'waiting_parts' => [
            'label' => 'Menunggu Sparepart',
            'badge' => 'Menunggu Sparepart',
            'color' => 'orange',
            'description' => 'Pengerjaan dijeda menunggu ketersediaan atau pesanan suku cadang.',
        ],
        'testing' => [
            'label' => 'Pengujian Fungsi (QC)',
            'badge' => 'Pengujian (QC)',
            'color' => 'violet',
            'description' => 'Perbaikan selesai, sedang dilakukan checklist uji fungsi gadget.',
        ],
        'ready_for_pickup' => [
            'label' => 'Siap Diambil Pelanggan',
            'badge' => 'Siap Diambil',
            'color' => 'sky',
            'description' => 'Gadget sudah selesai diuji dan siap diserahkan kepada pelanggan.',
        ],
        'completed' => [
            'label' => 'Selesai & Unit Diambil',
            'badge' => 'Selesai',
            'color' => 'emerald',
            'description' => 'Unit telah diserahkan kembali dan diambil oleh pelanggan.',
        ],
        'unrepairable' => [
            'label' => 'Tidak Bisa Diperbaiki (Gagal)',
            'badge' => 'Tidak Bisa Diperbaiki',
            'color' => 'rose',
            'description' => 'Kerusakan parah/IC dead dan tidak memungkinkan untuk diperbaiki.',
        ],
        'cancelled' => [
            'label' => 'Dibatalkan Pelanggan',
            'badge' => 'Dibatalkan',
            'color' => 'slate',
            'description' => 'Perbaikan dibatalkan atas permintaan pelanggan atau alasan lain.',
        ],
    ];

    // Item form fields
    public ?string $selected_service_id = null;

    public ?string $selected_product_id = null;

    public int $part_quantity = 1;

    public string $part_search = '';

    public string $item_name = '';

    public float $item_price = 0;

    // Payment fields
    public float $payment_amount = 0;

    public string $payment_method = 'cash'; // cash, transfer, qris

    public string $payment_notes = '';

    // Assign technician
    public ?string $assigned_technician_id = null;

    public function mount(string $id): void
    {
        $this->order = ServiceOrder::with([
            'customer',
            'device',
            'technician',
            'items.service',
            'items.orderPart.product',
            'histories.user',
            'payments.user',
        ])->findOrFail($id);

        if (app(TenantContext::class)->isTechnician() && $this->order->technician_id !== Auth::id()) {
            abort(403, 'Order servis ini tidak ditugaskan kepada Anda.');
        }

        $this->assigned_technician_id = $this->order->technician_id;
        $this->payment_amount = $this->order->remaining_balance;
    }

    public function advanceStatus(string $newStatus, ?string $notes = null): void
    {
        $this->order->updateStatus($newStatus, $notes, Auth::id());
        $this->order->load([
            'customer',
            'device',
            'technician',
            'items.service',
            'items.orderPart.product',
            'histories.user',
        ]);

        $badge = self::STATUS_OPTIONS[$newStatus]['badge'] ?? ucfirst(str_replace('_', ' ', $newStatus));

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Status Diperbarui',
            'message' => "Status order kini: {$badge}",
        ]);
    }

    public function openUpdateStatusModal(?string $targetStatus = null): void
    {
        $this->new_status = $targetStatus ?? $this->order->status;
        $this->status_notes = '';
        $this->resetValidation();
        $this->showUpdateStatusModal = true;
    }

    public function updateStatusWithNotes(): void
    {
        $this->validate([
            'new_status' => ['required', 'string', 'in:'.implode(',', array_keys(self::STATUS_OPTIONS))],
            'status_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->advanceStatus($this->new_status, $this->status_notes ?: null);
        $this->showUpdateStatusModal = false;
        $this->reset(['new_status', 'status_notes']);
    }

    public function openAddItemModal(): void
    {
        $this->reset([
            'selected_service_id',
            'selected_product_id',
            'part_search',
            'item_name',
            'item_price',
        ]);
        $this->part_quantity = 1;
        $this->resetValidation();
        $this->showAddItemModal = true;
    }

    protected function recalculateDraftItem(): void
    {
        $service = $this->selected_service_id
            ? Service::find($this->selected_service_id)
            : null;

        $product = $this->selected_product_id
            ? Product::find($this->selected_product_id)
            : null;

        $qty = max(1, (int) $this->part_quantity);
        if ($product && $product->stock > 0 && $qty > $product->stock) {
            $qty = $product->stock;
        }
        $this->part_quantity = $qty;

        if ($service && $product) {
            $qtySuffix = $qty > 1 ? " x{$qty}" : '';
            $this->item_name = "{$service->name} ({$product->name}{$qtySuffix})";
        } elseif ($service) {
            $this->item_name = $service->name;
        } elseif ($product) {
            $this->item_name = $qty > 1
                ? "{$product->name} ({$qty} {$product->unit})"
                : $product->name;
        } else {
            $this->item_name = '';
        }

        $servicePrice = $service ? (float) $service->price : 0.0;
        $partPrice = $product ? ((float) $product->sell_price * $qty) : 0.0;
        $this->item_price = $servicePrice + $partPrice;
    }

    public function onServiceSelected(): void
    {
        $this->recalculateDraftItem();
    }

    public function updatedSelectedServiceId(): void
    {
        $this->recalculateDraftItem();
    }

    public function selectCatalogService(?string $serviceId = null): void
    {
        $this->selected_service_id = $serviceId ?: null;
        $this->recalculateDraftItem();
        $this->resetValidation(['item_name', 'item_price']);
    }

    public function selectSparepart(?string $productId = null): void
    {
        $this->selected_product_id = $productId ?: null;
        $this->part_quantity = 1;
        $this->part_search = '';
        $this->recalculateDraftItem();
        $this->resetValidation(['selected_product_id', 'part_quantity', 'item_name', 'item_price']);
    }

    public function updatedPartQuantity(): void
    {
        $this->recalculateDraftItem();
    }

    public function incrementPartQuantity(): void
    {
        $this->part_quantity = max(1, $this->part_quantity + 1);
        $this->recalculateDraftItem();
    }

    public function decrementPartQuantity(): void
    {
        $this->part_quantity = max(1, $this->part_quantity - 1);
        $this->recalculateDraftItem();
    }

    public function addItem(InventoryService $inventoryService): void
    {
        $this->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'item_price' => ['required', 'numeric', 'min:0'],
            'selected_product_id' => ['nullable', 'exists:products,id'],
            'part_quantity' => ['required_with:selected_product_id', 'integer', 'min:1'],
        ], [
            'item_name.required' => 'Nama layanan atau sparepart wajib diisi.',
            'item_price.required' => 'Nominal biaya wajib diisi.',
            'item_price.min' => 'Nominal biaya tidak boleh negatif.',
            'part_quantity.min' => 'Jumlah sparepart minimal 1.',
        ]);

        try {
            DB::transaction(function () use ($inventoryService) {
                $orderPart = null;

                if ($this->selected_product_id) {
                    $product = Product::findOrFail($this->selected_product_id);
                    $orderPart = $inventoryService->deductPartForOrderItem(
                        $this->order,
                        $product,
                        $this->part_quantity,
                        Auth::id()
                    );
                }

                $this->order->items()->create([
                    'store_id' => $this->order->store_id,
                    'service_id' => $this->selected_service_id ?: null,
                    'service_order_part_id' => $orderPart?->id,
                    'name' => trim($this->item_name),
                    'price' => $this->item_price,
                    'technician_id' => $this->order->technician_id,
                ]);

                // Recalculate total cost
                $totalCost = $this->order->items()->sum('price');
                $this->order->total_cost = $totalCost;
                if ($totalCost > 0) {
                    $this->order->is_cost_undetermined = false;
                }

                // Update payment status
                if ($this->order->paid_amount >= $totalCost && $totalCost > 0) {
                    $this->order->payment_status = 'paid';
                } elseif ($this->order->paid_amount > 0) {
                    $this->order->payment_status = 'partially_paid';
                } else {
                    $this->order->payment_status = 'unpaid';
                }

                $this->order->save();
            });
        } catch (Exception $e) {
            $this->addError('selected_product_id', $e->getMessage());

            return;
        }

        $savedItemName = trim($this->item_name);
        $this->showAddItemModal = false;
        $this->order->load(['items.service', 'items.orderPart.product']);
        $this->payment_amount = $this->order->remaining_balance;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Biaya Ditambahkan',
            'message' => "Item '{$savedItemName}' berhasil ditambahkan ke rincian biaya.",
        ]);
    }

    public function removeItem(string $itemId, InventoryService $inventoryService): void
    {
        DB::transaction(function () use ($itemId, $inventoryService) {
            $item = $this->order->items()->with('orderPart')->findOrFail($itemId);

            if ($item->orderPart) {
                $inventoryService->returnPartFromOrder($item->orderPart, Auth::id());
            }

            $item->delete();

            $totalCost = $this->order->items()->sum('price');
            $this->order->total_cost = $totalCost;

            if ($this->order->paid_amount >= $totalCost && $totalCost > 0) {
                $this->order->payment_status = 'paid';
            } elseif ($this->order->paid_amount > 0) {
                $this->order->payment_status = 'partially_paid';
            } else {
                $this->order->payment_status = 'unpaid';
            }

            $this->order->save();
        });

        $this->order->load(['items.service', 'items.orderPart.product']);
        $this->payment_amount = $this->order->remaining_balance;

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Item Dihapus',
            'message' => 'Rincian biaya berhasil diperbarui.',
        ]);
    }

    public function recordPayment(FinanceService $financeService): void
    {
        $this->validate([
            'payment_amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris'],
            'payment_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $financeService->recordOrderPayment(
            order: $this->order,
            amount: (float) $this->payment_amount,
            method: $this->payment_method,
            userId: Auth::id(),
            notes: $this->payment_notes ?: null,
        );

        $noteText = $this->payment_notes ? " - Catatan: {$this->payment_notes}" : '';
        $this->order->histories()->create([
            'store_id' => $this->order->store_id,
            'user_id' => Auth::id(),
            'from_status' => $this->order->status,
            'to_status' => $this->order->status,
            'notes' => 'Pembayaran diterima: Rp. '.number_format($this->payment_amount, 0, ',', '.')." ({$this->payment_method}){$noteText}.",
        ]);

        $this->showPaymentModal = false;
        $this->payment_notes = '';
        $this->order->load(['payments.user', 'histories.user']);
        $this->payment_amount = $this->order->remaining_balance;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Pembayaran Dicatat',
            'message' => 'Penerimaan pembayaran kasir berhasil dibukukan.',
        ]);
    }

    public function assignTechnician(): void
    {
        abort_if(app(TenantContext::class)->isTechnician(), 403);

        $this->order->update([
            'technician_id' => $this->assigned_technician_id ?: null,
        ]);

        $techName = $this->order->technician?->name ?? 'Antrean Terbuka';

        $this->order->histories()->create([
            'store_id' => $this->order->store_id,
            'user_id' => Auth::id(),
            'from_status' => $this->order->status,
            'to_status' => $this->order->status,
            'notes' => "Penugasan teknisi diubah ke: {$techName}.",
        ]);

        $this->showAssignTechModal = false;
        $this->order->load('technician');

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Teknisi Ditugaskan',
            'message' => "Order #{$this->order->order_number} ditugaskan kepada {$techName}.",
        ]);
    }

    public function deleteOrder(InventoryService $inventoryService): mixed
    {
        abort_if(app(TenantContext::class)->isTechnician(), 403);

        $orderNumber = $this->order->order_number;

        DB::transaction(function () use ($inventoryService) {
            foreach ($this->order->parts()->get() as $part) {
                $inventoryService->returnPartFromOrder($part, Auth::id());
            }

            $this->order->delete();
        });

        session()->flash('success', "Order #{$orderNumber} berhasil dihapus.");

        return $this->redirect(route('orders.index'), navigate: true);
    }

    public function render()
    {
        $isTechnician = app(TenantContext::class)->isTechnician();
        $availableServices = Service::where('is_active', true)->get();
        $availableProducts = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->when($this->part_search, function ($query) {
                $search = trim($this->part_search);
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get();
        $selectedProduct = $this->selected_product_id
            ? Product::find($this->selected_product_id)
            : null;
        $technicians = $isTechnician ? collect() : TechnicianProfile::with('user')->get();

        return view('livewire.order.order-detail', [
            'isTechnician' => $isTechnician,
            'availableServices' => $availableServices,
            'availableProducts' => $availableProducts,
            'selectedProduct' => $selectedProduct,
            'technicians' => $technicians,
            'statusOptions' => self::STATUS_OPTIONS,
            'statusLabels' => collect(self::STATUS_OPTIONS)->mapWithKeys(fn ($opt, $k) => [$k => $opt['badge']])->all(),
        ])->layoutData([
            'title' => "Order #{$this->order->order_number}",
            'breadcrumbs' => [
                ['label' => 'Order Servis', 'url' => route('orders.index')],
                ['label' => '#'.$this->order->order_number],
            ],
        ]);
    }
}
