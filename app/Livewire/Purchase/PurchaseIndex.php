<?php

namespace App\Livewire\Purchase;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PurchaseIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $paymentFilter = '';

    // Create Modal state
    public bool $showCreateModal = false;

    public ?string $selected_supplier_id = null;

    public float $down_payment = 0;

    public string $purchase_notes = '';

    /**
     * @var array<int, array{product_id: string, quantity: int, unit_cost: float}>
     */
    public array $items = [];

    // Pay Modal state
    public bool $showPayModal = false;

    public ?Purchase $selectedPurchaseForPay = null;

    public float $pay_amount = 0;

    public string $pay_method = 'transfer';

    public string $pay_ref = '';

    public string $pay_notes = '';

    public function mount(): void
    {
        $this->items = [
            ['product_id' => '', 'quantity' => 1, 'unit_cost' => 0],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['selected_supplier_id', 'down_payment', 'purchase_notes']);
        $this->items = [
            ['product_id' => '', 'quantity' => 1, 'unit_cost' => 0],
        ];
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function addItemRow(): void
    {
        $this->items[] = ['product_id' => '', 'quantity' => 1, 'unit_cost' => 0];
    }

    public function removeItemRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->items[] = ['product_id' => '', 'quantity' => 1, 'unit_cost' => 0];
        }
    }

    public function onProductSelected(int $index): void
    {
        $productId = $this->items[$index]['product_id'] ?? null;
        if ($productId) {
            $product = Product::find($productId);
            if ($product) {
                $this->items[$index]['unit_cost'] = (float) $product->buy_price;
            }
        }
    }

    public function savePurchase(PurchaseService $purchaseService): void
    {
        $this->validate([
            'selected_supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $supplier = Supplier::findOrFail($this->selected_supplier_id);
            $purchase = $purchaseService->createPurchase(
                $supplier,
                $this->items,
                (float) $this->down_payment,
                Auth::id(),
                $this->purchase_notes ?: null
            );

            $this->showCreateModal = false;
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'PO Dibuat',
                'message' => "Order pembelian #{$purchase->purchase_number} berhasil disimpan.",
            ]);
        } catch (Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Gagal Membuat PO',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function confirmReceive(string $purchaseId, PurchaseService $purchaseService): void
    {
        try {
            $purchase = Purchase::findOrFail($purchaseId);
            $purchaseService->receivePurchase($purchase, Auth::id());

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Barang Diterima',
                'message' => "Stok seluruh item pada PO #{$purchase->purchase_number} telah otomatis ditambahkan ke inventaris.",
            ]);
        } catch (Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Gagal Menerima Barang',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function openPayModal(string $purchaseId): void
    {
        $this->selectedPurchaseForPay = Purchase::findOrFail($purchaseId);
        $this->pay_amount = $this->selectedPurchaseForPay->remaining_debt;
        $this->pay_method = 'transfer';
        $this->pay_ref = '';
        $this->pay_notes = '';
        $this->resetValidation();
        $this->showPayModal = true;
    }

    public function submitPayment(PurchaseService $purchaseService): void
    {
        $this->validate([
            'pay_amount' => ['required', 'numeric', 'min:1'],
        ]);

        if (! $this->selectedPurchaseForPay) {
            return;
        }

        try {
            $purchaseService->recordSupplierPayment(
                $this->selectedPurchaseForPay,
                $this->pay_amount,
                $this->pay_method,
                Auth::id(),
                $this->pay_ref ?: null,
                $this->pay_notes ?: null
            );

            $this->showPayModal = false;
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Pembayaran Dicatat',
                'message' => 'Pembayaran tagihan ke supplier berhasil dibukukan.',
            ]);
        } catch (Exception $e) {
            $this->addError('pay_amount', $e->getMessage());
        }
    }

    public function cancelPurchaseOrder(string $purchaseId, PurchaseService $purchaseService): void
    {
        try {
            $purchase = Purchase::findOrFail($purchaseId);
            $purchaseService->cancelPurchase($purchase, Auth::id());

            $this->dispatch('toast', [
                'type' => 'info',
                'title' => 'PO Dibatalkan',
                'message' => "Pembelian #{$purchase->purchase_number} telah dibatalkan.",
            ]);
        } catch (Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Gagal Membatalkan',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $query = Purchase::with(['supplier', 'items.product', 'user']);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('purchase_number', 'like', "%{$this->search}%")
                    ->orWhereHas('supplier', function ($sq) {
                        $sq->where('name', 'like', "%{$this->search}%");
                    });
            });
        }

        if (! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (! empty($this->paymentFilter)) {
            $query->where('payment_status', $this->paymentFilter);
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(15);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('livewire.purchase.purchase-index', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'products' => $products,
        ])->layoutData([
            'title' => 'Riwayat Pembelian & Kulakan',
            'breadcrumbs' => [
                ['label' => 'Supplier', 'url' => route('suppliers.index')],
                ['label' => 'Riwayat Pembelian (PO)'],
            ],
        ]);
    }
}
