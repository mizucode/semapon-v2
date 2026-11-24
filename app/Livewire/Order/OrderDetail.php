<?php

namespace App\Livewire\Order;

use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\TechnicianProfile;
use App\Services\FinanceService;
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

    public bool $showDiagnosisModal = false;

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

    public string $item_name = '';

    public float $item_price = 0;

    // Diagnosis form fields
    public string $diagnosis_notes = '';

    public string $action_taken = '';

    public array $testing_checklist = [
        'lcd' => true,
        'touch' => true,
        'charging' => true,
        'battery' => true,
        'camera' => true,
        'mic_speaker' => true,
        'signal_wifi' => true,
    ];

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
            'diagnoses.technician',
            'histories.user',
            'payments.user',
        ])->findOrFail($id);

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
            'diagnoses.technician',
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
        $this->reset(['selected_service_id', 'item_name', 'item_price']);
        $this->resetValidation();
        $this->showAddItemModal = true;
    }

    public function onServiceSelected(): void
    {
        if ($this->selected_service_id) {
            $service = Service::find($this->selected_service_id);
            if ($service) {
                $this->item_name = $service->name;
                $this->item_price = (float) $service->price;
            }
        }
    }

    public function addItem(): void
    {
        $this->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'item_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () {
            $this->order->items()->create([
                'store_id' => $this->order->store_id,
                'service_id' => $this->selected_service_id ?: null,
                'name' => $this->item_name,
                'price' => $this->item_price,
                'technician_id' => $this->order->technician_id,
            ]);

            // Recalculate total cost
            $totalCost = $this->order->items()->sum('price');
            $this->order->total_cost = $totalCost;

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

        $this->showAddItemModal = false;
        $this->order->load('items');
        $this->payment_amount = $this->order->remaining_balance;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Layanan Ditambahkan',
            'message' => "Item '{$this->item_name}' berhasil ditambahkan ke rincian biaya.",
        ]);
    }

    public function removeItem(string $itemId): void
    {
        DB::transaction(function () use ($itemId) {
            $item = $this->order->items()->findOrFail($itemId);
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

        $this->order->load('items');
        $this->payment_amount = $this->order->remaining_balance;

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Item Dihapus',
            'message' => 'Rincian biaya berhasil diperbarui.',
        ]);
    }

    public function saveDiagnosis(): void
    {
        $this->validate([
            'diagnosis_notes' => ['required', 'string', 'max:1000'],
            'action_taken' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->order->diagnoses()->create([
            'store_id' => $this->order->store_id,
            'technician_id' => Auth::id(),
            'diagnosis_notes' => $this->diagnosis_notes,
            'testing_checklist' => $this->testing_checklist,
            'action_taken' => $this->action_taken ?: null,
        ]);

        $this->showDiagnosisModal = false;
        $this->order->load('diagnoses.technician');

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Diagnosa Disimpan',
            'message' => 'Hasil diagnosa teknisi dan checklist pengujian telah dicatat.',
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

    public function deleteOrder(): mixed
    {
        $orderNumber = $this->order->order_number;

        DB::transaction(function () {
            $this->order->delete();
        });

        session()->flash('success', "Order #{$orderNumber} berhasil dihapus.");

        return $this->redirect(route('orders.index'), navigate: true);
    }

    public function render()
    {
        $availableServices = Service::where('is_active', true)->get();
        $technicians = TechnicianProfile::with('user')->get();

        return view('livewire.order.order-detail', [
            'availableServices' => $availableServices,
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
