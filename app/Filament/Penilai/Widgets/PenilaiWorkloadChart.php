<?php

namespace App\Filament\Penilai\Widgets;

use App\Models\Tender;
use Filament\Widgets\ChartWidget;

class PenilaiWorkloadChart extends ChartWidget
{
    protected ?string $heading = 'Beban Tugas Penilaian Mengikut Kategori';

    protected ?string $description = 'Jumlah tender sedia dinilai dan dalam penilaian merentasi sektor';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $categories = [
            'supply' => 'Bekalan',
            'service' => 'Perkhidmatan',
            'work' => 'Kerja',
            'consultancy' => 'Perundingan',
        ];

        $tenderCounts = Tender::whereIn('status', ['box_opened', 'evaluating'])
            ->selectRaw('category, status, count(*) as count')
            ->groupBy('category', 'status')
            ->get();

        $grouped = [];
        foreach ($tenderCounts as $row) {
            $grouped[$row->category][$row->status] = (int) $row->count;
        }

        $labels = [];
        $boxOpenedData = [];
        $evaluatingData = [];

        foreach ($categories as $cat => $label) {
            $labels[] = $label;
            $boxOpenedData[] = $grouped[$cat]['box_opened'] ?? 0;
            $evaluatingData[] = $grouped[$cat]['evaluating'] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Peti Dibuka (Sedia Dinilai)',
                    'data' => $boxOpenedData,
                    'backgroundColor' => '#10b981',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Sedang Dinilai (Teknikal / Kewangan)',
                    'data' => $evaluatingData,
                    'backgroundColor' => '#3b82f6',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
