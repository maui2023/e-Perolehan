<?php

namespace App\Filament\Resources\Tenders\RelationManagers;

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

class AdminBidsRelationManager extends RelationManager
{
    protected static string $relationship = 'bids';

    protected static ?string $title = 'Peti Bidaan & Keputusan Penilaian (Lembaga Perolehan)';

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
            ->modifyQueryUsing(fn ($query) => $query->with('vendor'))
            ->recordTitleAttribute('bid_reference')
            ->columns([
                TextColumn::make('bid_reference')
                    ->label('Kod Bidaan')
                    ->weight('bold')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('vendor.company_name')
                    ->label('Syarikat Pembekal')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (Bid $record): string => "SSM: {$record->vendor->registration_number}"),
                TextColumn::make('blind_code')
                    ->label('Kod Dwi-Buta')
                    ->placeholder('-'),
                TextColumn::make('price_display')
                    ->label('Tawaran Harga')
                    ->state(function (Bid $record): string {
                        /** @var Tender $tender */
                        $tender = $this->getOwnerRecord();
                        if ($tender->isBoxOpened() && filled($record->decrypted_bid_amount)) {
                            return 'RM '.number_format((float) $record->decrypted_bid_amount, 2);
                        }

                        return 'TERKUNCI (AES-256)';
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_starts_with($state, 'RM') ? 'success' : 'warning'),
                TextColumn::make('technical_score')
                    ->label('Teknikal')
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state}%" : '-')
                    ->sortable(),
                TextColumn::make('financial_score')
                    ->label('Kewangan')
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state}%" : '-')
                    ->sortable(),
                TextColumn::make('total_score')
                    ->label('Skor Komposit')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state}%" : '-')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'submitted', 'sealed' => 'Terkunci',
                        'opened' => 'Dibuka',
                        'passed_technical' => 'Lulus Teknikal',
                        'failed_technical' => 'Gagal Teknikal',
                        'awarded' => 'DIANUGERAHKAN',
                        'rejected' => 'Tidak Berjaya',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'submitted', 'sealed' => 'warning',
                        'opened', 'passed_technical' => 'info',
                        'failed_technical', 'rejected' => 'danger',
                        'awarded' => 'success',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Action::make('download_slip')
                    ->label('Slip PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (Bid $record): bool => filled($record->submission_slip_pdf_path))
                    ->url(fn (Bid $record): string => route('vendor.bids.slip', $record->id), shouldOpenInNewTab: true),

                Action::make('award_bid')
                    ->label('Anugerah Kontrak')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->visible(function (Bid $record): bool {
                        /** @var Tender $tender */
                        $tender = $this->getOwnerRecord();

                        return $tender->isBoxOpened()
                            && $tender->status !== 'awarded'
                            && $record->technical_score !== null
                            && $record->technical_score >= $tender->min_technical_score;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Penganugerahan Tender & Pelantikan Kontrak')
                    ->modalDescription('Tindakan ini akan mengesahkan keputusan Lembaga Perolehan, mengeluarkan Surat Setuju Terima (SST) rasmi, dan memulakan pengarkiban MinIO.')
                    ->schema([
                        TextInput::make('company_name_display')
                            ->label('Syarikat Pembida Terpilih')
                            ->disabled()
                            ->default(fn (Bid $record) => $record->vendor->company_name),
                        TextInput::make('contract_amount_display')
                            ->label('Nilai Kontrak Ditawarkan (RM)')
                            ->disabled()
                            ->default(fn (Bid $record) => 'RM '.number_format((float) $record->decrypted_bid_amount, 2)),
                        TextInput::make('total_score_display')
                            ->label('Skor Komposit Keseluruhan')
                            ->disabled()
                            ->default(fn (Bid $record) => "{$record->total_score}%"),
                        Textarea::make('award_notes')
                            ->label('Ulasan / Minit Mesyuarat Lembaga Perolehan')
                            ->required()
                            ->placeholder('Contoh: Diputuskan sebulat suara dalam Mesyuarat Lembaga Perolehan Bil. 4/2026 berdasarkan pematuhan teknikal terbaik dan kos paling munasabah...')
                            ->rows(3),
                    ])
                    ->action(function (Bid $record, array $data) {
                        /** @var Tender $tender */
                        $tender = $this->getOwnerRecord();

                        $evaluationService = app(EvaluationService::class);
                        $evaluationService->awardTender($tender, $record, $data['award_notes'], auth()->user());

                        Notification::make()
                            ->title('Tender Berjaya Dianugerahkan')
                            ->body("Tender {$tender->reference_no} telah diawardkan kepada {$record->vendor->company_name}. Surat Setuju Terima (SST) telah dijana secara rasmi.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
