<?php

namespace App\Filament\Widgets;

use App\Models\Bid;
use App\Models\Tender;
use App\Models\TenderPurchase;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProcurementOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Papan Pemuka Eksekutif & Ringkasan Perolehan';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenderAgg = Tender::query()
            ->selectRaw("count(*) as total, count(case when status = 'published' then 1 end) as published, coalesce(sum(estimated_budget), 0) as budget")
            ->first();
        $totalTenders = (int) ($tenderAgg->total ?? 0);
        $openTenders = (int) ($tenderAgg->published ?? 0);
        $totalBudget = (float) ($tenderAgg->budget ?? 0);

        $vendorCounts = Vendor::query()
            ->selectRaw('compliance_status, count(*) as count')
            ->groupBy('compliance_status')
            ->pluck('count', 'compliance_status')
            ->all();
        $totalVendors = array_sum($vendorCounts);
        $verifiedVendors = (int) ($vendorCounts['verified'] ?? 0);
        $pendingVendors = (int) ($vendorCounts['pending'] ?? 0);

        $bidAgg = Bid::query()
            ->selectRaw("count(*) as total, count(case when status = 'sealed' then 1 end) as sealed, coalesce(sum(case when status = 'awarded' then decrypted_bid_amount end), 0) as awarded_sum")
            ->first();
        $totalBids = (int) ($bidAgg->total ?? 0);
        $sealedBids = (int) ($bidAgg->sealed ?? 0);
        $totalAwardedValue = (float) ($bidAgg->awarded_sum ?? 0);

        // Calculate savings on awarded tenders using aggregated sums
        $awardedBudgetTotal = (float) Tender::where('status', 'awarded')->sum('estimated_budget');
        $awardedActualTotal = $totalAwardedValue;
        $savings = max(0, $awardedBudgetTotal - $awardedActualTotal);
        $savingsPercentage = $awardedBudgetTotal > 0 ? round(($savings / $awardedBudgetTotal) * 100, 1) : 0;

        $purchaseAgg = TenderPurchase::where('status', 'paid')
            ->selectRaw('count(*) as count, coalesce(sum(amount), 0) as total')
            ->first();
        $documentRevenue = (float) ($purchaseAgg->total ?? 0);
        $totalPurchases = (int) ($purchaseAgg->count ?? 0);

        return [
            Stat::make('Jumlah Tender / Sebut Harga', $totalTenders)
                ->description("{$openTenders} tender aktif dibuka untuk bidaan")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart([4, 6, 8, 9, 12, 14, $totalTenders]),

            Stat::make('Nilai Peruntukan Bajet', 'RM '.number_format($totalBudget, 2))
                ->description('Jumlah siling peruntukan merentasi 16 projek')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info')
                ->chart([350000, 890000, 1200000, 2400000, 4800000, 6500000, (int) $totalBudget]),

            Stat::make('Kontrak Dianugerahkan', 'RM '.number_format($totalAwardedValue, 2))
                ->description('Penjimatan kos: RM '.number_format($savings, 2)." ({$savingsPercentage}%)")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success')
                ->chart([265000, 653000, 1500000, (int) $totalAwardedValue]),

            Stat::make('Peti Bidaan Tertutup (Sealed Bids)', $totalBids)
                ->description("{$sealedBids} sampul bidaan kriptografi berkunci")
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning')
                ->chart([2, 5, 8, 11, 14, 16, $totalBids]),

            Stat::make('Pembekal Berdaftar', $totalVendors)
                ->description("{$verifiedVendors} disahkan | {$pendingVendors} menunggu semakan")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->chart([3, 5, 7, 9, 11, $totalVendors]),

            Stat::make('Hasil Jualan Dokumen', 'RM '.number_format($documentRevenue, 2))
                ->description("Daripada {$totalPurchases} transaksi pembelian sah")
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('success')
                ->chart([150, 400, 850, 1200, 1500, (int) $documentRevenue]),
        ];
    }
}
