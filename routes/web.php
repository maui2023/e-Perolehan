<?php

use App\Http\Controllers\AffanPayController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\TenderReportController;
use App\Http\Controllers\WitnessController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Awam & Ketelusan e-Perolehan
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPortalController::class, 'index'])->name('portal.home');
Route::get('/tenders/{slug}', [PublicPortalController::class, 'show'])->name('tenders.public-show');
Route::get('/tenders/{tender}/notice-pdf', [PublicPortalController::class, 'downloadNoticePdf'])->name('tenders.notice-pdf');
Route::get('/verify/{type}/{ref}', [PublicPortalController::class, 'verifyDocument'])->name('verify.document');

/*
|--------------------------------------------------------------------------
| Gerbang Pembayaran AffanPay (maui2023/affanpay-laravel)
|--------------------------------------------------------------------------
*/
Route::post('/tenders/{tender}/purchase', [AffanPayController::class, 'purchase'])->name('tenders.purchase');
Route::get('/affanpay/simulate/{reference}', [AffanPayController::class, 'simulate'])->name('affanpay.simulate');
Route::post('/affanpay/simulate/{reference}', [AffanPayController::class, 'processSimulation'])->name('affanpay.simulate.post');
Route::get('/affanpay/return/{reference}', [AffanPayController::class, 'returnCallback'])->name('affanpay.return');
Route::post('/affanpay/webhook', [AffanPayController::class, 'webhook'])->name('affanpay.webhook');
Route::post('/api/v1/payments/webhook', [AffanPayController::class, 'webhook'])->name('webhook.affanpay.api');
Route::get('/purchases/{purchase}/receipt', [AffanPayController::class, 'downloadReceipt'])->name('purchases.receipt');

/*
|--------------------------------------------------------------------------
| Enjin Bidaan Bertutup (Sealed Bidding) & Muat Turun Dokumen
|--------------------------------------------------------------------------
*/
Route::post('/vendor/bids/{tender}/submit', [BidController::class, 'submit'])->name('vendor.bids.submit');
Route::get('/vendor/bids/{bid}/slip', [BidController::class, 'downloadSlip'])->name('vendor.bids.slip');
Route::get('/documents/{document}/download', [BidController::class, 'downloadTenderDocument'])->name('documents.download');

/*
|--------------------------------------------------------------------------
| Pengesahan Saksi Digital & Laporan Rasmi Perolehan
|--------------------------------------------------------------------------
*/
Route::post('/penilai/witness/{tender}/affirm', [WitnessController::class, 'affirm'])->name('witness.affirm');
Route::get('/tenders/{tender}/opening-slip', [TenderReportController::class, 'openingSlip'])->name('tenders.opening-slip');
Route::get('/tenders/{tender}/evaluation-report', [TenderReportController::class, 'evaluationReport'])->name('tenders.evaluation-report');
Route::get('/tenders/{tender}/sst-letter', [TenderReportController::class, 'sstLetter'])->name('tenders.sst-letter');
