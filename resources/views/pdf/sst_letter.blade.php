<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Surat Setuju Terima (SST) - {{ $tender->reference_no }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.6; margin: 25px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .org-name { font-size: 17px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .org-sub { font-size: 10px; color: #64748b; }
        .doc-title { font-size: 14px; font-weight: bold; text-align: center; text-decoration: underline; margin-bottom: 20px; color: #0f172a; }
        .recipient-box { margin-bottom: 20px; }
        .content { text-align: justify; }
        .highlight-box { background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 12px; margin: 15px 0; border-radius: 4px; }
        .signature-section { margin-top: 35px; }
        .footer { margin-top: 35px; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 9px; color: #94a3b8; text-align: center; }
        .stamp-box { border: 2px solid #059669; color: #059669; padding: 8px 12px; display: inline-block; font-weight: bold; border-radius: 4px; text-transform: uppercase; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="org-name">{{ $setting->org_name }}</div>
        <div class="org-sub">{{ $setting->org_address }}</div>
        <div class="org-sub">No. Pendaftaran: {{ $setting->org_reg_no ?? '-' }} | Tel: {{ $setting->org_phone ?? '-' }} | Emel: {{ $setting->org_email ?? '-' }}</div>
    </div>

    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="recipient-box">
                    Kepada:<br>
                    <strong>Pengarah Urusan</strong><br>
                    <strong>{{ $vendor->company_name }}</strong> (No. SSM: {{ $vendor->registration_number }})<br>
                    {{ $vendor->address ?? '-' }}<br>
                    {{ $vendor->postcode }} {{ $vendor->city }} {{ $vendor->state }}<br>
                    Tel: {{ $vendor->company_phone ?? '-' }}
                </div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                Rujukan Fail: <strong>{{ $tender->reference_no }}/SST/{{ date('Y') }}</strong><br>
                Tarikh: <strong>{{ $tender->awarded_at ? $tender->awarded_at->format('d F Y') : now()->format('d F Y') }}</strong><br>
                <div style="margin-top: 10px;">
                    <span class="stamp-box">SURAT RASMI KERAJAAN / ENTITI</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title">SURAT SETUJU TERIMA (LETTER OF ACCEPTANCE)</div>

    <div class="content">
        <p>Tuan/Puan,</p>

        <p><strong>TAJUK PEROLEHAN: {{ strtoupper($tender->title) }} (NO. RUJUKAN: {{ $tender->reference_no }})</strong></p>

        <p>Dengan sukacitanya dimaklumkan bahawa Jawatankuasa / Lembaga Perolehan <strong>{{ $setting->org_name }}</strong> telah meluluskan dan bersetuju menerima tawaran bidaan daripada pihak syarikat tuan/puan bagi pelaksanaan kerja-kerja/bekalan yang dinyatakan di atas.</p>

        <div class="highlight-box">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 35%; padding: 4px 0;"><strong>Harga Kontrak Diluluskan:</strong></td>
                    <td style="width: 65%; padding: 4px 0;"><strong>{{ $setting->currency }} {{ number_format($bid->decrypted_bid_amount, 2) }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Tempoh Pelaksanaan Kontrak:</strong></td>
                    <td style="padding: 4px 0;">{{ $bid->completion_duration_months ?? 12 }} Bulan</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Bon Pelaksanaan (Performance Bond):</strong></td>
                    <td style="padding: 4px 0;">5% daripada jumlah harga kontrak (dalam bentuk Jaminan Bank)</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Tarikh Mula Kerja / Kuat Kuasa:</strong></td>
                    <td style="padding: 4px 0;">Dua (2) minggu dari tarikh penerimaan surat ini</td>
                </tr>
            </table>
        </div>

        <p>Pihak tuan/puan dikehendaki menandatangani Surat Akuan Penerimaan di bawah dan mengembalikannya kepada pihak urus setia dalam tempoh <strong>empat belas (14) hari bekerja</strong> daripada tarikh surat ini bersama-sama dokumen Bon Pelaksanaan serta Polisi Insurans Tanggungan Awam.</p>

        <p>Sekian, terima kasih.</p>
    </div>

    <div class="signature-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Bagi Pihak {{ $setting->org_name }}:</strong><br><br><br><br>
                    ____________________________________<br>
                    <strong>Pengerusi / Ketua Pegawai Eksekutif</strong><br>
                    {{ $setting->org_name }}
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Akuan Penerimaan & Persetujuan Pembekal:</strong><br>
                    Kami mengesahkan penerimaan SST ini mengikut syarat-syarat yang ditetapkan.<br><br><br>
                    ____________________________________<br>
                    Tandatangan Wakil Sah Syarikat & Cop Rasmi<br>
                    Tarikh: ________________________
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 25px; padding: 8px; background-color: #f1f5f9; border-radius: 4px; font-size: 8px; color: #64748b;">
        <strong>KOD KESELAMATAN DIGITAL:</strong><br>
        Dokumen ini dilindungi integriti digital dengan SHA-256 Hash. Pengesahan kesahihan SST boleh dibuat secara dalam talian di: <span style="font-family: monospace;">{{ $verifyUrl }}</span>
    </div>

    <div class="footer">
        {{ $setting->letterhead_footer_text ?? 'Dokumen Rasmi Dijana oleh Sistem e-Perolehan Berintegriti Tinggi.' }}
    </div>
</body>
</html>
