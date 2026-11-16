<?php

namespace App\Filament\Vendor\Pages;

use App\Models\AuditTrail;
use BackedEnum;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class VendorProfile extends BaseEditProfile
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Profil Syarikat & Sijil';

    public static function getLabel(): string
    {
        return 'Profil Syarikat & Pematuhan';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Profil Syarikat & Pematuhan Vendor';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Profil Syarikat & Pengurusan Pematuhan';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Kemas kini maklumat pendaftaran SSM, sijil MOF/CIDB, maklumat akaun wakil, dan perakuan integriti vendor.';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Akaun & Wakil Sah')
                    ->description('Maklumat peribadi dan keselamatan akaun wakil syarikat.')
                    ->icon('heroicon-o-user')
                    ->components([
                        $this->getNameFormComponent()
                            ->label('Nama Penuh Wakil Sah')
                            ->required(),
                        $this->getEmailFormComponent()
                            ->label('Emel Log Masuk')
                            ->required(),
                        TextInput::make('phone')
                            ->label('No. Telefon Bimbit')
                            ->tel()
                            ->placeholder('012-3456789'),
                        $this->getPasswordFormComponent()
                            ->label('Kata Laluan Baharu (Pilihan)'),
                        $this->getPasswordConfirmationFormComponent()
                            ->label('Sahkan Kata Laluan Baharu'),
                    ])
                    ->columns(2),

                Section::make('Maklumat Pendaftaran Syarikat / Perniagaan')
                    ->description('Profil perniagaan dan nombor pendaftaran rasmi syarikat.')
                    ->icon('heroicon-o-building-office')
                    ->components([
                        TextInput::make('company_name')
                            ->label('Nama Syarikat / Perniagaan')
                            ->required(),
                        TextInput::make('registration_number')
                            ->label('No. Pendaftaran SSM / RoC / RoB')
                            ->required(),
                        TextInput::make('tax_identification_number')
                            ->label('No. Cukai Pendapatan (TIN / LHDN)')
                            ->placeholder('Contoh: C 1234567890'),
                        TextInput::make('company_email')
                            ->label('Emel Rasmi Syarikat')
                            ->email(),
                        TextInput::make('company_phone')
                            ->label('No. Telefon Rasmi Syarikat')
                            ->tel(),
                        Textarea::make('address')
                            ->label('Alamat Berdaftar Syarikat')
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label('Bandar'),
                        TextInput::make('state')
                            ->label('Negeri'),
                        TextInput::make('postcode')
                            ->label('Poskod'),
                    ])
                    ->columns(2),

                Section::make('Pengiktirafan Kementerian & Sijil Pematuhan')
                    ->description('Nombor pendaftaran MOF / CIDB dan status Bumiputera.')
                    ->icon('heroicon-o-shield-check')
                    ->components([
                        TextInput::make('mof_registration_number')
                            ->label('No. Pendaftaran MOF (Kementerian Kewangan)')
                            ->placeholder('Contoh: K.KEW/357-XXXXX')
                            ->helperText('Diperlukan untuk pembekalan barangan & perkhidmatan am.'),
                        TextInput::make('mof_codes')
                            ->label('Kod Bidang MOF Berdaftar')
                            ->placeholder('Contoh: 210101, 210102, 220101')
                            ->helperText('Senaraikan kod bidang yang diluluskan MOF.'),
                        TextInput::make('cidb_registration_number')
                            ->label('No. Pendaftaran CIDB (Lembaga Pembangunan Industri Pembinaan)')
                            ->placeholder('Contoh: 012023XXXXXXXX')
                            ->helperText('Diperlukan untuk penyertaan tender kerja/pembinaan.'),
                        TextInput::make('cidb_grade')
                            ->label('Gred Pendaftaran CIDB')
                            ->placeholder('Contoh: G1 / G2 / G3 / G4 / G5 / G6 / G7')
                            ->helperText('Gred keupayaan kewangan & had tender CIDB.'),
                        Toggle::make('bumiputera_status')
                            ->label('Status Syarikat Bumiputera Diiktiraf')
                            ->helperText('Tandakan sekiranya syarikat memegang sijil taraf Bumiputera sah.'),
                    ])
                    ->columns(2),

                Section::make('Maklumat Akaun Bank Syarikat')
                    ->description('Maklumat akaun bank rasmi syarikat bagi tujuan transaksi perolehan.')
                    ->icon('heroicon-o-credit-card')
                    ->components([
                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->placeholder('Contoh: Maybank / CIMB / Bank Islam / RHB'),
                        TextInput::make('bank_account_number')
                            ->label('Nombor Akaun Bank')
                            ->placeholder('Contoh: 5642XXXXXXXX'),
                        TextInput::make('bank_account_holder')
                            ->label('Nama Pemegang Akaun (Syarikat)')
                            ->placeholder('Nama rasmi seperti dalam penyata bank')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Muat Naik Dokumen Bukti & Sijil (PDF)')
                    ->description('Dokumen rasmi bagi menyokong pengesahan pematuhan oleh Urus Setia.')
                    ->icon('heroicon-o-document-arrow-up')
                    ->components([
                        FileUpload::make('ssm_cert_path')
                            ->label('Salinan Sijil Pendaftaran SSM')
                            ->disk('private')
                            ->directory('vendor-certificates')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240),
                        FileUpload::make('mof_cert_path')
                            ->label('Salinan Sijil Kementerian Kewangan (MOF)')
                            ->disk('private')
                            ->directory('vendor-certificates')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240),
                        FileUpload::make('cidb_cert_path')
                            ->label('Salinan Perakuan CIDB')
                            ->disk('private')
                            ->directory('vendor-certificates')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240),
                        FileUpload::make('bank_statement_path')
                            ->label('Salinan Penyata Bank Terkini (3 Bulan)')
                            ->disk('private')
                            ->directory('vendor-certificates')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240),
                    ])
                    ->columns(2),

                Section::make('Perakuan Integriti & Anti-Rasuah (SPRM)')
                    ->description('Ikrar integriti tanpa kompromi menurut peruntukan Seksyen 17A Akta SPRM 2009.')
                    ->icon('heroicon-o-check-badge')
                    ->components([
                        Toggle::make('agree_anti_bribery')
                            ->label('Syarikat kami berikrar mematuhi sepenuhnya polisi pencegahan rasuah, tidak menawarkan sebarang suapan, dan bersedia dibatalkan penyertaan jika melanggar syarat integriti ini.')
                            ->required(),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getUser();
        $vendor = $user->vendor;

        $data['phone'] = $user->phone;

        if ($vendor) {
            $data = array_merge($data, [
                'company_name' => $vendor->company_name,
                'registration_number' => $vendor->registration_number,
                'tax_identification_number' => $vendor->tax_identification_number,
                'company_email' => $vendor->company_email,
                'company_phone' => $vendor->company_phone,
                'address' => $vendor->address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'postcode' => $vendor->postcode,
                'mof_registration_number' => $vendor->mof_registration_number,
                'mof_codes' => $vendor->mof_codes,
                'cidb_registration_number' => $vendor->cidb_registration_number,
                'cidb_grade' => $vendor->cidb_grade,
                'bank_name' => $vendor->bank_name,
                'bank_account_number' => $vendor->bank_account_number,
                'bank_account_holder' => $vendor->bank_account_holder,
                'bumiputera_status' => (bool) $vendor->bumiputera_status,
                'ssm_cert_path' => $vendor->ssm_cert_path,
                'mof_cert_path' => $vendor->mof_cert_path,
                'cidb_cert_path' => $vendor->cidb_cert_path,
                'bank_statement_path' => $vendor->bank_statement_path,
                'agree_anti_bribery' => (bool) $vendor->anti_bribery_agreed_at,
            ]);
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $vendorFields = [
            'company_name',
            'registration_number',
            'tax_identification_number',
            'company_email',
            'company_phone',
            'address',
            'city',
            'state',
            'postcode',
            'mof_registration_number',
            'mof_codes',
            'cidb_registration_number',
            'cidb_grade',
            'bank_name',
            'bank_account_number',
            'bank_account_holder',
            'bumiputera_status',
            'ssm_cert_path',
            'mof_cert_path',
            'cidb_cert_path',
            'bank_statement_path',
            'agree_anti_bribery',
        ];

        $vendorData = Arr::only($data, $vendorFields);
        $userData = Arr::except($data, $vendorFields);

        if (! empty($vendorData['agree_anti_bribery']) && ! $record->vendor?->anti_bribery_agreed_at) {
            $vendorData['anti_bribery_agreed_at'] = now();
        }
        unset($vendorData['agree_anti_bribery']);

        if ($record->vendor) {
            $record->vendor->update($vendorData);
        } else {
            $record->vendor()->create(array_merge($vendorData, [
                'compliance_status' => 'pending',
            ]));
        }

        AuditTrail::log(
            'vendor_profile_updated',
            "Kemas kini profil vendor: {$vendorData['company_name']}",
            $record->vendor,
            null,
            $vendorData,
            $record->id
        );

        return parent::handleRecordUpdate($record, $userData);
    }
}
