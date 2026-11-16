<?php

namespace App\Filament\Widgets;

use App\Models\Tender;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentTendersTable extends TableWidget
{
    protected static ?string $heading = 'Tender Terkini & Status Peti Bidaan';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tender::query()
                    ->withCount('bids')
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('reference_no')
                    ->label('No. Rujukan')
                    ->badge()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label('Tajuk Perolehan')
                    ->limit(45)
                    ->tooltip(fn ($record) => $record->title),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'supply' => 'Bekalan',
                        'service' => 'Perkhidmatan',
                        'work' => 'Kerja',
                        'consultancy' => 'Perundingan',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'supply' => 'info',
                        'service' => 'success',
                        'work' => 'warning',
                        'consultancy' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('estimated_budget')
                    ->label('Anggaran Bajet')
                    ->money('MYR')
                    ->sortable(),

                TextColumn::make('bids_count')
                    ->label('Bidaan Diterima')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => "{$state} Sampul"),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draf',
                        'published' => 'Bidaan Dibuka',
                        'closed' => 'Peti Ditutup',
                        'box_opened' => 'Peti Dibuka Saksi',
                        'evaluating' => 'Dalam Penilaian',
                        'awarded' => 'Dianugerahkan',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'published' => 'info',
                        'closed' => 'warning',
                        'box_opened' => 'primary',
                        'evaluating' => 'danger',
                        'awarded' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('closing_at')
                    ->label('Tarikh Tutup')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('Tiada'),
            ])
            ->paginated(false);
    }
}
