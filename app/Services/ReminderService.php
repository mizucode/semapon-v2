<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Reminder;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\User;

class ReminderService
{
    /**
     * Scan database for alertable events and generate reminders without duplication.
     */
    public function scanAndGenerateReminders(Store $store): int
    {
        $createdCount = 0;

        // 1. Scan Overdue Orders (Lewat estimasi selesai)
        $overdueOrders = ServiceOrder::withoutGlobalScopes()
            ->where('store_id', $store->id)
            ->whereNotIn('status', ['completed', 'cancelled', 'unrepairable'])
            ->whereNotNull('estimated_completion_at')
            ->where('estimated_completion_at', '<', now())
            ->get();

        foreach ($overdueOrders as $order) {
            $exists = Reminder::withoutGlobalScopes()
                ->where('store_id', $store->id)
                ->where('reference_type', 'service_order')
                ->where('reference_id', $order->id)
                ->where('type', 'overdue_order')
                ->where('is_read', false)
                ->exists();

            if (! $exists) {
                Reminder::create([
                    'store_id' => $store->id,
                    'user_id' => $order->technician_id,
                    'type' => 'overdue_order',
                    'title' => "Order #{$order->order_number} Terlambat",
                    'message' => "Order servis perbaikan telah melewati estimasi tanggal selesai ({$order->estimated_completion_at->format('d/m/Y')}). Segera tindak lanjuti.",
                    'reference_type' => 'service_order',
                    'reference_id' => $order->id,
                ]);
                $createdCount++;
            }
        }

        // 2. Scan Ready for Pickup but Unpicked > 3 days
        $unpickedOrders = ServiceOrder::withoutGlobalScopes()
            ->where('store_id', $store->id)
            ->where('status', 'ready_for_pickup')
            ->where('updated_at', '<', now()->subDays(3))
            ->get();

        foreach ($unpickedOrders as $order) {
            $exists = Reminder::withoutGlobalScopes()
                ->where('store_id', $store->id)
                ->where('reference_type', 'service_order')
                ->where('reference_id', $order->id)
                ->where('type', 'ready_unpicked')
                ->where('is_read', false)
                ->exists();

            if (! $exists) {
                Reminder::create([
                    'store_id' => $store->id,
                    'type' => 'ready_unpicked',
                    'title' => "Unit Belum Diambil: #{$order->order_number}",
                    'message' => "Perangkat milik {$order->customer?->name} telah selesai lebih dari 3 hari yang lalu namun belum diambil. Kirim pengingat WhatsApp.",
                    'reference_type' => 'service_order',
                    'reference_id' => $order->id,
                ]);
                $createdCount++;
            }
        }

        // 3. Scan Low Stock Products
        $lowStockProducts = Product::withoutGlobalScopes()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->get();

        foreach ($lowStockProducts as $prod) {
            $exists = Reminder::withoutGlobalScopes()
                ->where('store_id', $store->id)
                ->where('reference_type', 'product')
                ->where('reference_id', $prod->id)
                ->where('type', 'low_stock')
                ->where('is_read', false)
                ->exists();

            if (! $exists) {
                Reminder::create([
                    'store_id' => $store->id,
                    'type' => 'low_stock',
                    'title' => "Stok Menipis: {$prod->name}",
                    'message' => "Sisa stok tinggal {$prod->stock} {$prod->unit} (Batas minimum: {$prod->min_stock} {$prod->unit}). Segera lakukan kulakan PO.",
                    'reference_type' => 'product',
                    'reference_id' => $prod->id,
                ]);
                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Notify technician of a newly assigned repair order.
     */
    public function notifyTechnicianAssignment(ServiceOrder $order, User $technician): Reminder
    {
        return Reminder::create([
            'store_id' => $order->store_id,
            'user_id' => $technician->id,
            'type' => 'tech_assigned',
            'title' => "Tugas Servis Baru: #{$order->order_number}",
            'message' => "Anda telah ditugaskan untuk menangani order #{$order->order_number} ({$order->device?->brand} {$order->device?->model}).",
            'reference_type' => 'service_order',
            'reference_id' => $order->id,
        ]);
    }

    /**
     * Mark all active reminders as read for a store.
     */
    public function markAllAsRead(Store $store): void
    {
        Reminder::where('store_id', $store->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Scan database across all active stores for alerts.
     */
    public function scanAllStores(): int
    {
        $total = 0;
        $stores = Store::where('status', 'active')->get();

        foreach ($stores as $store) {
            $total += $this->scanAndGenerateReminders($store);
        }

        return $total;
    }

    /**
     * Get count of unread reminders for current store.
     */
    public function getUnreadCount(Store $store): int
    {
        return Reminder::where('store_id', $store->id)
            ->where('is_read', false)
            ->count();
    }
}
