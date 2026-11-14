<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Tender;
use App\Models\TenderDocument;
use App\Services\PdfReportService;
use App\Services\SealedBiddingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BidController extends Controller
{
    public function __construct(
        protected SealedBiddingService $biddingService,
        protected PdfReportService $pdfService
    ) {}

    /**
     * Penyerahan Bidaan Bertutup (Sealed Bidding Submission)
     */
    public function submit(Request $request, Tender $tender)
    {
        $user = Auth::user();

        if (! $user || ! $user->vendor) {
            return redirect('/vendor/login')->with('error', 'Sila log masuk sebagai vendor untuk menghantar bidaan.');
        }

        $vendor = $user->vendor;

        $validated = $request->validate([
            'bid_amount' => 'required|numeric|min:1',
            'completion_duration_months' => 'required|integer|min:1|max:120',
            'technical_summary' => 'nullable|string|max:5000',
            'technical_proposal' => 'required|file|mimes:pdf,zip|max:51200', // max 50MB
            'financial_proposal' => 'required|file|mimes:pdf,zip|max:51200', // max 50MB
            'commercial_breakdown' => 'nullable|file|mimes:pdf,xlsx,xls|max:20480',
        ]);

        $files = [
            'technical_proposal' => $request->file('technical_proposal'),
            'financial_proposal' => $request->file('financial_proposal'),
        ];

        if ($request->hasFile('commercial_breakdown')) {
            $files['commercial_breakdown'] = $request->file('commercial_breakdown');
        }

        try {
            $bid = $this->biddingService->submitBid($vendor, $tender, $validated, $files);

            return redirect('/vendor')->with('success', "Bidaan bertutup rujukan {$bid->bid_reference} berjaya diserahkan dan dikunci secara digital! Slip penyerahan rasmi telah dijana.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Muat Turun Borang Akuan Penyerahan Bidaan Rasmi (Slip PDF)
     */
    public function downloadSlip(Bid $bid)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        // Access: vendor owner or urus setia / admin
        $isOwner = $user->vendor && $user->vendor->id === $bid->vendor_id;
        $isOfficial = $user->isAdmin() || $user->isUrusSetia();

        if (! $isOwner && ! $isOfficial) {
            abort(403, 'Akses dinafikan.');
        }

        if (empty($bid->submission_slip_pdf_path) || ! Storage::disk('private')->exists($bid->submission_slip_pdf_path)) {
            $this->pdfService->generateBidSubmissionSlipPdf($bid);
        }

        $path = $bid->submission_slip_pdf_path;
        $filename = 'Akuan_Penyerahan_'.$bid->bid_reference.'.pdf';

        return response()->streamDownload(function () use ($path) {
            echo Storage::disk('private')->get($path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Muat Turun Dokumen Spesifikasi Tender (Hanya untuk Vendor yang telah membayar atau Awam)
     */
    public function downloadTenderDocument(TenderDocument $document)
    {
        $user = Auth::user();
        $tender = $document->tender;

        // If public notice, allow anyone
        if ($document->is_public_notice) {
            $disk = Storage::disk($document->disk ?? 'private');
            if ($disk->exists($document->file_path)) {
                return response()->streamDownload(function () use ($disk, $document) {
                    echo $disk->get($document->file_path);
                }, $document->file_name, ['Content-Type' => $document->mime_type ?? 'application/pdf']);
            }
        }

        // Otherwise requires vendor to have purchased or admin/penilai access
        if (! $user) {
            return redirect('/vendor/login')->with('warning', 'Sila log masuk untuk memuat turun dokumen spesifikasi.');
        }

        $canAccess = false;
        if ($user->isAdmin() || $user->isUrusSetia() || $user->isPenilaiTeknikal() || $user->isPenilaiKewangan()) {
            $canAccess = true;
        } elseif ($user->vendor) {
            $canAccess = ($tender->document_price <= 0) || $user->vendor->hasPurchasedTender($tender->id);
        }

        if (! $canAccess) {
            abort(403, 'Anda perlu membuat pembelian dokumen tender ini terlebih dahulu.');
        }

        $disk = Storage::disk($document->disk ?? 'private');
        if (! $disk->exists($document->file_path)) {
            abort(404, 'Fail dokumen tender tidak ditemui di storan.');
        }

        return response()->streamDownload(function () use ($disk, $document) {
            echo $disk->get($document->file_path);
        }, $document->file_name, ['Content-Type' => $document->mime_type ?? 'application/octet-stream']);
    }
}
