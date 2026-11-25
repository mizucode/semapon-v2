<?php

namespace App\Livewire\Order;

use App\Models\ServiceOrder;
use App\Services\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.base')]
class PrintReceipt extends Component
{
    public ServiceOrder $order;

    #[Url]
    public string $format = 'thermal'; // 'thermal' (58/80mm), 'a5' (Kertas A5), or 'a4' (Kertas A4)

    #[Url]
    public string $type = 'receipt'; // 'receipt' (Nota Servis) or 'intake' (Tanda Terima)

    public function mount(string $id, ?string $format = null, ?string $type = null): void
    {
        if ($type && in_array($type, ['receipt', 'intake'], true)) {
            $this->type = $type;
        } elseif (request()->query('type') && in_array(request()->query('type'), ['receipt', 'intake'], true)) {
            $this->type = request()->query('type');
        }

        $requestedFormat = $format ?? request()->query('format');
        if ($requestedFormat && in_array($requestedFormat, ['thermal', 'standard', 'a4', 'a5'], true)) {
            $this->format = $requestedFormat === 'standard' ? 'a4' : $requestedFormat;
        }

        // Tanda terima servis hanya untuk format cetak lembar fisik (A5 / A4), bukan thermal
        if ($this->type === 'intake' && $this->format === 'thermal') {
            $this->format = 'a5';
        }

        $this->order = ServiceOrder::with([
            'store',
            'customer',
            'device',
            'technician',
            'items',
        ])->findOrFail($id);

        if (app(TenantContext::class)->isTechnician() && $this->order->technician_id !== auth()->id()) {
            abort(403, 'Order servis ini tidak ditugaskan kepada Anda.');
        }
    }

    public function render()
    {
        $docTitle = $this->type === 'intake'
            ? "Tanda Terima Servis #{$this->order->order_number}"
            : "Nota Servis #{$this->order->order_number}";

        return view('livewire.order.print-receipt', [
            'store' => $this->order->store,
        ])->layoutData([
            'title' => $docTitle,
        ]);
    }
}
