<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Slip Rasmi Pembukaan Peti Tender - {{ $tender->reference_no }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; margin: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 15px; }
        .org-name { font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .org-sub { font-size: 10px; color: #64748b; }
        .slip-title { font-size: 14px; font-weight: bold; text-align: right; color: #059669; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px 8px; text-align: left; }
        .table-bordered th { background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; font-size: 10px; }
        .table-bordered td { border: 1px solid #e2e8f0; font-size: 10px; }
        .witness-card { background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; margin-bottom: 8px; border-radius: 4px; }
        .footer { margin-top: 25px; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 9px; color: #94a3b8; text-align: center; }
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
                    <div class="slip-title">SLIP PEMBUKAAN PETI TENDER</div>
                    <div style="font-size: 11px;">Kod Tender: <strong>{{ $tender->reference_no }}</strong></div>
                    <div style="font-size: 10px; color: #64748b;">Masa Dibuka: {{ $tender->dual_witness_opened_at ? $tender->dual_witness_opened_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-bordered">
        <tr>
            <th style="width: 25%;">TAJUK PEROLEHAN</th>
            <td style="width: 75%;"><strong>{{ $tender->title }}</strong></td>
        </tr>
        <tr>
            <th>KATEGORI & KAEDAH</th>
            <td>{{ $tender->category }} | {{ strtoupper($tender->procurement_type) }}</td>
        </tr>
        <tr>
            <th>TARIKH & MASA TUTUP</th>
            <td>{{ $tender->closing_at ? $tender->closing_at->format('d/m/Y H:i:s') : '-' }}</td>
        </tr>
        <tr>
            <th>JUMLAH BIDAAN DITERIMA</th>
            <td><strong>{{ $bids->count() }} Pembida</strong></td>
        </tr>
    </table>

    <div style="margin-top: 15px;">
        <strong style="color: #0f172a;">PENGESAHAN DUA SAKSI DIGITAL (DUAL-WITNESS PROTOCOL):</strong>
        <div style="margin-top: 8px;">
            @forelse($witnesses as $index => $w)
            <div class="witness-card">
                <strong>Saksi {{ $index + 1 }}: {{ $w->user->name }}</strong> (Peranan: {{ ucwords(str_replace('_', ' ', $w->witness_role)) }})<br>
                Emel: {{ $w->user->email }} | Alamat IP: {{ $w->ip_address }} | Tarikh Pengesahan: {{ $w->confirmed_at->format('d/m/Y H:i:s') }}<br>
                Catatan: {{ $w->witness_remarks ?: 'Tiada catatan tambahan' }}<br>
                <span style="font-family: monospace; font-size: 8px; color: #166534;">Tandatangan Digital SHA-256: {{ $w->digital_signature_hash }}</span>
            </div>
            @empty
            <p>Tiada rekod saksi.</p>
            @endforelse
        </div>
    </div>

    <div style="margin-top: 15px;">
        <strong style="color: #0f172a;">JADUAL PEMBUKAAN PETI TENDER (JADUAL HARGA & TEMPOH):</strong>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th style="width: 5%;">BIL</th>
                    <th style="width: 20%;">KOD RAWAK / RUJUKAN</th>
                    <th style="width: 35%;">NAMA PEMBIDA</th>
                    <th style="width: 15%; text-align: center;">TEMPOH</th>
                    <th style="width: 25%; text-align: right;">HARGA TAWARAN ({{ $setting->currency }})</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bids as $index => $bid)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $bid->blind_code ?: $bid->bid_reference }}</strong></td>
                    <td>{{ $bid->vendor->company_name }} ({{ $bid->vendor->registration_number }})</td>
                    <td style="text-align: center;">{{ $bid->completion_duration_months ?? '-' }} Bulan</td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ $bid->decrypted_bid_amount ? number_format($bid->decrypted_bid_amount, 2) : 'DIENKRIPSI' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tiada bidaan diterima.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        {{ $setting->letterhead_footer_text ?? 'Dokumen Rasmi Dijana oleh Sistem e-Perolehan Berintegriti Tinggi.' }}
    </div>
</body>
</html>
