<?php

namespace App\Filament\Penilai\Widgets;

use App\Models\Evaluation;
use App\Models\Tender;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenilaiOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Tugas Penilaian & Integriti Peti';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $boxOpenedTenders = Tender::where('status', 'box_opened')->count();
        $evaluatingTenders = Tender::where('status', 'evaluating')->count();
        $closedTenders = Tender::where('status', 'closed')->count();
        $completedEvaluations = Evaluation::count();

        return [
            Stat::make('Peti Bidaan Dibuka', $boxOpenedTenders)
                ->description('Tender sedia dinilai oleh jawatankuasa')
                ->descriptionIcon('heroicon-m-folder-open')
                ->color('success')
                ->chart([1, 2, 2, 3, $boxOpenedTenders]),

            Stat::make('Sedang Dalam Penilaian', $evaluatingTenders)
                ->description('Pemarkahan teknikal & kewangan aktif')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary')
                ->chart([1, 1, 2, 2, $evaluatingTenders]),

            Stat::make('Menunggu Buka Peti Bidaan', $closedTenders)
                ->description('Menunggu pengesahan digital 2 saksi')
                ->descriptionIcon('heroicon-m-key')
                ->color('warning')
                ->chart([0, 1, 1, 2, $closedTenders]),

            Stat::make('Penilaian Selesai Ditandatangani', $completedEvaluations)
                ->description('Laporan matriks skor disahkan')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info')
                ->chart([2, 4, 6, 8, $completedEvaluations]),
        ];
    }
}
