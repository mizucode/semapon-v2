<?php

namespace App\Livewire\Mobile;

use App\Models\Product;
use App\Models\ServiceOrder;
use App\Services\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Menu & Lainnya', 'breadcrumbs' => [['label' => 'Menu & Lainnya']]])]
class MobileMenu extends Component
{
    public function render(): View
    {
        $context = app(TenantContext::class);
        $store = $context->getStore();
        $role = $context->getRole() ?? 'owner';
        $user = Auth::user();

        $activeOrdersCount = ServiceOrder::whereIn('status', [
            'received', 'diagnosing', 'waiting_approval', 'in_progress', 'waiting_parts', 'testing', 'pending',
        ])->count();

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        return view('livewire.mobile.mobile-menu', [
            'user' => $user,
            'store' => $store,
            'role' => $role,
            'activeOrdersCount' => $activeOrdersCount,
            'lowStockCount' => $lowStockCount,
        ]);
    }
}
