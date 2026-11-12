<?php

namespace App\Livewire\Order;

use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\TechnicianProfile;
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
        ])->findOrFail($id);

        $this->assigned_technician_id = $this->order->technician_id;
        $this->payment_amount = $this->order->remaining_balance;
    }

    public function advanceStatus(string $newStatus, ?string $notes = null): void
    {
        $this->order->updateStatus($newStatus, $notes, Auth::id());
        $this->order->refresh();

        $statusLabels = [
            'received' => 'Diterima',
            'diagnosing' => 'Diagnosa',
            'waiting_approval' => 'Tunggu Konfirmasi',
            'in_progress' => 'Sedang Dikerjakan',
            'waiting_parts' => 'Menunggu Sparepart',
            'testing' => 'Pengujian (QC)',
            'ready_for_pickup' => 'Siap Diambil',
            'completed' => 'Selesai & Diambil',
            'cancelled' => 'Dibatalkan',
            'unrepairable' => 'Tidak Bisa Diperbaiki',
        ];

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Status Diperbarui',
            'message' => 'Status order kini: '.($statusLabels[$newStatus] ?? ucfirst(str_replace('_', ' ', $newStatus))),
        ]);
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

    public function recordPayment(): void
    {
        $this->validate([
            'payment_amount' => ['required', 'numeric', 'min:1'],
        ]);

        DB::transaction(function () {
            $newPaidTotal = (float) $this->order->paid_amount + $this->payment_amount;
            $this->order->paid_amount = $newPaidTotal;

            if ($newPaidTotal >= (float) $this->order->total_cost) {
                $this->order->payment_status = 'paid';
            } else {
                $this->order->payment_status = 'partially_paid';
            }

            $this->order->save();

            $this->order->histories()->create([
                'store_id' => $this->order->store_id,
                'user_id' => Auth::id(),
                'from_status' => $this->order->status,
                'to_status' => $this->order->status,
                'notes' => 'Pembayaran diterima: Rp'.number_format($this->payment_amount, 0, ',', '.')." ({$this->payment_method}).",
            ]);
        });

        $this->showPaymentModal = false;
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

    public function render()
    {
        $availableServices = Service::where('is_active', true)->get();
        $technicians = TechnicianProfile::with('user')->get();

        return view('livewire.order.order-detail', [
            'availableServices' => $availableServices,
            'technicians' => $technicians,
        ])->layoutData([
            'title' => "Order #{$this->order->order_number}",
            'breadcrumbs' => [
                ['label' => 'Order Servis', 'url' => route('orders.index')],
                ['label' => '#'.$this->order->order_number],
            ],
        ]);
    }
}
