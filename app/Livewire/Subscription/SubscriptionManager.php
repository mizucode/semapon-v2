<?php

namespace App\Livewire\Subscription;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SubscriptionManager extends Component
{
    public string $billingCycle = 'monthly'; // 'monthly', 'yearly'

    public bool $showCheckoutModal = false;

    public ?Plan $selectedPlanForCheckout = null;

    public ?SubscriptionPayment $activeInvoice = null;

    public function selectPlan(string $planId): void
    {
        $this->selectedPlanForCheckout = Plan::findOrFail($planId);
        $this->activeInvoice = null;
        $this->showCheckoutModal = true;
    }

    public function generateInvoice(TenantContext $tenantContext, SubscriptionService $subscriptionService): void
    {
        if (! $this->selectedPlanForCheckout) {
            return;
        }

        try {
            $store = $tenantContext->getStore();
            $this->activeInvoice = $subscriptionService->createInvoice(
                $store,
                $this->selectedPlanForCheckout,
                $this->billingCycle,
                'transfer'
            );

            $this->dispatch('toast', [
                'type' => 'info',
                'title' => 'Invoice Dibuat',
                'message' => "Invoice #{$this->activeInvoice->invoice_number} berhasil diterbitkan. Silakan lakukan pembayaran.",
            ]);
        } catch (Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Gagal Menerbitkan Invoice',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function simulatePay(SubscriptionService $subscriptionService): void
    {
        if (! $this->activeInvoice) {
            return;
        }

        try {
            $subscriptionService->confirmPayment($this->activeInvoice);
            $this->showCheckoutModal = false;

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Pembayaran Berhasil!',
                'message' => "Selamat, toko Anda kini telah berhasil di-upgrade ke paket {$this->selectedPlanForCheckout?->name}.",
            ]);
        } catch (Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Gagal Konfirmasi',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function render(TenantContext $tenantContext)
    {
        $store = $tenantContext->getStore();
        $plans = Plan::where('is_active', true)->get();

        $currentSubscription = Subscription::where('store_id', $store->id)
            ->latest('id')
            ->first();

        $invoices = SubscriptionPayment::where('store_id', $store->id)
            ->with('plan')
            ->latest()
            ->get();

        return view('livewire.subscription.subscription-manager', [
            'store' => $store,
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
            'invoices' => $invoices,
        ])->layoutData([
            'title' => 'Paket Langganan SaaS',
            'breadcrumbs' => [
                ['label' => 'Paket & Langganan'],
            ],
        ]);
    }
}
