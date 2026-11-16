<?php

namespace App\Filament\Penilai\Resources\Tenders\RelationManagers;

use App\Models\Bid;
use App\Models\Tender;
use App\Services\EvaluationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BidsRelationManager extends RelationManager
{
    protected static string $relationship = 'bids';

    protected static ?string $title = 'Senarai Bidaan Bertutup (Penilaian Dwi-Buta)';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function canEdit(Model $record): bool
    {
        return false;
    }

    public function canDelete(Model $record): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['documents', 'evaluations', 'tender']))
            ->recordTitleAttribute('blind_code')
            ->columns([
                TextColumn::make('blind_code')
                    ->label('Kod Dwi-Buta')
                    ->weight('bold')
                    ->searchable()
                    ->placeholder('Menunggu Pembukaan Saksi')
                    ->description(fn (Bid $record): string => "Tempoh: {$record->completion_duration_months} Bulan"),
                TextColumn::make('technical_summary')
                    ->label('Ringkasan Teknikal')
                    ->limit(35)
                    ->tooltip(fn (Bid $record): ?string => $record->technical_summary)
                    ->placeholder('Tiada ringkasan'),
                TextColumn::make('technical_score')
                    ->label('Skor Teknikal')
                    ->badge()
                    ->state(function (Bid $record): string {
                        if ($record->technical_score === null) {
                            return 'Belum Dinilai';
                        }
                        $min = $record->tender->min_technical_score ?? 70;
                        $pass = $record->technical_score >= $min ? 'LULUS' : 'GAGAL';

                        return "{$record->technical_score}% ({$pass})";
                    })
                    ->color(function (string $state): string {
                        if (str_contains($state, 'LULUS')) {
                            return 'success';
                        }
                        if (str_contains($state, 'GAGAL')) {
                            return 'danger';
                        }

                        return 'gray';
                    }),
                TextColumn::make('financial_score')
                    ->label('Skor Kewangan')
                    ->badge()
                    ->state(function (Bid $record): string {
                        if ($record->financial_score === null) {
                            return '-';
                        }

                        return "{$record->financial_score}%";
                    })
                    ->color(fn ($state) => $state === '-' ? 'gray' : 'info'),
                TextColumn::make('total_score')
                    ->label('Skor Komposit')
                    ->weight('bold')
                    ->state(function (Bid $record): string {
                        if ($record->total_score === null) {
                            return '-';
                        }

                        return "{$record->total_score}%";
                    })
                    ->color(fn ($state) => $state === '-' ? 'gray' : 'primary'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'submitted', 'sealed' => 'Terkunci (Sealed)',
                        'opened' => 'Dibuka (Sedia Dinilai)',
                        'passed_technical' => 'Lulus Teknikal',
                        'failed_technical' => 'Gagal Teknikal',
                        'awarded' => 'Dianugerahkan',
                        'rejected' => 'Tidak Berjaya',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'submitted', 'sealed' => 'warning',
                        'opened' => 'primary',
                        'passed_technical' => 'info',
                        'failed_technical', 'rejected' => 'danger',
                        'awarded' => 'success',
                        default => 'gray',
                    }),
            ])
            ->headerActions([
                Action::make('evaluation_report_pdf')
                    ->label('Laporan Penilaian (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn (): bool => $this->getOwnerRecord()->isBoxOpened())
                    ->url(fn (): string => route('tenders.evaluation-report', $this->getOwnerRecord()->id), shouldOpenInNewTab: true),
            ])
            ->recordActions([
                Action::make('download_technical_doc')
                    ->label('Dokumen Teknikal')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(function (Bid $record): bool {
                        return $record->documents->where('is_financial', false)->isNotEmpty();
                    })
                    ->url(function (Bid $record): ?string {
                        $doc = $record->documents->where('is_financial', false)->first();

                        return $doc ? route('bids.documents.download', $doc->id) : null;
                    }, shouldOpenInNewTab: true),

                Action::make('evaluate_technical')
                    ->label('Nilai Teknikal')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(function (Bid $record): bool {
                        /** @var Tender $tender */
                        $tender = $this->getOwnerRecord();

                        return $tender->isBoxOpened() && $tender->status !== 'awarded';
                    })
                    ->modalHeading(fn (Bid $record): string => "Borang Penilaian Teknikal — {$record->blind_code}")
                    ->modalDescription('Penilaian dwi-buta dijalankan tanpa nama syarikat atau tawaran harga pembida.')
                    ->schema([
                        TextInput::make('score_criteria_1')
                            ->label('Kriteria 1: Pematuhan Spesifikasi Teknikal & Kualiti (Maks 40)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(40)
                            ->default(fn (Bid $record) => $record->evaluations->where('evaluation_type', 'technical')->first()?->score_criteria_1 ?? 30),
                        TextInput::make('score_criteria_2')
                            ->label('Kriteria 2: Keupayaan & Rekod Prestasi (Maks 30)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(30)
                            ->default(fn (Bid $record) => $record->evaluations->where('evaluation_type', 'technical')->first()?->score_criteria_2 ?? 20),
                        TextInput::make('score_criteria_3')
                            ->label('Kriteria 3: Metodologi & Jadual Pelaksanaan (Maks 30)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(30)
                            ->default(fn (Bid $record) => $record->evaluations->where('evaluation_type', 'technical')->first()?->score_criteria_3 ?? 20),
                        Textarea::make('comments')
                            ->label('Ulasan / Justifikasi Penilai')
                            ->rows(3)
                            ->placeholder('Sila masukkan ulasan atau kekuatan/kelemahan cadangan pembida...'),
                    ])
                    ->action(function (Bid $record, array $data) {
                        $evaluationService = app(EvaluationService::class);
                        $evaluation = $evaluationService->evaluateTechnical(
                            $record,
                            auth()->user(),
                            $data,
                            $data['comments'] ?? null
                        );

                        $statusText = $evaluation->is_passed ? 'LULUS' : 'GAGAL';

                        Notification::make()
                            ->title('Penilaian Teknikal Disimpan')
                            ->body("Skor {$evaluation->total_score}% bagi {$record->blind_code} ({$statusText}).")
                            ->color($evaluation->is_passed ? 'success' : 'warning')
                            ->send();
                    }),

                Action::make('evaluate_financial')
                    ->label('Nilai Kewangan')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->visible(function (Bid $record): bool {
                        /** @var Tender $tender */
                        $tender = $this->getOwnerRecord();

                        return $tender->isBoxOpened()
                            && $record->technical_score !== null
                            && $record->technical_score >= $tender->min_technical_score
                            && $tender->status !== 'awarded';
                    })
                    ->modalHeading(fn (Bid $record): string => "Penilaian Kewangan Berkomputer — {$record->blind_code}")
                    ->modalDescription(function (Bid $record): string {
                        $lowest = $record->tender->bids()
                            ->where('technical_score', '>=', $record->tender->min_technical_score)
                            ->whereNotNull('decrypted_bid_amount')
                            ->where('decrypted_bid_amount', '>', 0)
                            ->min('decrypted_bid_amount');

                        return sprintf(
                            'Tawaran Bidaan: RM %s | Tawaran Terendah Layak: RM %s. Skor kewangan dikira secara perkadaran (Lowest/Price * 100).',
                            number_format((float) $record->decrypted_bid_amount, 2),
                            number_format((float) $lowest, 2)
                        );
                    })
                    ->schema([
                        TextInput::make('price_display')
                            ->label('Tawaran Harga Bidaan (RM)')
                            ->disabled()
                            ->default(fn (Bid $record) => 'RM '.number_format((float) $record->decrypted_bid_amount, 2)),
                        Textarea::make('comments')
                            ->label('Ulasan Penilaian Kewangan & Komersial')
                            ->rows(2)
                            ->placeholder('Ulasan kewangan atau kewajaran kos bidaan...'),
                    ])
                    ->action(function (Bid $record, array $data) {
                        $evaluationService = app(EvaluationService::class);
                        $evaluation = $evaluationService->evaluateFinancial(
                            $record,
                            auth()->user(),
                            [],
                            $data['comments'] ?? null
                        );

                        $record->refresh();

                        Notification::make()
                            ->title('Penilaian Kewangan Disimpan')
                            ->body("Skor Kewangan: {$record->financial_score}% | Skor Komposit: {$record->total_score}% bagi {$record->blind_code}.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
