<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Bid;
use App\Models\OrganizationSetting;
use App\Models\Tender;
use App\Models\TenderPurchase;
use App\Models\Vendor;
use App\Services\PdfReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PublicPortalController extends Controller
{
    public function __construct(
        protected PdfReportService $pdfService
    ) {}

    public function index(Request $request)
    {
        $setting = OrganizationSetting::current();
        $selectedCategory = $request->query('category', 'all');
        $searchTerm = trim($request->query('q', ''));

        // Base query for active/published tenders
        $query = Tender::query()
            ->whereIn('status', ['published', 'closed', 'box_opened'])
            ->where(function ($q) {
                $q->whereNull('closing_at')
                    ->orWhere('closing_at', '>=', now()->subDays(7)); // Show recent closed within 7 days
            });

        // Category filter
        if ($selectedCategory !== 'all' && filled($selectedCategory)) {
            $query->where('category', $selectedCategory);
        }

        // Search filter with wildcard escaping to prevent injection/broad scans
        if (filled($searchTerm)) {
            $escapedTerm = addcslashes($searchTerm, '%_\\');
            $query->where(function ($q) use ($escapedTerm) {
                $q->where('title', 'like', "%{$escapedTerm}%")
                    ->orWhere('reference_no', 'like', "%{$escapedTerm}%")
                    ->orWhere('description', 'like', "%{$escapedTerm}%")
                    ->orWhere('eligibility_criteria', 'like', "%{$escapedTerm}%");
            });
        }

        $tenders = $query->orderByRaw("CASE WHEN status = 'published' AND (closing_at IS NULL OR closing_at >= CURRENT_TIMESTAMP) THEN 1 ELSE 2 END")
            ->orderBy('closing_at', 'asc')
            ->paginate(12)
            ->withQueryString();

        // Consolidated category count query
        $activeCategoryRaw = Tender::where('status', 'published')
            ->where(fn ($q) => $q->whereNull('closing_at')->orWhere('closing_at', '>=', now()))
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->all();

        $categoryCounts = [
            'all' => array_sum($activeCategoryRaw),
            'supply' => (int) ($activeCategoryRaw['supply'] ?? 0),
            'service' => (int) ($activeCategoryRaw['service'] ?? 0),
            'work' => (int) ($activeCategoryRaw['work'] ?? 0),
            'consultancy' => (int) ($activeCategoryRaw['consultancy'] ?? 0),
        ];

        // System-wide transparency statistics cached for 60 seconds
        $stats = Cache::remember('portal_transparency_stats', 60, function () use ($categoryCounts) {
            return [
                'active_tenders' => $categoryCounts['all'],
                'evaluated_bids' => Bid::whereNotNull('total_score')->count(),
                'awarded_value' => (float) Tender::where('tenders.status', 'awarded')
                    ->join('bids', 'tenders.awarded_bid_id', '=', 'bids.id')
                    ->sum('bids.decrypted_bid_amount'),
                'registered_vendors' => Vendor::where('compliance_status', 'verified')->count(),
            ];
        });

        // Public Award Board (Recently Awarded Tenders)
        $awardedTenders = Tender::with(['awardedBid.vendor'])
            ->where('status', 'awarded')
            ->orderByDesc('awarded_at')
            ->take(6)
            ->get();

        return view('landing', compact('setting', 'tenders', 'categoryCounts', 'stats', 'awardedTenders', 'selectedCategory', 'searchTerm'));
    }

    public function show(string $slug)
    {
        $setting = OrganizationSetting::current();

        $tender = Tender::with('documents')
            ->where('slug', $slug)
            ->orWhere('reference_no', $slug)
            ->firstOrFail();

        return view('tenders.public-show', compact('tender', 'setting'));
    }

    public function downloadNoticePdf(Tender $tender)
    {
        $path = $this->pdfService->generateTenderNoticePdf($tender);

        if (! Storage::disk('private')->exists($path)) {
            abort(404, 'Dokumen notis iklan tender tidak ditemui.');
        }

        $filename = 'Notis_Tender_'.$tender->reference_no.'.pdf';

        return response()->streamDownload(function () use ($path) {
            echo Storage::disk('private')->get($path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function verifyDocument(string $type, string $ref)
    {
        $setting = OrganizationSetting::current();
        $record = null;
        $title = 'Pengesahan Ketulenan Dokumen Digital';
        $verified = false;
        $details = [];

        switch ($type) {
            case 'receipt':
                $purchase = TenderPurchase::with(['vendor', 'tender'])->where('purchase_reference', $ref)->first();
                if ($purchase && $purchase->isPaid()) {
                    $verified = true;
                    $title = 'Pengesahan Resit Pembelian Dokumen Tender';
                    $details = [
                        'No. Rujukan Resit' => $purchase->purchase_reference,
                        'Nama Syarikat Pembida' => $purchase->vendor->company_name,
                        'No. SSM Syarikat' => $purchase->vendor->registration_number,
                        'Tender' => "[{$purchase->tender->reference_no}] {$purchase->tender->title}",
                        'Jumlah Bayaran' => 'RM '.number_format($purchase->amount, 2),
                        'Tarikh Bayaran' => $purchase->paid_at?->format('d/m/Y H:i:s'),
                        'No. Transaksi Gerbang' => $purchase->affanpay_payment_ref ?? $purchase->affanpay_bill_id,
                        'Status' => 'SAH & TELAH DIBAYAR',
                    ];
                }
                break;

            case 'bid':
                $bid = Bid::with(['vendor', 'tender'])->where('bid_reference', $ref)->first();
                if ($bid && $bid->submitted_at) {
                    $verified = true;
                    $title = 'Pengesahan Borang Akuan Penyerahan Bidaan';
                    $details = [
                        'No. Rujukan Bidaan' => $bid->bid_reference,
                        'Nama Pembida' => $bid->vendor->company_name,
                        'Tender Disertai' => "[{$bid->tender->reference_no}] {$bid->tender->title}",
                        'Tarikh & Masa Penyerahan' => $bid->submitted_at->format('d/m/Y H:i:s'),
                        'Hash Pakej Bidaan (SHA-256)' => $bid->sha256_package_hash,
                        'Status Bidaan' => strtoupper($bid->status),
                    ];
                }
                break;

            case 'opening':
                $tender = Tender::with('witnesses.user')->where('reference_no', $ref)->first();
                if ($tender && $tender->isBoxOpened()) {
                    $verified = true;
                    $title = 'Pengesahan Slip Pembukaan Peti Tender Dua Saksi';
                    $details = [
                        'No. Rujukan Tender' => $tender->reference_no,
                        'Tajuk Tender' => $tender->title,
                        'Masa Dibuka Rasmi' => $tender->dual_witness_opened_at?->format('d/m/Y H:i:s'),
                        'Bilangan Saksi Sah' => $tender->witnesses->count().' Saksi Digital',
                        'Jumlah Bidaan Dinyahsulit' => $tender->bids()->count().' Sampul Bidaan',
                        'Status' => 'PETI TENDER TELAH DIBUKA SECARA SAH',
                    ];
                }
                break;

            case 'evaluation':
                $tender = Tender::with(['awardedBid.vendor'])->where('reference_no', $ref)->first();
                if ($tender) {
                    $verified = true;
                    $title = 'Pengesahan Laporan Rasmi Penilaian Perolehan';
                    $details = [
                        'No. Rujukan Tender' => $tender->reference_no,
                        'Tajuk Perolehan' => $tender->title,
                        'Status Semasa' => strtoupper(str_replace('_', ' ', $tender->status)),
                        'Ambang Markah Teknikal' => $tender->min_technical_score.'%',
                        'Pemenang Dianugerahkan' => $tender->awardedBid ? $tender->awardedBid->vendor->company_name : 'Belum Dimuktamadkan',
                    ];
                }
                break;

            case 'sst':
                $tender = Tender::with(['awardedBid.vendor'])->where('reference_no', $ref)->first();
                if ($tender && $tender->isAwarded() && $tender->awardedBid) {
                    $verified = true;
                    $title = 'Pengesahan Surat Setuju Terima (SST)';
                    $details = [
                        'No. Rujukan Tender' => $tender->reference_no,
                        'Penerima Anugerah' => $tender->awardedBid->vendor->company_name,
                        'No. Pendaftaran Pembekal' => $tender->awardedBid->vendor->registration_number,
                        'Nilai Kontrak Rasmi' => 'RM '.number_format($tender->awardedBid->decrypted_bid_amount, 2),
                        'Tarikh Anugerah' => $tender->awarded_at?->format('d/m/Y H:i:s'),
                        'Integriti Keputusan' => 'DISAHKAN OLEH LEMBAGA PEROLEHAN',
                    ];
                }
                break;

            case 'tender':
                $tender = Tender::where('reference_no', $ref)->first();
                if ($tender) {
                    $verified = true;
                    $title = 'Pengesahan Notis Iklan Tender Rasmi';
                    $details = [
                        'No. Rujukan Tender' => $tender->reference_no,
                        'Tajuk Tender' => $tender->title,
                        'Kategori' => strtoupper($tender->category),
                        'Tarikh Mula Iklan' => $tender->published_at?->format('d/m/Y H:i:s'),
                        'Tarikh Tutup' => $tender->closing_at?->format('d/m/Y H:i:s'),
                        'Status Iklan' => strtoupper($tender->status),
                    ];
                }
                break;
        }

        $escapedRef = addcslashes($ref, '%_\\');
        $auditLog = AuditTrail::where('description', 'like', "%{$escapedRef}%")
            ->latest('id')
            ->first();

        return view('verify.document', compact('setting', 'type', 'ref', 'verified', 'title', 'details', 'auditLog'));
    }
}
