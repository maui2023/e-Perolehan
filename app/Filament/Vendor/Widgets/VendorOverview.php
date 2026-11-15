<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Bid;
use App\Models\Tender;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class VendorOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Aktiviti Bidaan Saya';

    protected function getStats(): array
    {
        $vendor = Auth::user()?->vendor;
        $myBidsCount = $vendor ? Bid::where('vendor_id', $vendor->id)->count() : 0;
        $openTenders = Tender::where('status', 'published')->count();
        $vendorStatus = $vendor ? ucfirst($vendor->status) : 'Belum Profil';

        return [
            Stat::make('Tender Dibuka Sekarang', $openTenders)
                ->description('Peluang perolehan sedia dibida')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),

            Stat::make('Penyertaan Bidaan Saya', $myBidsCount)
                ->description('Jumlah kertas bidaan dihantar')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('info'),

            Stat::make('Status Pendaftaran Pembekal', $vendorStatus)
                ->description($vendor && $vendor->isVerified() ? 'Akaun Disahkan Aktif' : 'Status profil syarikat')
                ->descriptionIcon($vendor && $vendor->isVerified() ? 'heroicon-m-check-badge' : 'heroicon-m-clock')
                ->color($vendor && $vendor->isVerified() ? 'success' : 'warning'),
        ];
    }
}
