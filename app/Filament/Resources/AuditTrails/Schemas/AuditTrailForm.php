<?php

namespace App\Filament\Resources\AuditTrails\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditTrailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event_type')
                    ->label('Jenis Peristiwa')
                    ->disabled(),
                TextInput::make('user.name')
                    ->label('Pegawai / Pengguna')
                    ->disabled(),
                TextInput::make('ip_address')
                    ->label('Alamat IP')
                    ->disabled(),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->columnSpanFull()
                    ->disabled(),
                Textarea::make('new_values')
                    ->label('Data / Payload Transaksi')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->columnSpanFull()
                    ->disabled(),
                TextInput::make('prev_hash')
                    ->label('Hash Blok Terdahulu (SHA-256)')
                    ->columnSpanFull()
                    ->disabled(),
                TextInput::make('hash')
                    ->label('Hash Blok Semasa (SHA-256)')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }
}
