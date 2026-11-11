<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\InventoryService;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $categoryFilter = '';

    public bool $onlyLowStock = false;

    // Modal state
    public bool $showProductModal = false;

    public bool $isEditing = false;

    public ?string $editingProductId = null;

    // Form fields
    public string $name = '';

    public ?string $category_id = null;

    public string $sku = '';

    public string $barcode = '';

    public string $type = 'sparepart';

    public float $buy_price = 0;

    public float $sell_price = 0;

    public int $stock = 0;

    public int $min_stock = 5;

    public string $unit = 'pcs';

    public bool $is_active = true;

    // Quick stock adjustment modal
    public bool $showStockAdjustModal = false;

    public ?Product $selectedProductForStock = null;

    public string $adjust_type = 'in'; // 'in', 'out', 'adjustment'

    public int $adjust_quantity = 1;

    public string $adjust_notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'sku' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:sparepart,accessory,unit,other'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingOnlyLowStock(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'name', 'category_id', 'sku', 'barcode', 'type',
            'buy_price', 'sell_price', 'stock', 'min_stock', 'unit',
            'is_active', 'editingProductId',
        ]);
        $this->isEditing = false;
        $this->type = 'sparepart';
        $this->unit = 'pcs';
        $this->min_stock = 5;
        $this->is_active = true;
        $this->resetValidation();
        $this->showProductModal = true;
    }

    public function openEditModal(string $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->sku = $product->sku ?? '';
        $this->barcode = $product->barcode ?? '';
        $this->type = $product->type;
        $this->buy_price = (float) $product->buy_price;
        $this->sell_price = (float) $product->sell_price;
        $this->stock = $product->stock;
        $this->min_stock = $product->min_stock;
        $this->unit = $product->unit;
        $this->is_active = $product->is_active;

        $this->isEditing = true;
        $this->resetValidation();
        $this->showProductModal = true;
    }

    public function saveProduct(TenantContext $tenantContext, InventoryService $inventoryService): void
    {
        $this->validate();

        $store = $tenantContext->getStore();

        if ($this->isEditing && $this->editingProductId) {
            $product = Product::findOrFail($this->editingProductId);
            $product->update([
                'name' => $this->name,
                'category_id' => $this->category_id ?: null,
                'sku' => $this->sku ?: null,
                'barcode' => $this->barcode ?: null,
                'type' => $this->type,
                'buy_price' => $this->buy_price,
                'sell_price' => $this->sell_price,
                'min_stock' => $this->min_stock,
                'unit' => $this->unit,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Produk Diperbarui',
                'message' => "Data barang '{$this->name}' berhasil disimpan.",
            ]);
        } else {
            $product = Product::create([
                'store_id' => $store->id,
                'name' => $this->name,
                'category_id' => $this->category_id ?: null,
                'sku' => $this->sku ?: null,
                'barcode' => $this->barcode ?: null,
                'type' => $this->type,
                'buy_price' => $this->buy_price,
                'sell_price' => $this->sell_price,
                'stock' => 0,
                'min_stock' => $this->min_stock,
                'unit' => $this->unit,
                'is_active' => $this->is_active,
            ]);

            // If initial stock was provided, record initial stock-in movement
            if ($this->stock > 0) {
                $inventoryService->recordStockIn(
                    $product,
                    $this->stock,
                    $this->buy_price,
                    Auth::id(),
                    'Stok awal saat pendaftaran produk'
                );
            }

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Barang Ditambahkan',
                'message' => "Barang '{$this->name}' berhasil ditambahkan ke inventaris.",
            ]);
        }

        $this->showProductModal = false;
    }

    public function openStockAdjustModal(string $productId): void
    {
        $this->selectedProductForStock = Product::findOrFail($productId);
        $this->adjust_type = 'in';
        $this->adjust_quantity = 1;
        $this->adjust_notes = '';
        $this->resetValidation();
        $this->showStockAdjustModal = true;
    }

    public function submitStockAdjustment(InventoryService $inventoryService): void
    {
        $this->validate([
            'adjust_quantity' => ['required', 'integer', 'min:1'],
            'adjust_type' => ['required', 'in:in,out,adjustment'],
        ]);

        if (! $this->selectedProductForStock) {
            return;
        }

        try {
            if ($this->adjust_type === 'in') {
                $inventoryService->recordStockIn(
                    $this->selectedProductForStock,
                    $this->adjust_quantity,
                    (float) $this->selectedProductForStock->buy_price,
                    Auth::id(),
                    $this->adjust_notes ?: 'Penerimaan stok manual'
                );
            } elseif ($this->adjust_type === 'out') {
                $inventoryService->recordStockOut(
                    $this->selectedProductForStock,
                    $this->adjust_quantity,
                    Auth::id(),
                    $this->adjust_notes ?: 'Pengeluaran stok manual'
                );
            } elseif ($this->adjust_type === 'adjustment') {
                $inventoryService->adjustStock(
                    $this->selectedProductForStock,
                    $this->adjust_quantity,
                    Auth::id(),
                    $this->adjust_notes ?: 'Penyesuaian stok opname'
                );
            }

            $this->showStockAdjustModal = false;
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Stok Berhasil Diperbarui',
                'message' => "Perubahan stok untuk '{$this->selectedProductForStock->name}' berhasil dicatat.",
            ]);
        } catch (\Exception $e) {
            $this->addError('adjust_quantity', $e->getMessage());
        }
    }

    public function render()
    {
        $query = Product::with('category');

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%");
            });
        }

        if (! empty($this->typeFilter)) {
            $query->where('type', $this->typeFilter);
        }

        if (! empty($this->categoryFilter)) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->onlyLowStock) {
            $query->lowStock();
        }

        $products = $query->latest()->paginate(15);
        $categories = ProductCategory::orderBy('name')->get();

        // Stats
        $stats = [
            'total_items' => Product::count(),
            'low_stock_count' => Product::lowStock()->count(),
            'spareparts_count' => Product::where('type', 'sparepart')->count(),
            'accessories_count' => Product::where('type', 'accessory')->count(),
        ];

        return view('livewire.inventory.product-index', [
            'products' => $products,
            'categories' => $categories,
            'stats' => $stats,
        ])->layoutData([
            'title' => 'Manajemen Inventaris & Sparepart',
            'breadcrumbs' => [
                ['label' => 'Inventaris & Sparepart'],
            ],
        ]);
    }
}
