<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penilaian Tender & Syor Lembaga - {{ $tender->reference_no }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; }
        .org-name { font-size: 15px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .org-sub { font-size: 9px; color: #64748b; }
        .slip-title { font-size: 13px; font-weight: bold; text-align: right; color: #4338ca; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 5px 7px; text-align: left; }
        .table-bordered th { background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; font-size: 9px; }
        .table-bordered td { border: 1px solid #e2e8f0; font-size: 9px; }
        .recommend-box { background-color: #eef2ff; border-left: 4px solid #4f46e5; padding: 10px; margin-top: 15px; }
        .footer { margin-top: 20px; padding-top: 8px; border-top: 1px dashed #cbd5e1; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%; vertical-align: top; padding: 0;">
                    <div class="org-name">{{ $setting->org_name }}</div>
                    <div class="org-sub">{{ $setting->org_address }}</div>
                    <div class="org-sub">No. Pendaftaran: {{ $setting->org_reg_no ?? '-' }}</div>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right; padding: 0;">
                    <div class="slip-title">LAPORAN PENILAIAN TENDER</div>
                    <div style="font-size: 10px;">Kod Tender: <strong>{{ $tender->reference_no }}</strong></div>
                    <div style="font-size: 9px; color: #64748b;">Tarikh Laporan: {{ now()->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-bordered">
        <tr>
            <th style="width: 25%;">TAJUK TENDER</th>
            <td style="width: 75%;"><strong>{{ $tender->title }}</strong></td>
        </tr>
        <tr>
            <th>AMBANG LULUS TEKNIKAL</th>
            <td><strong>{{ $tender->min_technical_score }}%</strong> (Pembida yang gagal mencapai markah ini tidak layak dinilai aspek kewangan)</td>
        </tr>
        <tr>
            <th>ANGGARAN PERUNTUKAN</th>
            <td>{{ $tender->estimated_budget ? $setting->currency . ' ' . number_format($tender->estimated_budget, 2) : 'Dirasiakan' }}</td>
        </tr>
    </table>

    <div style="margin-top: 12px;">
        <strong style="color: #0f172a;">MATRIKS PENILAIAN BERGANDA (DOUBLE-BLIND EVALUATION MATRIX):</strong>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th style="width: 4%;">KED.</th>
                    <th style="width: 12%;">KOD BLIND</th>
                    <th style="width: 26%;">NAMA SYARIKAT</th>
                    <th style="width: 18%; text-align: right;">HARGA TAWARAN ({{ $setting->currency }})</th>
                    <th style="width: 12%; text-align: center;">SKOR TEKNIKAL</th>
                    <th style="width: 12%; text-align: center;">SKOR KEWANGAN</th>
                    <th style="width: 16%; text-align: center;">JUMLAH MARKAH</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bids as $index => $bid)
                <tr style="{{ $index === 0 && $bid->total_score ? 'background-color: #f0fdf4;' : '' }}">
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td><strong>{{ $bid->blind_code ?: $bid->bid_reference }}</strong></td>
                    <td>{{ $bid->vendor->company_name }}</td>
                    <td style="text-align: right;">
                        {{ $bid->decrypted_bid_amount ? number_format($bid->decrypted_bid_amount, 2) : '-' }}
                    </td>
                    <td style="text-align: center;">
                        {{ $bid->technical_score !== null ? number_format($bid->technical_score, 1) . '%' : '-' }}
                        @if($bid->technical_score !== null && $bid->technical_score < $tender->min_technical_score)
                            <br><span style="color: #ef4444; font-size: 8px;">GAGAL AMBANG</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        {{ $bid->financial_score !== null ? number_format($bid->financial_score, 1) . '%' : '-' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $bid->total_score !== null ? number_format($bid->total_score, 1) . '%' : 'BELUM LENGKAP' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tiada rekod penilaian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bids->isNotEmpty() && $bids->first()->total_score)
    <div class="recommend-box">
        <strong>SYOR JAWATANKUASA PENILAIAN PEROLEHAN:</strong><br>
        Berdasarkan matriks penilaian teknikal dan kewangan di atas, Jawatankuasa Penilaian dengan sebulat suara memperakukan agar Tender <strong>{{ $tender->reference_no }}</strong> dianugerahkan kepada:
        <br><br>
        <strong>Syarikat: {{ $bids->first()->vendor->company_name }} (No. SSM: {{ $bids->first()->vendor->registration_number }})</strong><br>
        Harga Tawaran: <strong>{{ $setting->currency }} {{ number_format($bids->first()->decrypted_bid_amount, 2) }}</strong><br>
        Jumlah Markah Keseluruhan: <strong>{{ number_format($bids->first()->total_score, 1) }}%</strong>
    </div>
    @endif

    <table style="margin-top: 30px;">
        <tr>
            <td style="width: 33%; text-align: center;">
                <div style="border-top: 1px solid #94a3b8; padding-top: 5px;">
                    <strong>Ketua Penilai Teknikal</strong><br>
                    Jawatankuasa Penilaian Teknikal
                </div>
            </td>
            <td style="width: 33%; text-align: center;">
                <div style="border-top: 1px solid #94a3b8; padding-top: 5px;">
                    <strong>Ketua Penilai Kewangan</strong><br>
                    Jawatankuasa Penilaian Komersial
                </div>
            </td>
            <td style="width: 33%; text-align: center;">
                <div style="border-top: 1px solid #94a3b8; padding-top: 5px;">
                    <strong>Pengerusi Lembaga Perolehan</strong><br>
                    Pengesahan Muktamad
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $setting->letterhead_footer_text ?? 'Dokumen Rasmi Dijana oleh Sistem e-Perolehan Berintegriti Tinggi.' }}
    </div>
</body>
</html>
