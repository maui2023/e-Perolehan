<?php

namespace App\Filament\Widgets;

use App\Models\Tender;
use Filament\Widgets\ChartWidget;

class TenderStatusPipelineChart extends ChartWidget
{
    protected ?string $heading = 'Saluran Kitaran & Status Peti Bidaan';

    protected ?string $description = 'Visualisasi status proses perolehan dari fasa Draf sehingga Penganugerahan';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    protected function getData(): array
    {
        $statuses = [
            'draft' => ['label' => 'Draf Urus Setia', 'color' => '#94a3b8'],
            'published' => ['label' => 'Bidaan Dibuka', 'color' => '#0284c7'],
            'closed' => ['label' => 'Peti Ditutup', 'color' => '#f59e0b'],
            'box_opened' => ['label' => 'Peti Dibuka Saksi', 'color' => '#8b5cf6'],
            'evaluating' => ['label' => 'Dalam Penilaian', 'color' => '#ec4899'],
            'awarded' => ['label' => 'Dianugerahkan', 'color' => '#10b981'],
        ];

        $statusCounts = Tender::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $labels = [];
        $data = [];
        $backgroundColors = [];

        foreach ($statuses as $status => $info) {
            $count = (int) ($statusCounts[$status] ?? 0);
            $labels[] = $info['label'];
            $data[] = $count;
            $backgroundColors[] = $info['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Tender',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
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
