<?php

namespace App\Livewire\Order;

use App\Models\ServiceOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.base')]
class PrintReceipt extends Component
{
    public ServiceOrder $order;

    #[Url]
    public string $format = 'thermal'; // 'thermal' (58/80mm) or 'standard' (A4)

    public function mount(string $id, ?string $format = null): void
    {
        if ($format && in_array($format, ['thermal', 'standard'])) {
            $this->format = $format;
        } elseif (request()->query('format') && in_array(request()->query('format'), ['thermal', 'standard'])) {
            $this->format = request()->query('format');
        }

        $this->order = ServiceOrder::with([
            'store',
            'customer',
            'device',
            'technician',
            'items',
        ])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.order.print-receipt', [
            'store' => $this->order->store,
        ])->layoutData([
            'title' => "Nota Servis #{$this->order->order_number}",
        ]);
    }
}
