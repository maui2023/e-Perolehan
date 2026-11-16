<?php

namespace App\Http\Controllers;

use App\Models\OrganizationSetting;
use App\Models\Tender;
use App\Models\TenderPurchase;
use App\Services\AffanPayService;
use App\Services\PdfReportService;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AffanPayController extends Controller
{
    public function __construct(
        protected AffanPayService $affanPayService,
        protected PdfReportService $pdfService
    ) {}

    /**
     * Inisialisasi Pembelian Dokumen Tender oleh Vendor
     */
    public function purchase(Request $request, Tender $tender)
    {
        $user = Auth::user();

        // Must be logged in as vendor
        if (! $user || $user->role !== 'vendor' || ! $user->vendor) {
            session()->put('url.intended', route('tenders.purchase', $tender->id));

            return redirect('/vendor/login')->with('warning', 'Sila log masuk sebagai Pembekal/Vendor untuk membeli dokumen tender.');
        }

        $vendor = $user->vendor;

        // If tender has zero document price, grant purchase directly
        if ($tender->document_price <= 0) {
            $purchase = TenderPurchase::firstOrCreate(
                [
                    'tender_id' => $tender->id,
                    'vendor_id' => $vendor->id,
                ],
                [
                    'purchase_reference' => 'FREE-'.strtoupper(Str::random(10)),
                    'amount' => 0.00,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'affanpay_bill_id' => 'FREE-ACCESS',
                    'affanpay_payment_ref' => 'FREE-GRANT',
                    'idempotency_key' => (string) Str::uuid(),
                ]
            );

            if (! $purchase->isPaid()) {
                $purchase->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            $this->pdfService->generatePurchaseReceiptPdf($purchase);

            Notification::make()
                ->title('Akses Dokumen Percuma Dibuka')
                ->body('Dokumen tender percuma telah berjaya dibuka untuk syarikat anda. Sila lengkapkan borang bidaan.')
                ->success()
                ->send();

            return redirect()->route('filament.vendor.resources.bids.create', ['tender_id' => $tender->id])
                ->with('success', 'Dokumen tender percuma telah berjaya dibuka untuk syarikat anda!');
        }

        // Check existing paid purchase
        $existing = TenderPurchase::where('tender_id', $tender->id)
            ->where('vendor_id', $vendor->id)
            ->where('status', 'paid')
            ->first();

        if ($existing) {
            Notification::make()
                ->title('Dokumen Telah Dimiliki')
                ->body('Syarikat anda telah pun memiliki dokumen tender ini. Anda boleh teruskan pengisian bidaan.')
                ->info()
                ->send();

            return redirect()->route('filament.vendor.resources.bids.create', ['tender_id' => $tender->id])
                ->with('info', 'Syarikat anda telah pun memiliki dokumen tender ini.');
        }

        // Find or create pending purchase
        $purchase = TenderPurchase::firstOrCreate(
            [
                'tender_id' => $tender->id,
                'vendor_id' => $vendor->id,
                'status' => 'pending',
            ],
            [
                'purchase_reference' => 'AP-'.date('Ymd').'-'.strtoupper(Str::random(6)),
                'amount' => $tender->document_price,
                'idempotency_key' => (string) Str::uuid(),
            ]
        );

        $result = $this->affanPayService->createBillForPurchase($purchase);

        if ($result['success'] && ! empty($result['payment_url'])) {
            return redirect()->away($result['payment_url']);
        }

        return redirect()->back()->with('error', $result['error'] ?? 'Ralat semasa menyambung ke gerbang pembayaran AffanPay.');
    }

    /**
     * Halaman Simulasi Pembayaran AffanPay (Untuk Ujian Tempatan & Sandbox)
     */
    public function simulate(string $reference)
    {
        if (! app()->environment('local', 'testing') && ! config('affanpay.simulation_mode', false)) {
            abort(403, 'Simulasi pembayaran AffanPay tidak dibenarkan dalam persekitaran ini.');
        }

        $purchase = TenderPurchase::with(['vendor', 'tender'])->where('purchase_reference', $reference)->firstOrFail();

        $user = Auth::user();
        if ($user && ! $user->isAdmin() && ! $user->isUrusSetia()) {
            if (! $user->vendor || $user->vendor->id !== $purchase->vendor_id) {
                abort(403, 'Akses dinafikan.');
            }
        } elseif (! $user && ! app()->environment('testing')) {
            return redirect('/vendor/login')->with('warning', 'Sila log masuk untuk meneruskan.');
        }

        $setting = OrganizationSetting::current();

        return view('affanpay.simulate', compact('purchase', 'setting'));
    }

    /**
     * Proses Hasil Tindakan Simulasi
     */
    public function processSimulation(Request $request, string $reference)
    {
        if (! app()->environment('local', 'testing') && ! config('affanpay.simulation_mode', false)) {
            abort(403, 'Simulasi pembayaran AffanPay tidak dibenarkan dalam persekitaran ini.');
        }

        $purchase = TenderPurchase::where('purchase_reference', $reference)->firstOrFail();

        $user = Auth::user();
        if ($user && ! $user->isAdmin() && ! $user->isUrusSetia()) {
            if (! $user->vendor || $user->vendor->id !== $purchase->vendor_id) {
                abort(403, 'Akses dinafikan.');
            }
        } elseif (! $user && ! app()->environment('testing')) {
            return redirect('/vendor/login')->with('warning', 'Sila log masuk untuk meneruskan.');
        }

        $action = $request->input('action', 'success');

        if ($action === 'success') {
            $paymentRef = 'SIM-AFFAN-'.strtoupper(Str::random(10));
            $payload = [
                'status' => 'paid',
                'external_ref' => $purchase->purchase_reference,
                'payment_ref' => $paymentRef,
                'amount' => $purchase->amount,
                'timestamp' => now()->toIso8601String(),
            ];

            $this->affanPayService->processWebhook($payload, config('affanpay.webhook_secret'));

            return redirect()->route('affanpay.return', ['reference' => $purchase->purchase_reference])
                ->with('success', 'Simulasi bayaran AffanPay berjaya diselesaikan!');
        }

        $purchase->update(['status' => 'failed']);

        return redirect()->route('affanpay.return', ['reference' => $purchase->purchase_reference])
            ->with('error', 'Simulasi pembayaran dibatalkan atau gagal.');
    }

    /**
     * Callback Return URL Selepas Checkout AffanPay
     */
    public function returnCallback(Request $request, string $reference)
    {
        $purchase = TenderPurchase::with(['tender', 'vendor'])->where('purchase_reference', $reference)->firstOrFail();

        // If still pending, trigger requery verify status
        if ($purchase->isPending()) {
            $this->affanPayService->verifyPaymentStatus($purchase);
        }

        if ($purchase->isPaid()) {
            Notification::make()
                ->title('Pembayaran Berjaya')
                ->body("Pembayaran RM {$purchase->amount} bagi tender [{$purchase->tender->reference_no}] berjaya! Sila teruskan penyerahan bidaan.")
                ->success()
                ->send();

            return redirect()->route('filament.vendor.resources.bids.create', ['tender_id' => $purchase->tender_id])
                ->with('success', "Pembayaran RM {$purchase->amount} bagi tender [{$purchase->tender->reference_no}] berjaya! Dokumen spesifikasi kini sedia dimuat turun.");
        }

        Notification::make()
            ->title('Status Pembayaran Belum Disahkan')
            ->body('Status pembayaran masih belum disahkan. Sila semak semula dalam beberapa minit.')
            ->warning()
            ->send();

        return redirect('/vendor')->with(
            'warning',
            'Status pembayaran masih belum disahkan. Sila semak semula dalam beberapa minit.'
        );
    }

    /**
     * Webhook Endpoint bagi Notifikasi Server-to-Server AffanPay
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-AffanPay-Signature')
            ?? $request->query('token')
            ?? $request->header('X-AffanPay-Webhook-Secret');

        $result = $this->affanPayService->processWebhook($payload, $signature);

        $statusCodes = [
            'invalid_signature' => 401,
            'purchase_not_found' => 404,
            'missing_reference' => 422,
        ];

        $code = $statusCodes[$result['status']] ?? 200;

        return response()->json([
            'success' => in_array($result['status'], ['paid_processed', 'already_paid']),
            'result' => $result,
        ], $code);
    }

    /**
     * Muat Turun Resit Rasmi Pembelian PDF
     */
    public function downloadReceipt(TenderPurchase $purchase)
    {
        $user = Auth::user();

        // Access control: vendor owner or urus setia/admin
        if ($user && ($user->isAdmin() || $user->isUrusSetia() || ($user->vendor && $user->vendor->id === $purchase->vendor_id))) {
            if (empty($purchase->receipt_pdf_path) || ! Storage::disk('private')->exists($purchase->receipt_pdf_path)) {
                $this->pdfService->generatePurchaseReceiptPdf($purchase);
            }

            $path = $purchase->receipt_pdf_path;
            $filename = 'Resit_'.$purchase->purchase_reference.'.pdf';

            return response()->streamDownload(function () use ($path) {
                echo Storage::disk('private')->get($path);
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        abort(403, 'Anda tidak mempunyai kebenaran untuk memuat turun resit ini.');
    }
}
