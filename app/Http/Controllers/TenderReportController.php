<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use App\Services\PdfReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TenderReportController extends Controller
{
    public function __construct(
        protected PdfReportService $pdfService
    ) {}

    public function openingSlip(Tender $tender)
    {
        $user = Auth::user();
        if (! $user || (! $user->isAdmin() && ! $user->isUrusSetia() && ! $user->isPenilaiTeknikal() && ! $user->isPenilaiKewangan())) {
            abort(403);
        }

        if (empty($tender->opening_slip_pdf_path) || ! Storage::disk('private')->exists($tender->opening_slip_pdf_path)) {
            $this->pdfService->generateTenderOpeningSlipPdf($tender);
        }

        $path = $tender->opening_slip_pdf_path;
        $filename = 'Slip_Pembukaan_Peti_'.$tender->reference_no.'.pdf';

        return response()->streamDownload(function () use ($path) {
            echo Storage::disk('private')->get($path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function evaluationReport(Tender $tender)
    {
        $user = Auth::user();
        if (! $user || (! $user->isAdmin() && ! $user->isUrusSetia())) {
            abort(403);
        }

        if (empty($tender->evaluation_report_pdf_path) || ! Storage::disk('private')->exists($tender->evaluation_report_pdf_path)) {
            $this->pdfService->generateEvaluationReportPdf($tender);
        }

        $path = $tender->evaluation_report_pdf_path;
        $filename = 'Laporan_Penilaian_'.$tender->reference_no.'.pdf';

        return response()->streamDownload(function () use ($path) {
            echo Storage::disk('private')->get($path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function sstLetter(Tender $tender)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $isAwardedVendor = $user->vendor && $tender->awardedBid && $tender->awardedBid->vendor_id === $user->vendor->id;
        $isOfficial = $user->isAdmin() || $user->isUrusSetia();

        if (! $isAwardedVendor && ! $isOfficial) {
            abort(403, 'Akses dinafikan.');
        }

        if (empty($tender->sst_letter_pdf_path) || ! Storage::disk('private')->exists($tender->sst_letter_pdf_path)) {
            $this->pdfService->generateSstLetterPdf($tender);
        }

        $path = $tender->sst_letter_pdf_path;
        $filename = 'Surat_Setuju_Terima_'.$tender->reference_no.'.pdf';

        return response()->streamDownload(function () use ($path) {
            echo Storage::disk('private')->get($path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }
}
