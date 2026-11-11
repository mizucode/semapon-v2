<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceOrderPart;
use App\Models\Store;
use App\Models\TechnicianProfile;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get service orders statistics and volume within a date range.
     */
    public function getServiceStats(Store $store, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = ServiceOrder::where('store_id', $store->id);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $totalOrders = (clone $query)->count();
        $completedOrders = (clone $query)->where('status', 'completed')->count();
        $cancelledOrders = (clone $query)->whereIn('status', ['cancelled', 'unrepairable'])->count();
        $inProgressOrders = (clone $query)->whereNotIn('status', ['completed', 'cancelled', 'unrepairable'])->count();
        $totalRevenue = (float) (clone $query)->where('status', 'completed')->sum('total_cost');

        // Popular services
        $popularServices = ServiceOrderItem::where('store_id', $store->id)
            ->select('name', DB::raw('count(*) as count'), DB::raw('sum(price) as total_amount'))
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'in_progress_orders' => $inProgressOrders,
            'total_revenue' => $totalRevenue,
            'popular_services' => $popularServices,
        ];
    }

    /**
     * Get technician productivity and commission earnings report.
     */
    public function getTechnicianStats(Store $store, ?string $startDate = null, ?string $endDate = null): array
    {
        $technicians = TechnicianProfile::where('store_id', $store->id)
            ->with('user')
            ->get();

        $stats = [];
        foreach ($technicians as $tech) {
            $orderQuery = ServiceOrder::where('store_id', $store->id)
                ->where('technician_id', $tech->user_id);

            if ($startDate) {
                $orderQuery->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $orderQuery->whereDate('created_at', '<=', $endDate);
            }

            $totalAssigned = (clone $orderQuery)->count();
            $completed = (clone $orderQuery)->where('status', 'completed')->count();
            $totalLabor = (float) (clone $orderQuery)->where('status', 'completed')->sum('total_cost');

            // Commission calculation based on tech rate
            $commission = $tech->commission_rate > 0
                ? ($totalLabor * ($tech->commission_rate / 100))
                : 0;

            $stats[] = [
                'technician_id' => $tech->user_id,
                'name' => $tech->user?->name ?? 'Teknisi',
                'specialization' => $tech->specialization,
                'total_assigned' => $totalAssigned,
                'completed' => $completed,
                'total_revenue' => $totalLabor,
                'commission_earned' => $commission,
            ];
        }

        return $stats;
    }

    /**
     * Get inventory valuation, low stock count, and top used spareparts.
     */
    public function getInventoryStats(Store $store): array
    {
        $products = Product::where('store_id', $store->id)->get();

        $totalItems = $products->count();
        $totalUnits = $products->sum('stock');
        $totalValuation = $products->sum(function (Product $p) {
            return $p->stock * (float) $p->buy_price;
        });
        $lowStockCount = $products->filter(function (Product $p) {
            return $p->isLowStock();
        })->count();

        // Top used spareparts in repair orders
        $topParts = ServiceOrderPart::where('store_id', $store->id)
            ->with('product')
            ->select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(subtotal) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return [
            'total_items' => $totalItems,
            'total_units' => $totalUnits,
            'total_valuation' => $totalValuation,
            'low_stock_count' => $lowStockCount,
            'top_parts' => $topParts,
        ];
    }

    /**
     * Export service orders as standard CSV string.
     */
    public function exportOrdersCsv(Store $store, ?string $startDate = null, ?string $endDate = null): string
    {
        $query = ServiceOrder::where('store_id', $store->id)
            ->with(['customer', 'device', 'technician'])
            ->latest();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $orders = $query->get();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['No. Order', 'Tanggal Masuk', 'Pelanggan', 'No. HP', 'Tipe Perangkat', 'Status', 'Total Biaya (Rp)', 'Terbayar (Rp)', 'Teknisi']);

        foreach ($orders as $o) {
            fputcsv($handle, [
                $o->order_number,
                $o->created_at->format('Y-m-d H:i'),
                $o->customer?->name ?? 'Umum',
                $o->customer?->phone ?? '-',
                ($o->device?->brand ?? '').' '.($o->device?->model ?? ''),
                $o->status,
                (float) $o->total_cost,
                (float) $o->paid_amount,
                $o->technician?->name ?? '-',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
