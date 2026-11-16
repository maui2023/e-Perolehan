<?php

namespace App\Filament\Widgets;

use App\Models\Bid;
use App\Models\Tender;
use Filament\Widgets\ChartWidget;

class ProcurementSpendChart extends ChartWidget
{
    protected ?string $heading = 'Analitik Perbelanjaan & Trend Nilai Kontrak';

    protected ?string $description = 'Perbandingan bulanan antara anggaran bajet tender vs nilai sebenar kontrak dianugerahkan (MYR)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i);
        });

        $startMin = now()->subMonths(5)->startOfMonth();
        $endMax = now()->endOfMonth();

        $tenders = Tender::whereBetween('created_at', [$startMin, $endMax])
            ->select(['id', 'estimated_budget', 'created_at'])
            ->get()
            ->groupBy(fn ($t) => $t->created_at?->format('Y-m'));

        $awardedBids = Bid::where('status', 'awarded')
            ->whereBetween('updated_at', [$startMin, $endMax])
            ->select(['id', 'decrypted_bid_amount', 'updated_at'])
            ->get()
            ->groupBy(fn ($b) => $b->updated_at?->format('Y-m'));

        $labels = [];
        $budgetData = [];
        $awardedData = [];

        foreach ($months as $month) {
            $ym = $month->format('Y-m');

            $labels[] = $month->translatedFormat('M Y');
            $budgetData[] = (float) ($tenders->get($ym)?->sum('estimated_budget') ?? 0);
            $awardedData[] = (float) ($awardedBids->get($ym)?->sum('decrypted_bid_amount') ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Anggaran Bajet Tender (RM)',
                    'data' => $budgetData,
                    'backgroundColor' => 'rgba(30, 58, 138, 0.75)',
                    'borderColor' => '#1e3a8a',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Nilai Kontrak Dianugerahkan (RM)',
                    'data' => $awardedData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.75)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 1,
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
