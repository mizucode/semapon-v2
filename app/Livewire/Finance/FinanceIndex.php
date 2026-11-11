<?php

namespace App\Livewire\Finance;

use App\Models\CashTransaction;
use App\Models\ExpenseCategory;
use App\Services\FinanceService;
use App\Services\TenantContext;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class FinanceIndex extends Component
{
    use WithPagination;

    public string $typeFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    // Modal: Record Expense
    public bool $showExpenseModal = false;

    public float $expense_amount = 0;

    public ?string $expense_category_id = null;

    public string $expense_method = 'cash';

    public string $expense_notes = '';

    // Modal: Manual Cash-in
    public bool $showCashInModal = false;

    public float $cash_in_amount = 0;

    public string $cash_in_category = 'modal_awal';

    public string $cash_in_desc = '';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function openExpenseModal(): void
    {
        $this->reset(['expense_amount', 'expense_category_id', 'expense_notes']);
        $this->expense_method = 'cash';
        $this->resetValidation();
        $this->showExpenseModal = true;
    }

    public function submitExpense(TenantContext $tenantContext, FinanceService $financeService): void
    {
        $this->validate([
            'expense_amount' => ['required', 'numeric', 'min:1'],
            'expense_category_id' => ['nullable', 'exists:expense_categories,id'],
            'expense_notes' => ['required', 'string', 'max:255'],
        ]);

        try {
            $store = $tenantContext->getStore();
            $financeService->recordExpense(
                $store,
                $this->expense_amount,
                $this->expense_category_id ?: null,
                $this->expense_method,
                Auth::id(),
                $this->expense_notes
            );

            $this->showExpenseModal = false;
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Pengeluaran Dicatat',
                'message' => 'Biaya operasional berhasil dibukukan ke kas toko.',
            ]);
        } catch (Exception $e) {
            $this->addError('expense_amount', $e->getMessage());
        }
    }

    public function openCashInModal(): void
    {
        $this->reset(['cash_in_amount', 'cash_in_desc']);
        $this->cash_in_category = 'modal_awal';
        $this->resetValidation();
        $this->showCashInModal = true;
    }

    public function submitCashIn(TenantContext $tenantContext): void
    {
        $this->validate([
            'cash_in_amount' => ['required', 'numeric', 'min:1'],
            'cash_in_desc' => ['required', 'string', 'max:255'],
        ]);

        $store = $tenantContext->getStore();

        CashTransaction::create([
            'store_id' => $store->id,
            'user_id' => Auth::id(),
            'type' => 'in',
            'amount' => $this->cash_in_amount,
            'category' => $this->cash_in_category,
            'description' => $this->cash_in_desc,
        ]);

        $this->showCashInModal = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Pemasukan Kas Dicatat',
            'message' => 'Saldo kas berhasil ditambahkan.',
        ]);
    }

    public function render(TenantContext $tenantContext, FinanceService $financeService)
    {
        $store = $tenantContext->getStore();
        $summary = $financeService->getFinancialSummary(
            $store,
            $this->startDate ?: null,
            $this->endDate ?: null
        );

        $query = CashTransaction::with('user');

        if (! empty($this->typeFilter)) {
            $query->where('type', $this->typeFilter);
        }

        if (! empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if (! empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        $transactions = $query->latest()->paginate(20);
        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view('livewire.finance.finance-index', [
            'summary' => $summary,
            'transactions' => $transactions,
            'expenseCategories' => $expenseCategories,
        ])->layoutData([
            'title' => 'Buku Kas & Arus Keuangan',
            'breadcrumbs' => [
                ['label' => 'Kas & Keuangan'],
            ],
        ]);
    }
}
