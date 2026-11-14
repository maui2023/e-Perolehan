<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Kenyataan Notis Iklan Tender - {{ $tender->reference_no }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; margin: 25px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .org-name { font-size: 17px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .org-sub { font-size: 10px; color: #64748b; }
        .doc-title { font-size: 15px; font-weight: bold; text-align: right; color: #1e3a8a; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-primary { background-color: #dbeafe; color: #1e40af; }
        .badge-category { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 6px 8px; text-align: left; }
        .table-details { border: 1px solid #cbd5e1; margin-top: 15px; }
        .table-details th { background-color: #f8fafc; border: 1px solid #cbd5e1; font-weight: bold; width: 28%; font-size: 11px; color: #334155; }
        .table-details td { border: 1px solid #cbd5e1; font-size: 11px; }
        .section-title { font-size: 13px; font-weight: bold; color: #0f172a; margin-top: 18px; margin-bottom: 6px; border-left: 3px solid #1e3a8a; padding-left: 8px; }
        .box-info { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-top: 10px; }
        .footer { clear: both; margin-top: 35px; padding-top: 12px; border-top: 1px dashed #cbd5e1; font-size: 9px; color: #94a3b8; text-align: center; }
        .security-badge { margin-top: 20px; padding: 8px 12px; background-color: #f1f5f9; border-left: 3px solid #1e3a8a; font-size: 9px; color: #475569; }
    </style>
</head>
<body>
    <div class="header">
        <table style="margin-top: 0;">
            <tr>
                <td style="width: 65%; vertical-align: top; padding: 0;">
                    <div class="org-name">{{ $setting->org_name }}</div>
                    <div class="org-sub">{{ $setting->org_address }}</div>
                    <div class="org-sub">No. Pendaftaran: {{ $setting->org_reg_no ?? '-' }} | Emel: {{ $setting->org_email ?? '-' }}</div>
                </td>
                <td style="width: 35%; vertical-align: top; text-align: right; padding: 0;">
                    <div class="doc-title">NOTIS IKLAN TENDER</div>
                    <div style="font-size: 11px; color: #64748b;">No. Rujukan: <strong>{{ $tender->reference_no }}</strong></div>
                    <div style="margin-top: 5px;">
                        <span class="badge badge-primary">{{ strtoupper($tender->procurement_type ?? 'TENDER TERBUKA') }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f8fafc; border-left: 4px solid #1e3a8a; padding: 10px 14px; margin-bottom: 15px;">
        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: bold;">Tajuk Perolehan:</div>
        <div style="font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 3px;">{{ $tender->title }}</div>
    </div>

    <div class="section-title">1. MAKLUMAT & SPESIFIKASI AM TENDER</div>
    <table class="table-details">
        <tr>
            <th>Nombor Rujukan Tender</th>
            <td><strong>{{ $tender->reference_no }}</strong></td>
        </tr>
        <tr>
            <th>Kategori Perolehan</th>
            <td><strong>{{ strtoupper($tender->category) }}</strong></td>
        </tr>
        <tr>
            <th>Jenis Perolehan</th>
            <td>{{ ucfirst($tender->procurement_type ?? 'Terbuka') }}</td>
        </tr>
        <tr>
            <th>Tarikh Mula Iklan</th>
            <td>{{ $tender->published_at ? $tender->published_at->format('d/m/Y (h:i A)') : 'Segera' }}</td>
        </tr>
        <tr>
            <th>Tarikh & Waktu Tutup Bidaan</th>
            <td><strong style="color: #dc2626;">{{ $tender->closing_at ? $tender->closing_at->format('d/m/Y (h:i A)') : 'Tertakluk Pengumuman' }}</strong></td>
        </tr>
        <tr>
            <th>Harga Dokumen Tender</th>
            <td>
                @if($tender->document_price > 0)
                    <strong>RM {{ number_format($tender->document_price, 2) }}</strong> (Pembelian dalam talian melalui AffanPay)
                @else
                    <strong style="color: #16a34a;">PERCUMA</strong>
                @endif
            </td>
        </tr>
        @if($tender->tender_deposit_amount > 0)
        <tr>
            <th>Deposit Bidaan (Tender Bond)</th>
            <td>RM {{ number_format($tender->tender_deposit_amount, 2) }}</td>
        </tr>
        @endif
    </table>

    <div class="section-title">2. SYARAT-SYARAT KELAYAKAN PEMBIDA</div>
    <div class="box-info">
        {!! nl2br(e($tender->eligibility_criteria ?? 'Pembida mestilah berdaftar secara sah dengan SSM dan mematuhi kod bidang berkaitan.')) !!}
    </div>

    <div class="section-title">3. SKOP KERJA & KETERANGAN PROJEK</div>
    <div class="box-info">
        {!! nl2br(e($tender->description ?? $tender->scope_of_work ?? 'Rujuk dokumen spesifikasi teknikal penuh dalam portal vendor.')) !!}
    </div>

    <div class="section-title">4. KAEDAH PEMBELIAN DOKUMEN & PENYERAHAN BIDAAN</div>
    <div class="box-info">
        <ol style="margin: 0; padding-left: 18px;">
            <li>Syarikat yang berminat dan berkelayakan dikehendaki mendaftar di <strong>Portal Pembekal</strong> di pautan <code>{{ url('/vendor') }}</code>.</li>
            <li>Pembelian dokumen tender dibuat secara dalam talian menggunakan gerbang pembayaran <strong>AffanPay</strong>. Resit rasmi digital dikeluarkan secara serta-merta.</li>
            <li>Semua penyerahan bidaan dilaksanakan secara <strong>Bidaan Bertutup (Sealed-Bidding)</strong>. Cadangan Teknikal dan Cadangan Kewangan dimuat naik secara berasingan.</li>
            <li>Peti tender dikunci secara digital dan hanya dibuka selepas tarikh tutup dengan kehadiran sekurang-kurangnya dua (2) pegawai saksi rasmi.</li>
        </ol>
    </div>

    <div class="security-badge">
        <strong>INTEGRITI & ANTI-RASUAH:</strong><br>
        {{ $setting->anti_bribery_policy_text }}<br>
        <span style="font-size: 8px; color: #94a3b8;">Pengesahan Digital: {{ $verifyUrl }}</span>
    </div>

    <div class="footer">
        {{ $setting->letterhead_footer_text }}<br>
        Dicetak pada {{ now()->format('d/m/Y H:i:s') }} | ID Notis: {{ md5($tender->reference_no) }}
    </div>
</body>
</html>
