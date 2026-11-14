<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Resit Rasmi Pembelian Dokumen Tender - {{ $purchase->purchase_reference }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; margin: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .org-name { font-size: 18px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .org-sub { font-size: 11px; color: #64748b; }
        .doc-title { font-size: 16px; font-weight: bold; text-align: right; color: #0284c7; }
        .receipt-no { font-size: 12px; text-align: right; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 8px 10px; text-align: left; }
        .table-bordered th { background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11px; }
        .table-bordered td { border: 1px solid #e2e8f0; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .total-box { margin-top: 20px; float: right; width: 45%; }
        .total-box table td { padding: 4px 8px; }
        .grand-total { font-size: 14px; font-weight: bold; color: #0f172a; background-color: #f8fafc; }
        .footer { clear: both; margin-top: 50px; padding-top: 15px; border-top: 1px dashed #cbd5e1; font-size: 10px; color: #94a3b8; text-align: center; }
        .security-box { margin-top: 25px; padding: 10px; background-color: #f8fafc; border-left: 3px solid #0284c7; font-size: 9px; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <table style="margin-top: 0;">
            <tr>
                <td style="width: 60%; vertical-align: top; padding: 0;">
                    <div class="org-name">{{ $setting->org_name }}</div>
                    <div class="org-sub">{{ $setting->org_address }}</div>
                    <div class="org-sub">No. Pendaftaran: {{ $setting->org_reg_no ?? '-' }} | Emel: {{ $setting->org_email ?? '-' }}</div>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right; padding: 0;">
                    <div class="doc-title">RESIT RASMI PEMBELIAN</div>
                    <div class="receipt-no">No. Resit: <strong>{{ $purchase->purchase_reference }}</strong></div>
                    <div class="receipt-no">Tarikh Bayaran: {{ $purchase->paid_at ? $purchase->paid_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</div>
                    <div style="margin-top: 6px;"><span class="badge badge-success">TELAH DIBAYAR</span></div>
                </td>
            </tr>
        </table>
    </div>

    <table style="margin-top: 5px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 0;">
                <strong style="color: #475569; font-size: 11px;">DITERIMA DARIPADA:</strong><br>
                <strong style="font-size: 13px;">{{ $vendor->company_name }}</strong><br>
                No. SSM / Pendaftaran: {{ $vendor->registration_number }}<br>
                {{ $vendor->address ?? '-' }}<br>
                Tel: {{ $vendor->company_phone ?? '-' }} | Emel: {{ $vendor->company_email ?? '-' }}
            </td>
            <td style="width: 50%; vertical-align: top; padding: 0; text-align: right;">
                <strong style="color: #475569; font-size: 11px;">SALURAN BAYARAN:</strong><br>
                Gerbang Pembayaran: <strong>AffanPay</strong><br>
                ID Transaksi Bil: {{ $purchase->affanpay_bill_id ?? '-' }}<br>
                No. Rujukan Bayaran: {{ $purchase->affanpay_payment_ref ?? '-' }}<br>
                Kunci Idempotensi: {{ substr($purchase->idempotency_key, 0, 16) }}...
            </td>
        </tr>
    </table>

    <table class="table-bordered" style="margin-top: 20px;">
        <thead>
            <tr>
                <th style="width: 5%;">BIL</th>
                <th style="width: 65%;">PERIHAL DOKUMEN / ITEM</th>
                <th style="width: 15%; text-align: center;">KOD TENDER</th>
                <th style="width: 15%; text-align: right;">JUMLAH ({{ $setting->currency }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>
                    <strong>Yuran Pembelian Dokumen Spesifikasi Tender</strong><br>
                    <span style="color: #64748b;">{{ $tender->title }}</span>
                </td>
                <td style="text-align: center;">{{ $tender->reference_no }}</td>
                <td style="text-align: right;">{{ number_format($purchase->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <table>
            <tr>
                <td>Jumlah Kasar:</td>
                <td style="text-align: right;">{{ $setting->currency }} {{ number_format($purchase->amount, 2) }}</td>
            </tr>
            <tr>
                <td>Cukai Perkhidmatan ({{ $setting->tax_percentage }}%):</td>
                <td style="text-align: right;">{{ $setting->currency }} 0.00</td>
            </tr>
            <tr class="grand-total">
                <td>JUMLAH BERSIH:</td>
                <td style="text-align: right;">{{ $setting->currency }} {{ number_format($purchase->amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="security-box">
        <strong>PENGESAHAN KESELAMATAN DIGITAL:</strong><br>
        Resit ini dijana secara berkomputer melalui Sistem e-Perolehan Berintegriti Tinggi. Tandatangan fizikal tidak diperlukan.<br>
        Pautan Pengesahan Dokumen: <span style="font-family: monospace;">{{ $verifyUrl }}</span>
    </div>

    <div class="footer">
        {{ $setting->letterhead_footer_text ?? 'Hakcipta Terpelihara. Dicetak secara rasmi melalui Sistem e-Perolehan.' }}
    </div>
</body>
</html>
