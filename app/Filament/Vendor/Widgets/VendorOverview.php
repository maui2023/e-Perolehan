<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Bid;
use App\Models\Tender;
use App\Models\TenderPurchase;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class VendorOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Aktiviti & Prestasi Bidaan Saya';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $vendor = Auth::user()?->vendor;
        $vendorId = $vendor?->id;

        $openTenders = Tender::where('status', 'published')->count();

        $bidAgg = $vendorId
            ? Bid::where('vendor_id', $vendorId)
                ->selectRaw("count(*) as total, count(case when status = 'awarded' then 1 end) as awarded")
                ->first()
            : null;
        $myBidsCount = (int) ($bidAgg->total ?? 0);
        $awardedBidsCount = (int) ($bidAgg->awarded ?? 0);

        $purchasedDocsCount = $vendorId ? TenderPurchase::where('vendor_id', $vendorId)->where('status', 'paid')->count() : 0;

        $complianceStatusText = match ($vendor?->compliance_status) {
            'verified' => 'Disahkan Aktif',
            'pending' => 'Menunggu Semakan',
            'rejected' => 'Perlu Tindakan',
            default => 'Belum Profil',
        };

        return [
            Stat::make('Tender Dibuka Sekarang', $openTenders)
                ->description('Peluang perolehan sedia dibida')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary')
                ->chart([2, 3, 5, 4, 6, $openTenders]),

            Stat::make('Penyertaan Bidaan Saya', $myBidsCount)
                ->description("{$awardedBidsCount} kontrak berjaya dimenangi")
                ->descriptionIcon('heroicon-m-document-check')
                ->color('info')
                ->chart([1, 1, 2, 2, 3, $myBidsCount]),

            Stat::make('Dokumen Tender Dibeli', $purchasedDocsCount)
                ->description('Resit sah dimuat turun')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success')
                ->chart([1, 2, 2, 3, 4, $purchasedDocsCount]),

            Stat::make('Status Pematuhan Syarikat', $complianceStatusText)
                ->description($vendor && $vendor->isVerified() ? 'Pematuhan Integriti Lengkap' : 'Sila kemas kini dokumen')
                ->descriptionIcon($vendor && $vendor->isVerified() ? 'heroicon-m-check-badge' : 'heroicon-m-clock')
                ->color($vendor && $vendor->isVerified() ? 'success' : 'warning'),
        ];
    }
}
