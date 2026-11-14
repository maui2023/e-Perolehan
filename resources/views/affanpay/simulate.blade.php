<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AffanPay Sandbox Gateway Simulator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-800 rounded-3xl border border-slate-700 shadow-2xl overflow-hidden">
        <!-- Gateway Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>AffanPay Sandbox Simulator</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight">Gerbang Pembayaran</h1>
            <p class="text-xs text-blue-100 mt-1">Mod Simulasi Ujian Tempatan & Sandbox</p>
        </div>

        <!-- Bill Details -->
        <div class="p-6 space-y-6">
            <div class="bg-slate-900/80 rounded-2xl p-4 border border-slate-700/60 space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">Rujukan Bil:</span>
                    <span class="font-mono font-bold text-blue-400">{{ $purchase->purchase_reference }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">Tender:</span>
                    <span class="font-semibold text-right text-slate-200 truncate max-w-[200px]">[{{ $purchase->tender->reference_no }}] {{ $purchase->tender->title }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">Pembekal / Vendor:</span>
                    <span class="font-semibold text-slate-200">{{ $purchase->vendor->company_name }}</span>
                </div>
                <div class="pt-3 border-t border-slate-700 flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-300">Jumlah Bayaran:</span>
                    <span class="text-2xl font-extrabold text-emerald-400">RM {{ number_format($purchase->amount, 2) }}</span>
                </div>
            </div>

            <!-- Simulation Controls -->
            <form action="{{ route('affanpay.simulate.post', $purchase->purchase_reference) }}" method="POST" class="space-y-3">
                @csrf
                <button 
                    type="submit" 
                    name="action" 
                    value="success"
                    class="w-full py-3.5 px-4 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 shadow-lg shadow-emerald-900/40 transition-all flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Sahkan Bayaran (Simulasi Berjaya)</span>
                </button>

                <button 
                    type="submit" 
                    name="action" 
                    value="failed"
                    class="w-full py-2.5 px-4 rounded-xl font-semibold text-xs text-rose-300 bg-rose-950/40 hover:bg-rose-950/70 border border-rose-800/60 transition-all flex items-center justify-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>Batalkan Bayaran (Simulasi Gagal)</span>
                </button>
            </form>

            <div class="text-center">
                <a href="{{ route('portal.home') }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                    &larr; Kembali ke Laman Utama Portal
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-900 px-6 py-3 text-center text-[10px] text-slate-400 border-t border-slate-700/60">
            Dikuasakan oleh pakej integrasi <code class="text-blue-400">maui2023/affanpay-laravel</code>
        </div>
    </div>

</body>
</html>
