<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_name',
        'org_short_name',
        'org_reg_no',
        'org_email',
        'org_phone',
        'org_address',
        'org_logo_path',
        'currency',
        'tax_percentage',
        'anti_bribery_policy_text',
        'procurement_terms_text',
        'letterhead_footer_text',
        'extra_config',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'extra_config' => 'array',
    ];

    public function getOrgAcronymAttribute(): string
    {
        if (! empty($this->extra_config['acronym'])) {
            return (string) $this->extra_config['acronym'];
        }

        if ($this->org_name === 'Lembaga Perolehan & Kontrak Universal') {
            return 'LPKU';
        }

        $words = preg_split('/[\s&]+/', (string) $this->org_name, -1, PREG_SPLIT_NO_EMPTY);
        $acronym = '';
        foreach ($words as $word) {
            $firstChar = mb_substr($word, 0, 1);
            if (ctype_alpha($firstChar)) {
                $acronym .= mb_strtoupper($firstChar);
            }
        }

        return $acronym ?: 'LPKU';
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'org_name' => config('app.name', 'Universal Procurement Portal'),
            'org_short_name' => 'E-Perolehan',
            'org_reg_no' => 'ORG-2026-REG',
            'org_email' => 'perolehan@organisasi.my',
            'org_phone' => '+60 3-8888 0000',
            'org_address' => "Kompleks Pentadbiran & Perolehan\nKuala Lumpur, Malaysia",
            'currency' => 'MYR',
            'tax_percentage' => 0.00,
            'anti_bribery_policy_text' => 'Organisasi ini berpegang teguh kepada polisi sifar toleransi terhadap sebarang bentuk rasuah, suapan, atau salah guna kuasa selaras dengan Seksyen 17A Akta SPRM 2009.',
            'procurement_terms_text' => 'Semua pembida tertakluk kepada syarat-syarat am dan khusus perolehan yang ditetapkan oleh jawatankuasa perolehan organisasi.',
            'letterhead_footer_text' => 'Dokumen ini dijana secara digital oleh Sistem e-Perolehan Berintegriti Tinggi.',
        ]);
    }
}
