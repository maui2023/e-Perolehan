<?php

namespace App\Services;

use App\Models\Bid;
use App\Models\OrganizationSetting;
use App\Models\Tender;
use App\Models\TenderPurchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfReportService
{
    /**
     * 1. Resit Rasmi Pembelian Dokumen Tender
     */
    public function generatePurchaseReceiptPdf(TenderPurchase $purchase): string
    {
        $setting = OrganizationSetting::current();
        $tender = $purchase->tender;
        $vendor = $purchase->vendor;

        $pdf = Pdf::loadView('pdf.receipt', [
            'purchase' => $purchase,
            'tender' => $tender,
            'vendor' => $vendor,
            'setting' => $setting,
            'verifyUrl' => route('verify.document', ['type' => 'receipt', 'ref' => $purchase->purchase_reference]),
        ])->setPaper('a4', 'portrait');

        $relativePath = 'receipts/'.$purchase->purchase_reference.'.pdf';
        Storage::disk('private')->put($relativePath, $pdf->output());

        $purchase->update(['receipt_pdf_path' => $relativePath]);

        return $relativePath;
    }

    /**
     * 2. Borang Akuan Penyerahan Bidaan Rasmi (Submission Slip)
     */
    public function generateBidSubmissionSlipPdf(Bid $bid): string
    {
        $setting = OrganizationSetting::current();
        $tender = $bid->tender;
        $vendor = $bid->vendor;

        $pdf = Pdf::loadView('pdf.submission_slip', [
            'bid' => $bid,
            'tender' => $tender,
            'vendor' => $vendor,
            'setting' => $setting,
            'verifyUrl' => route('verify.document', ['type' => 'bid', 'ref' => $bid->bid_reference]),
        ])->setPaper('a4', 'portrait');

        $relativePath = 'bids/'.$tender->id.'/slips/'.$bid->bid_reference.'.pdf';
        Storage::disk('private')->put($relativePath, $pdf->output());

        $bid->update(['submission_slip_pdf_path' => $relativePath]);

        return $relativePath;
    }

    /**
     * 3. Slip Pembukaan Peti Tender Dua Saksi Digital
     */
    public function generateTenderOpeningSlipPdf(Tender $tender): string
    {
        $setting = OrganizationSetting::current();
        $witnesses = $tender->witnesses()->with('user')->get();
        $bids = $tender->bids()->with('vendor')->get();

        $pdf = Pdf::loadView('pdf.opening_slip', [
            'tender' => $tender,
            'witnesses' => $witnesses,
            'bids' => $bids,
            'setting' => $setting,
            'verifyUrl' => route('verify.document', ['type' => 'opening', 'ref' => $tender->reference_no]),
        ])->setPaper('a4', 'portrait');

        $relativePath = 'tenders/'.$tender->id.'/opening_slip.pdf';
        Storage::disk('private')->put($relativePath, $pdf->output());

        $tender->update(['opening_slip_pdf_path' => $relativePath]);

        return $relativePath;
    }

    /**
     * 4. Laporan Lengkap Penilaian Tender & Syor Lembaga
     */
    public function generateEvaluationReportPdf(Tender $tender): string
    {
        $setting = OrganizationSetting::current();
        $bids = $tender->bids()
            ->with(['vendor', 'evaluations.evaluator'])
            ->orderByDesc('total_score')
            ->get();

        $pdf = Pdf::loadView('pdf.evaluation_report', [
            'tender' => $tender,
            'bids' => $bids,
            'setting' => $setting,
            'verifyUrl' => route('verify.document', ['type' => 'evaluation', 'ref' => $tender->reference_no]),
        ])->setPaper('a4', 'portrait');

        $relativePath = 'tenders/'.$tender->id.'/evaluation_report.pdf';
        Storage::disk('private')->put($relativePath, $pdf->output());

        $tender->update(['evaluation_report_pdf_path' => $relativePath]);

        return $relativePath;
    }

    /**
     * 5. Surat Setuju Terima (SST / Letter of Acceptance)
     */
    public function generateSstLetterPdf(Tender $tender): string
    {
        $setting = OrganizationSetting::current();
        $awardedBid = $tender->awardedBid;
        if (! $awardedBid) {
            throw new \RuntimeException('Tender belum diawardkan kepada mana-mana bidaan.');
        }

        $vendor = $awardedBid->vendor;

        $pdf = Pdf::loadView('pdf.sst_letter', [
            'tender' => $tender,
            'bid' => $awardedBid,
            'vendor' => $vendor,
            'setting' => $setting,
            'verifyUrl' => route('verify.document', ['type' => 'sst', 'ref' => $tender->reference_no]),
        ])->setPaper('a4', 'portrait');

        $relativePath = 'tenders/'.$tender->id.'/sst_letter_'.$tender->reference_no.'.pdf';
        Storage::disk('private')->put($relativePath, $pdf->output());

        $tender->update(['sst_letter_pdf_path' => $relativePath]);

        return $relativePath;
    }

    /**
     * 6. Kenyataan Notis Iklan Tender Awam (Public Notice PDF)
     */
    public function generateTenderNoticePdf(Tender $tender): string
    {
        $setting = OrganizationSetting::current();

        $pdf = Pdf::loadView('pdf.tender_notice', [
            'tender' => $tender,
            'setting' => $setting,
            'verifyUrl' => route('verify.document', ['type' => 'tender', 'ref' => $tender->reference_no]),
        ])->setPaper('a4', 'portrait');

        $relativePath = 'tenders/'.$tender->id.'/notice_'.$tender->reference_no.'.pdf';
        Storage::disk('private')->put($relativePath, $pdf->output());

        return $relativePath;
    }
}
