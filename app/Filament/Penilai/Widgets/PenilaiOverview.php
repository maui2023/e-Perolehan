<?php

namespace App\Filament\Penilai\Widgets;

use App\Models\Tender;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenilaiOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Tugas Penilaian';

    protected function getStats(): array
    {
        $boxOpenedTenders = Tender::where('status', 'box_opened')->count();
        $evaluatingTenders = Tender::where('status', 'evaluating')->count();
        $closedTenders = Tender::where('status', 'closed')->count();

        return [
            Stat::make('Peti Bidaan Dibuka', $boxOpenedTenders)
                ->description('Tender sedia dinilai oleh jawatankuasa')
                ->descriptionIcon('heroicon-m-folder-open')
                ->color('success'),

            Stat::make('Sedang Dalam Penilaian', $evaluatingTenders)
                ->description('Pemarkahan teknikal & harga sedang berjalan')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary'),

            Stat::make('Menunggu Buka Peti Bidaan', $closedTenders)
                ->description('Menunggu proses pengesahan 2 saksi')
                ->descriptionIcon('heroicon-m-key')
                ->color('warning'),
        ];
    }
}
