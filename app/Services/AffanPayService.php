<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\OrganizationSetting;
use App\Models\TenderPurchase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AffanPayService
{
    protected string $environment;

    protected string $baseUrl;

    protected ?string $email;

    protected ?string $password;

    protected ?string $token = null;

    protected bool $simulationMode;

    public function __construct()
    {
        $this->environment = config('affanpay.environment', 'sandbox');
        $this->baseUrl = config("affanpay.{$this->environment}.url", 'https://sandbox.affanpay.my');
        $this->email = config("affanpay.{$this->environment}.email");
        $this->password = config("affanpay.{$this->environment}.password");
        $this->simulationMode = (bool) config('affanpay.simulation_mode', false);
    }

    protected function authenticate(): bool
    {
        if ($this->simulationMode || empty($this->email) || empty($this->password)) {
            return false;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->connectTimeout(5)
                ->post($this->baseUrl.'/api/token', [
                    'email' => $this->email,
                    'password' => $this->password,
                ]);

            if ($response->successful()) {
                $this->token = $response->json('token');

                return true;
            }
        } catch (\Exception $e) {
            Log::error('AffanPay authentication exception: '.$e->getMessage());
        }

        return false;
    }

    public function createBillForPurchase(TenderPurchase $purchase): array
    {
        $setting = OrganizationSetting::current();
        $vendor = $purchase->vendor;
        $tender = $purchase->tender;

        // In simulation mode or if no credentials provided, return mock checkout URL
        if ($this->simulationMode || ! $this->authenticate()) {
            $mockBillId = 'BILL-SIM-'.strtoupper(Str::random(10));
            $purchase->update([
                'affanpay_bill_id' => $mockBillId,
                'payment_url' => route('affanpay.simulate', ['reference' => $purchase->purchase_reference]),
            ]);

            return [
                'success' => true,
                'bill_id' => $mockBillId,
                'payment_url' => $purchase->payment_url,
                'simulated' => true,
            ];
        }

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->timeout(12)
                ->post($this->baseUrl.'/api/v1/bill', [
                    'name' => "Tender Doc [{$tender->reference_no}] - {$vendor->company_name}",
                    'description' => "Pembelian Dokumen Tender: {$tender->title}",
                    'amount' => $purchase->amount,
                    'external_ref' => $purchase->purchase_reference,
                    'customer_name' => $vendor->company_name,
                    'customer_email' => $vendor->company_email ?? $vendor->user->email,
                    'customer_phone' => $vendor->company_phone ?? $vendor->user->phone,
                    'redirect_url' => route('affanpay.return', ['reference' => $purchase->purchase_reference]),
                    'callback_url' => route('affanpay.webhook'),
                ]);

            $responseData = $response->json() ?? [];

            if ($response->successful()) {
                $billId = $responseData['id'] ?? data_get($responseData, 'data.id');
                $paymentUrl = $responseData['url'] ?? data_get($responseData, 'data.url');

                $purchase->update([
                    'affanpay_bill_id' => $billId,
                    'payment_url' => $paymentUrl,
                    'payment_payload' => $responseData,
                ]);

                return [
                    'success' => true,
                    'bill_id' => $billId,
                    'payment_url' => $paymentUrl,
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Ralat semasa menjana bil di AffanPay.',
            ];
        } catch (\Exception $e) {
            Log::error('AffanPay createBill exception: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processWebhook(array $payload, ?string $signature = null): array
    {
        $expectedSecret = config('affanpay.webhook_secret');
        if (! empty($expectedSecret)) {
            if (empty($signature) || ! hash_equals($expectedSecret, $signature)) {
                return ['status' => 'invalid_signature'];
            }
        }

        $purchaseRef = $payload['external_ref'] ?? data_get($payload, 'data.external_ref');
        if (! $purchaseRef) {
            return ['status' => 'missing_reference'];
        }

        $purchase = TenderPurchase::where('purchase_reference', $purchaseRef)->first();
        if (! $purchase) {
            return ['status' => 'purchase_not_found'];
        }

        // Idempotency: if already paid, do not re-process
        if ($purchase->isPaid()) {
            return ['status' => 'already_paid'];
        }

        $status = strtolower($payload['status'] ?? data_get($payload, 'data.status') ?? '');
        $paymentRef = $payload['payment_ref'] ?? data_get($payload, 'data.payments.0.payment_ref') ?? 'PAY-'.strtoupper(Str::random(8));

        if (in_array($status, ['paid', 'success', 'successful', 'completed'])) {
            $purchase->update([
                'status' => 'paid',
                'affanpay_payment_ref' => $paymentRef,
                'paid_at' => now(),
                'payment_payload' => array_merge($purchase->payment_payload ?? [], ['webhook' => $payload]),
            ]);

            // Auto-generate official PDF receipt
            app(PdfReportService::class)->generatePurchaseReceiptPdf($purchase);

            AuditTrail::log(
                eventType: 'AFFANPAY_PAYMENT_SUCCESS',
                description: "Pembayaran dokumen tender berjaya bagi rujukan {$purchase->purchase_reference} (RM {$purchase->amount})",
                auditable: $purchase,
                newValues: ['status' => 'paid', 'payment_ref' => $paymentRef],
                userId: $purchase->vendor->user_id
            );

            return ['status' => 'paid_processed'];
        }

        if (in_array($status, ['failed', 'cancelled', 'expired'])) {
            $purchase->update(['status' => 'failed']);

            return ['status' => 'marked_failed'];
        }

        return ['status' => 'ignored'];
    }

    public function verifyPaymentStatus(TenderPurchase $purchase): bool
    {
        if ($purchase->isPaid()) {
            return true;
        }

        if ($this->simulationMode) {
            return $purchase->isPaid();
        }

        if (! $purchase->affanpay_bill_id || ! $this->authenticate()) {
            return false;
        }

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->timeout(10)
                ->get($this->baseUrl.'/api/v1/bill/'.$purchase->affanpay_bill_id);

            if ($response->successful()) {
                $data = $response->json();
                $status = strtolower(data_get($data, 'data.status') ?? $data['status'] ?? '');

                if (in_array($status, ['paid', 'success', 'completed'])) {
                    $purchase->update([
                        'status' => 'paid',
                        'affanpay_payment_ref' => data_get($data, 'data.payments.0.payment_ref') ?? 'AFFAN-'.time(),
                        'paid_at' => now(),
                    ]);

                    app(PdfReportService::class)->generatePurchaseReceiptPdf($purchase);

                    AuditTrail::log(
                        eventType: 'AFFANPAY_QUERY_VERIFIED',
                        description: "Status bayaran disahkan berjaya melalui API requery AffanPay bagi {$purchase->purchase_reference}",
                        auditable: $purchase,
                        userId: $purchase->vendor->user_id
                    );

                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error('AffanPay verifyPaymentStatus exception: '.$e->getMessage());
        }

        return false;
    }
}
