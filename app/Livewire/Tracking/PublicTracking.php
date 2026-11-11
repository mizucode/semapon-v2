<?php

namespace App\Livewire\Tracking;

use App\Models\ServiceOrder;
use App\Services\TrackingTokenService;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public-tracking')]
class PublicTracking extends Component
{
    public string $token = '';

    public ?ServiceOrder $order = null;

    public bool $isInvalid = false;

    public string $errorMessage = '';

    public function mount(string $token, TrackingTokenService $tokenService, Request $request): void
    {
        $this->token = trim($token);

        // Resolve order safely via token service
        $resolvedOrder = $tokenService->resolveOrder($this->token, $request->ip());

        // Fallback for direct token lookup on order model (if legacy or token table fallback)
        if (! $resolvedOrder) {
            $resolvedOrder = ServiceOrder::withoutGlobalScopes()
                ->with(['store', 'customer', 'device', 'items'])
                ->where('tracking_token', $this->token)
                ->first();
        }

        if (! $resolvedOrder) {
            $this->isInvalid = true;
            $this->errorMessage = 'Tautan pelacakan servis tidak valid atau sudah kedaluwarsa.';

            return;
        }

        $this->order = $resolvedOrder;
    }

    public function approveEstimate(): void
    {
        if (! $this->order || $this->order->status !== 'waiting_approval') {
            return;
        }

        $this->order->customer_approved_at = now();
        $this->order->status = 'in_progress';
        $this->order->save();

        $this->order->histories()->create([
            'store_id' => $this->order->store_id,
            'user_id' => null,
            'from_status' => 'waiting_approval',
            'to_status' => 'in_progress',
            'notes' => 'Pelanggan menyetujui estimasi biaya melalui halaman tracking online.',
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Estimasi Disetujui',
            'message' => 'Terima kasih, teknisi kami akan segera memproses perbaikan perangkat Anda.',
        ]);

        $this->order->refresh();
    }

    public function rejectEstimate(): void
    {
        if (! $this->order || $this->order->status !== 'waiting_approval') {
            return;
        }

        $this->order->status = 'cancelled';
        $this->order->save();

        $this->order->histories()->create([
            'store_id' => $this->order->store_id,
            'user_id' => null,
            'from_status' => 'waiting_approval',
            'to_status' => 'cancelled',
            'notes' => 'Pelanggan membatalkan perbaikan melalui halaman tracking online.',
        ]);

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Perbaikan Dibatalkan',
            'message' => 'Pemberitahuan pembatalan telah diteruskan ke toko. Anda dapat mengambil unit kembali.',
        ]);

        $this->order->refresh();
    }

    public function render()
    {
        $store = $this->order?->store;

        return view('livewire.tracking.public-tracking', [
            'order' => $this->order,
            'store' => $store,
        ])->layout('components.layouts.public-tracking', [
            'title' => $this->order ? "Order #{$this->order->order_number}" : 'Lacak Status Servis',
            'storeName' => $store?->name ?? 'SEMAPON Servis HP',
            'storePhone' => $store?->phone ?? null,
        ]);
    }
}
