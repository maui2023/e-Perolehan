<?php

namespace App\Filament\Widgets;

use App\Models\Bid;
use App\Models\Tender;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProcurementOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Sistem Perolehan';

    protected function getStats(): array
    {
        $totalTenders = Tender::count();
        $openTenders = Tender::where('status', 'published')->count();
        $totalVendors = Vendor::count();
        $verifiedVendors = Vendor::where('status', 'verified')->count();
        $totalBids = Bid::count();
        $sealedBids = Bid::where('status', 'sealed')->count();

        return [
            Stat::make('Jumlah Tender / Sebut Harga', $totalTenders)
                ->description("{$openTenders} tender sedang dibuka untuk bidaan")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart([3, 5, 4, 6, 8, 7, $totalTenders]),

            Stat::make('Pembekal Berdaftar', $totalVendors)
                ->description("{$verifiedVendors} pembekal berstatus disahkan")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success')
                ->chart([1, 2, 4, 3, 5, 4, $totalVendors]),

            Stat::make('Peti Bidaan Tertutup (Sealed Bids)', $totalBids)
                ->description("{$sealedBids} bidaan masih berkunci")
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning')
                ->chart([0, 1, 3, 2, 4, 5, $totalBids]),
        ];
    }
}
