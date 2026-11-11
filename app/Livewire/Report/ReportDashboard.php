<?php

namespace App\Livewire\Report;

use App\Services\FinanceService;
use App\Services\ReportService;
use App\Services\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
class ReportDashboard extends Component
{
    public string $activeTab = 'overview'; // 'overview', 'technicians', 'inventory', 'financial'

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function exportOrders(TenantContext $tenantContext, ReportService $reportService): StreamedResponse
    {
        $store = $tenantContext->getStore();
        $csvContent = $reportService->exportOrdersCsv($store, $this->startDate ?: null, $this->endDate ?: null);
        $fileName = 'rekap_servis_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render(TenantContext $tenantContext, ReportService $reportService, FinanceService $financeService)
    {
        $store = $tenantContext->getStore();

        $serviceStats = $reportService->getServiceStats($store, $this->startDate ?: null, $this->endDate ?: null);
        $techStats = $reportService->getTechnicianStats($store, $this->startDate ?: null, $this->endDate ?: null);
        $inventoryStats = $reportService->getInventoryStats($store);
        $financialSummary = $financeService->getFinancialSummary($store, $this->startDate ?: null, $this->endDate ?: null);

        return view('livewire.report.report-dashboard', [
            'serviceStats' => $serviceStats,
            'techStats' => $techStats,
            'inventoryStats' => $inventoryStats,
            'financialSummary' => $financialSummary,
        ])->layoutData([
            'title' => 'Laporan & Analisis Bisnis',
            'breadcrumbs' => [
                ['label' => 'Laporan & Analisis'],
            ],
        ]);
    }
}
