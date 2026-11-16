<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Bid;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class VendorBidsChart extends ChartWidget
{
    protected ?string $heading = 'Status Penyertaan Bidaan Syarikat';

    protected ?string $description = 'Pecahan status bidaan saya: Sampul Berkunci, Dibuka Penilaian, dan Dianugerahkan';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $vendor = Auth::user()?->vendor;
        $vendorId = $vendor?->id;

        $counts = $vendorId
            ? Bid::where('vendor_id', $vendorId)
                ->whereIn('status', ['sealed', 'opened', 'awarded', 'rejected'])
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->all()
            : [];

        $sealed = (int) ($counts['sealed'] ?? 0);
        $opened = (int) ($counts['opened'] ?? 0);
        $awarded = (int) ($counts['awarded'] ?? 0);
        $rejected = (int) ($counts['rejected'] ?? 0);

        return [
            'datasets' => [
                [
                    'label' => 'Bilangan Bidaan',
                    'data' => [$sealed, $opened, $awarded, $rejected],
                    'backgroundColor' => [
                        '#f59e0b', // sealed (warning)
                        '#3b82f6', // opened (info)
                        '#10b981', // awarded (success)
                        '#ef4444', // rejected (danger)
                    ],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => [
                'Peti Berkunci (Sealed)',
                'Dibuka Penilaian (Opened)',
                'Dianugerahkan (Awarded)',
                'Tidak Berjaya (Unsuccessful)',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
