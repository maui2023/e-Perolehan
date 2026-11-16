<?php

namespace App\Filament\Widgets;

use App\Models\Tender;
use Filament\Widgets\ChartWidget;

class TendersByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Pecahan Perolehan Mengikut Kategori';

    protected ?string $description = 'Taburan portfolio tender merentasi Bekalan, Perkhidmatan, Kerja, dan Perundingan';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    protected function getData(): array
    {
        $categories = [
            'supply' => ['label' => 'Bekalan (Supply)', 'color' => '#3b82f6'],
            'service' => ['label' => 'Perkhidmatan (Service)', 'color' => '#10b981'],
            'work' => ['label' => 'Kerja (Work)', 'color' => '#f59e0b'],
            'consultancy' => ['label' => 'Perundingan (Consultancy)', 'color' => '#8b5cf6'],
        ];

        $categoryCounts = Tender::query()
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->all();

        $labels = [];
        $data = [];
        $backgroundColors = [];

        foreach ($categories as $key => $info) {
            $count = (int) ($categoryCounts[$key] ?? 0);
            $labels[] = $info['label'];
            $data[] = $count;
            $backgroundColors[] = $info['color'];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
