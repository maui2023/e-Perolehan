@extends('layouts.public')

@section('title', $tender->reference_no . ' - ' . $tender->title)

@section('content')

<div class="bg-navy-950 text-white py-10 border-b border-navy-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-neutral-400 mb-4">
            <a href="{{ route('portal.home') }}" class="hover:text-white transition-colors">Utama</a>
            <span>/</span>
            <a href="{{ route('portal.home', ['category' => $tender->category]) }}" class="hover:text-white uppercase transition-colors">{{ $tender->category }}</a>
            <span>/</span>
            <span class="text-neutral-300 font-mono">{{ $tender->reference_no }}</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="max-w-4xl">
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-navy-800 text-navy-200 border border-navy-700 uppercase tracking-wider">
                        {{ strtoupper($tender->category) }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/10 text-white uppercase tracking-wider">
                        {{ strtoupper($tender->procurement_type ?? 'TENDER TERBUKA') }}
                    </span>

                    @if($tender->isClosed())
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-neutral-800 text-neutral-300 border border-neutral-700">
                            PETI BIDAAN DITUTUP
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            PENYERAHAN DIBUKA
                        </span>
                    @endif
                </div>

                <div class="text-xs font-mono font-bold text-navy-300 tracking-wider">
                    NO. RUJUKAN: {{ $tender->reference_no }}
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1 leading-tight">
                    {{ $tender->title }}
                </h1>
            </div>

            <!-- Notice PDF Button -->
            <div class="shrink-0">
                <a 
                    href="{{ route('tenders.notice-pdf', $tender->id) }}" 
                    target="_blank" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 transition-all shadow-sm"
                >
                    <svg class="w-4 h-4 text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                    <span>Muat Turun Iklan (PDF)</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Details & Specifications -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Box 1: Deskripsi & Skop Kerja -->
            <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-navy-950 flex items-center gap-2 pb-4 border-b border-neutral-100">
                    <svg class="w-5 h-5 text-navy-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Skop Kerja & Maklumat Perolehan</span>
                </h2>
                <div class="mt-4 text-sm text-neutral-700 leading-relaxed space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Keterangan Projek:</div>
                        <p class="whitespace-pre-line">{{ $tender->description ?? 'Tiada maklumat tambahan diperincikan.' }}</p>
                    </div>

                    @if($tender->scope_of_work)
                    <div>
                        <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Perincian Skop & Penghantaran:</div>
                        <p class="whitespace-pre-line">{{ $tender->scope_of_work }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Box 2: Dokumen Tender Rasmi & Jadual Spesifikasi -->
            @if($tender->documents && $tender->documents->count() > 0)
            <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-navy-950 flex items-center justify-between pb-4 border-b border-neutral-100">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-navy-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Dokumen Tender Rasmi & Spesifikasi</span>
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-navy-50 text-navy-800 border border-navy-200">
                        {{ $tender->documents->count() }} Dokumen
                    </span>
                </h2>
                <div class="mt-4 divide-y divide-neutral-100">
                    @php
                        $userVendor = auth()->user()?->vendor;
                        $hasPurchased = $userVendor && $userVendor->hasPurchasedTender($tender->id);
                        $isFreeTender = (float) $tender->document_price <= 0;
                    @endphp
                    @foreach($tender->documents as $doc)
                        @php
                            $canDownload = $doc->is_public || $isFreeTender || $hasPurchased;
                        @endphp
                        <div class="py-3.5 flex items-center justify-between gap-4">
                            <div class="min-w-0 flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-neutral-100 flex items-center justify-center shrink-0 mt-0.5 text-neutral-600">
                                    <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                                </div>
                                <div class="truncate">
                                    <div class="text-sm font-bold text-navy-950 truncate">{{ $doc->title }}</div>
                                    <div class="text-xs text-neutral-500 flex items-center gap-2 mt-0.5">
                                        <span>{{ $doc->file_name }}</span>
                                        <span>&bull;</span>
                                        <span>{{ round($doc->file_size / 1024, 1) }} KB</span>
                                        @if($doc->is_public)
                                            <span class="text-emerald-600 font-semibold">(Akses Awam)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0">
                                @if($canDownload)
                                    <a 
                                        href="{{ route('documents.download', $doc->id) }}" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-navy-900 hover:bg-navy-800 transition-colors shadow-sm"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Muat Turun</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        <span>Perlu Beli</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Box 3: Syarat-syarat Kelayakan -->
            <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-navy-950 flex items-center gap-2 pb-4 border-b border-neutral-100">
                    <svg class="w-5 h-5 text-navy-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Syarat & Kriteria Kelayakan Pembida</span>
                </h2>
                <div class="mt-4 text-sm text-neutral-700 leading-relaxed">
                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                        <p class="whitespace-pre-line">{{ $tender->eligibility_criteria ?? 'Pembekal berdaftar SSM yang mempunyai kod bidang berkaitan.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Box 3: Polisi Integriti & Bidaan Bertutup -->
            <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-navy-950 flex items-center gap-2 pb-4 border-b border-neutral-100">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>Prosedur Integriti Bidaan Bertutup & Penilaian Bebas</span>
                </h2>
                <div class="mt-4 text-xs text-neutral-600 leading-relaxed space-y-3">
                    <p>
                        1. <strong>Penyulitan Bidaan (Sealed):</strong> Cadangan harga kewangan anda akan disulitkan sebaik dimuat naik dan disimpan di luar akses capaian awam.
                    </p>
                    <p>
                        2. <strong>Protokol Dua Saksi:</strong> Peti tender hanya dibuka secara digital selepas tarikh tutup bidaan dengan pengesahan serentak sekurang-kurangnya dua pegawai saksi bertauliah.
                    </p>
                    <p>
                        3. <strong>Penilaian Double-Blind:</strong> Penilai teknikal tidak dapat melihat nama syarikat atau tawaran harga anda. Markah lulus minimum teknikal ditetapkan pada <strong>{{ $tender->min_technical_score }}%</strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Action Card & Key Timelines -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-sm sticky top-24">
                <h3 class="text-base font-bold text-navy-950 pb-3 border-b border-neutral-100">
                    Tindakan & Pembelian Dokumen
                </h3>

                <div class="mt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-neutral-500">Harga Dokumen:</span>
                        <span class="text-xl font-extrabold text-navy-900">
                            @if($tender->document_price > 0)
                                RM {{ number_format($tender->document_price, 2) }}
                            @else
                                <span class="text-emerald-600">PERCUMA</span>
                            @endif
                        </span>
                    </div>

                    <div class="text-xs text-neutral-500">
                        Pembayaran dokumen tender diuruskan secara selamat menggunakan gerbang pembayaran <strong>AffanPay</strong>.
                    </div>

                    <div class="pt-2 border-t border-neutral-100 space-y-2">
                        <div class="text-xs text-neutral-500">Tarikh Tutup Bidaan:</div>
                        <div class="text-sm font-bold text-rose-700 bg-rose-50 p-2.5 rounded-lg border border-rose-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $tender->closing_at ? $tender->closing_at->format('d M Y, h:i A') : 'Tertakluk Pengumuman' }}</span>
                        </div>
                    </div>

                    @if(! $tender->isClosed())
                        @php
                            $user = auth()->user();
                            $vendor = $user?->vendor;
                            $hasPurchased = $vendor && $vendor->hasPurchasedTender($tender->id);
                            $canBid = $vendor && ($hasPurchased || $tender->document_price <= 0);
                        @endphp

                        @if($canBid)
                            <div class="pt-4">
                                <a 
                                    href="{{ route('filament.vendor.resources.bids.create', ['tender_id' => $tender->id]) }}" 
                                    class="w-full py-3 px-4 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Telah Dibeli &mdash; Masuk Bidaan</span>
                                </a>
                            </div>
                        @else
                            <!-- Buy Document / Submit CTA Form -->
                            <form action="{{ route('tenders.purchase', $tender->id) }}" method="POST" class="pt-4">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="w-full py-3 px-4 rounded-xl font-bold text-sm text-white bg-navy-900 hover:bg-navy-800 shadow-md shadow-navy-900/20 transition-all flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span>Beli Dokumen & Bida</span>
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="mt-4 p-3 rounded-xl bg-neutral-100 text-center text-xs font-semibold text-neutral-600 border border-neutral-200">
                            Peti bidaan bagi tender ini telah ditutup.
                        </div>
                    @endif

                    <div class="pt-4 text-center">
                        <a href="/vendor" class="text-xs font-semibold text-navy-800 hover:underline">
                            Sudah ada akaun vendor? Log masuk ke Portal Vendor &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
