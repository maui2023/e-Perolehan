<?php

namespace App\Filament\Resources\Vendors\Tables;

use App\Models\AuditTrail;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->columns([
                TextColumn::make('company_name')
                    ->label('Nama Syarikat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Vendor $record): string => "SSM: {$record->registration_number}"),
                TextColumn::make('user.name')
                    ->label('Wakil Sah')
                    ->searchable()
                    ->description(fn (Vendor $record): ?string => $record->user?->email),
                TextColumn::make('mof_registration_number')
                    ->label('MOF')
                    ->placeholder('-')
                    ->description(fn (Vendor $record): ?string => $record->mof_codes),
                TextColumn::make('cidb_registration_number')
                    ->label('CIDB')
                    ->placeholder('-')
                    ->description(fn (Vendor $record): ?string => $record->cidb_grade ? "Gred {$record->cidb_grade}" : null),
                TextColumn::make('bank_details')
                    ->label('Maklumat Bank')
                    ->state(function (Vendor $record): string {
                        if ($record->bank_name && $record->bank_account_number) {
                            return "{$record->bank_name}: {$record->bank_account_number}";
                        }

                        return 'Belum diisi';
                    })
                    ->color(fn ($state) => $state === 'Belum diisi' ? 'gray' : 'primary'),
                IconColumn::make('bumiputera_status')
                    ->label('Bumiputera')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('compliance_status')
                    ->label('Status Pematuhan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'verified' => 'DISAHKAN / LULUS',
                        'pending' => 'Menunggu Semakan',
                        'rejected' => 'DITOLAK',
                        'suspended' => 'Digantung',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        'suspended' => 'gray',
                        default => 'gray',
                    }),
                IconColumn::make('anti_bribery_agreed_at')
                    ->label('Ikrar SPRM')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('compliance_status')
                    ->label('Status Pematuhan')
                    ->options([
                        'pending' => 'Menunggu Semakan',
                        'verified' => 'Disahkan / Lulus',
                        'rejected' => 'Ditolak',
                        'suspended' => 'Digantung',
                    ]),
            ])
            ->recordActions([
                Action::make('verify_compliance')
                    ->label('Luluskan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Vendor $record): bool => $record->compliance_status !== 'verified')
                    ->requiresConfirmation()
                    ->modalHeading('Luluskan Pematuhan Syarikat Pembekal')
                    ->modalDescription(fn (Vendor $record): string => "Adakah anda mengesahkan profil pematuhan {$record->company_name} (SSM: {$record->registration_number})?")
                    ->action(function (Vendor $record) {
                        $record->update([
                            'compliance_status' => 'verified',
                            'compliance_verified_at' => now(),
                            'verified_by_user_id' => auth()->id(),
                        ]);

                        AuditTrail::log(
                            eventType: 'VENDOR_COMPLIANCE_VERIFIED',
                            description: "Pematuhan pembekal {$record->company_name} diluluskan oleh ".auth()->user()->name,
                            auditable: $record,
                            newValues: ['compliance_status' => 'verified'],
                            userId: auth()->id()
                        );

                        Notification::make()
                            ->title('Pematuhan Disahkan')
                            ->body("Syarikat {$record->company_name} kini layak menyertai bidaan.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject_compliance')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Vendor $record): bool => $record->compliance_status !== 'rejected')
                    ->schema([
                        Textarea::make('compliance_notes')
                            ->label('Alasan Penolakan Pematuhan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Sila nyatakan dokumen yang tidak lengkap atau sebab penolakan...'),
                    ])
                    ->action(function (Vendor $record, array $data) {
                        $record->update([
                            'compliance_status' => 'rejected',
                            'compliance_notes' => $data['compliance_notes'],
                            'verified_by_user_id' => auth()->id(),
                        ]);

                        AuditTrail::log(
                            eventType: 'VENDOR_COMPLIANCE_REJECTED',
                            description: "Pematuhan pembekal {$record->company_name} ditolak: {$data['compliance_notes']}",
                            auditable: $record,
                            newValues: ['compliance_status' => 'rejected', 'notes' => $data['compliance_notes']],
                            userId: auth()->id()
                        );

                        Notification::make()
                            ->title('Pematuhan Ditolak')
                            ->body("Syarikat {$record->company_name} telah ditandakan sebagai Ditolak.")
                            ->warning()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Semak / Kemaskini'),
            ]);
    }
}
