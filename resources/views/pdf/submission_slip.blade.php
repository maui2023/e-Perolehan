<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Akuan Penyerahan Bidaan Bertutup - {{ $bid->bid_reference }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; margin: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 15px; }
        .org-name { font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .org-sub { font-size: 10px; color: #64748b; }
        .slip-title { font-size: 14px; font-weight: bold; text-align: right; color: #0284c7; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px 8px; text-align: left; }
        .table-bordered th { background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; font-size: 10px; }
        .table-bordered td { border: 1px solid #e2e8f0; font-size: 10px; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-sealed { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .hash-box { background-color: #0f172a; color: #38bdf8; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 9px; word-break: break-all; margin-top: 15px; }
        .declaration-box { margin-top: 15px; padding: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 10px; color: #334155; }
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
                    <div class="slip-title">SLIP AKUAN PENYERAHAN BIDAAN</div>
                    <div style="font-size: 11px;">Rujukan: <strong>{{ $bid->bid_reference }}</strong></div>
                    <div style="font-size: 10px; color: #64748b;">Cap Masa: {{ $bid->submitted_at ? $bid->submitted_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</div>
                    <div style="margin-top: 4px;"><span class="badge badge-sealed">BIDAAN BERTUTUP & TERKUNCI (SEALED)</span></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-bordered">
        <tr>
            <th style="width: 25%;">KOD / TAJUK TENDER</th>
            <td style="width: 75%;"><strong>{{ $tender->reference_no }}</strong> - {{ $tender->title }}</td>
        </tr>
        <tr>
            <th>KATEGORI & JENIS</th>
            <td>{{ $tender->category }} | {{ strtoupper($tender->procurement_type) }}</td>
        </tr>
        <tr>
            <th>NAMA PEMBIDA / SYARIKAT</th>
            <td><strong>{{ $vendor->company_name }}</strong> (No. SSM: {{ $vendor->registration_number }})</td>
        </tr>
        <tr>
            <th>TEMPOH CADANGAN KERJA</th>
            <td>{{ $bid->completion_duration_months ?? '-' }} Bulan</td>
        </tr>
        <tr>
            <th>STATUS KERAHSIAAN HARGA</th>
            <td><strong style="color: #b45309;">DIENKRIPSI (AES-256)</strong> - Hanya boleh dinyahsulit selepas tarikh tutup dengan pengesahan 2 pegawai saksi digital.</td>
        </tr>
    </table>

    <div style="margin-top: 15px;">
        <strong style="font-size: 11px; color: #0f172a;">SENARAI DOKUMEN BIDAAN DIMUAT NAIK:</strong>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th style="width: 5%;">BIL</th>
                    <th style="width: 35%;">JENIS DOKUMEN</th>
                    <th style="width: 35%;">NAMA FAIL</th>
                    <th style="width: 25%;">CHECKSUM SHA-256</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bid->documents as $index => $doc)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $doc->type)) }}</td>
                    <td>{{ $doc->file_name }}</td>
                    <td style="font-family: monospace; font-size: 8px;">{{ substr($doc->sha256_checksum ?? 'N/A', 0, 20) }}...</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Tiada rekod dokumen lampiran.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="hash-box">
        <strong>CAP JARI INTEGRITI DIGITAL KESELURUHAN (SHA-256 PACKAGE CHECKSUM):</strong><br>
        {{ $bid->sha256_package_hash ?? 'N/A' }}
    </div>

    <div class="declaration-box">
        <strong>PERAKUAN PEMATUHAN INTEGRITI & ANTI-RASUAH:</strong><br>
        Pembida dengan ini memperakui bahawa segala maklumat dan dokumen yang dikemukakan adalah benar, sah, dan bebas daripada sebarang unsur penipuan, pakatan harga (cartel/collusion), atau percubaan merasuah pegawai penilai. Kegagalan mematuhi etika ini boleh mengakibatkan bidaan dibatalkan serta-merta dan disenaraihitamkan.
    </div>

    <table style="margin-top: 25px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div style="border-top: 1px solid #64748b; width: 80%; padding-top: 5px;">
                    <strong>Pengesahan Digital Pembekal:</strong><br>
                    {{ $vendor->company_name }}<br>
                    Cap Masa: {{ $bid->submitted_at ? $bid->submitted_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <div style="border-top: 1px solid #64748b; width: 80%; margin-left: auto; padding-top: 5px;">
                    <strong>Sistem e-Perolehan:</strong><br>
                    Peti Maya: TERKUNCI & DIAMANKAN<br>
                    Semakan: {{ $verifyUrl }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $setting->letterhead_footer_text ?? 'Dokumen Rasmi Dijana oleh Sistem e-Perolehan Berintegriti Tinggi.' }}
    </div>
</body>
</html>
