@extends('layouts.public')

@section('title', 'Portal Awam & Ketelusan Perolehan')

@section('content')

<!-- Hero Section -->
<section class="relative bg-gradient-to-b from-navy-950 via-navy-900 to-navy-950 text-white overflow-hidden py-16 md:py-24 border-b border-navy-800">
    <!-- Subtle Background Glow Grid -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-navy-800/80 border border-navy-700/60 text-navy-200 text-xs font-semibold mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Peti Tender Terkunci Digital & Matriks Penilaian Bebas</span>
            </div>

            <!-- Main Heading -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-white leading-tight">
                Ketelusan, Integriti & Bidaan Adil Tanpa Kompromi
            </h1>

            <p class="mt-4 text-base sm:text-lg text-neutral-300 font-normal leading-relaxed">
                Platform e-Perolehan rasmi <strong class="text-white"><span class="md:hidden">{{ $setting->org_acronym ?? 'LPKU' }}</span><span class="hidden md:inline">{{ $setting->org_name }}</span></strong>. Terbuka kepada semua pembekal yang berkelayakan dengan sistem penyulitan bidaan bertutup (*sealed-bidding*) dan prosedur pembukaan dua saksi berintegriti tinggi.
            </p>

            <!-- Search Form Bar -->
            <form action="{{ route('portal.home') }}" method="GET" class="mt-8 flex flex-col sm:flex-row gap-2 bg-white/10 p-2 rounded-2xl backdrop-blur-md border border-white/15 shadow-xl shadow-navy-950/40">
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
                <div class="relative grow">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $searchTerm }}" 
                        placeholder="Cari nombor tender, tajuk perolehan, atau kata kunci..." 
                        class="w-full pl-11 pr-4 py-3 bg-white/90 focus:bg-white text-navy-950 placeholder-neutral-500 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-navy-500 transition-all shadow-inner"
                    >
                </div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-navy-600 hover:bg-navy-500 text-white font-semibold text-sm shadow-md shadow-navy-700/30 transition-all flex items-center justify-center gap-2">
                    <span>Cari Tender</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>
        </div>

        <!-- Live Statistics Counter Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-12 pt-10 border-t border-navy-800/80">
            <!-- Stat 1 -->
            <div class="bg-navy-900/60 border border-navy-800 rounded-xl p-4">
                <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Tender Aktif</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-white mt-1">{{ number_format($stats['active_tenders']) }}</div>
                <div class="text-[11px] text-emerald-400 font-medium mt-1">Dibuka untuk bidaan</div>
            </div>

            <!-- Stat 2 -->
            <div class="bg-navy-900/60 border border-navy-800 rounded-xl p-4">
                <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Bidaan Dinilai</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-white mt-1">{{ number_format($stats['evaluated_bids']) }}</div>
                <div class="text-[11px] text-neutral-400 font-medium mt-1">Penilaian dwipaksi (blind)</div>
            </div>

            <!-- Stat 3 -->
            <div class="bg-navy-900/60 border border-navy-800 rounded-xl p-4">
                <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nilai Anugerah Telus</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-white mt-1">RM {{ number_format($stats['awarded_value'] / 1000, 1) }}k</div>
                <div class="text-[11px] text-neutral-400 font-medium mt-1">Dipaparkan kepada awam</div>
            </div>

            <!-- Stat 4 -->
            <div class="bg-navy-900/60 border border-navy-800 rounded-xl p-4">
                <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Pembekal Sah</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-white mt-1">{{ number_format($stats['registered_vendors']) }}</div>
                <div class="text-[11px] text-emerald-400 font-medium mt-1">Pematuhan SSM/MOF</div>
            </div>
        </div>
    </div>
</section>

<!-- Main Tender Listing Section with Category Filters -->
<section id="tenders" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- Section Header & Category Filter Tabs -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-neutral-200">
        <div>
            <h2 class="text-2xl font-bold text-navy-900 tracking-tight">Senarai Iklan Tender Semasa</h2>
            <p class="text-sm text-neutral-500 mt-1">Pilih kategori perolehan untuk menapis tawaran yang berkaitan dengan bidang syarikat anda.</p>
        </div>

        @if(filled($searchTerm) || $selectedCategory !== 'all')
            <div>
                <a href="{{ route('portal.home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Padam Penapis Carian
                </a>
            </div>
        @endif
    </div>

    <!-- Interactive Category Filter Pill Buttons -->
    <div class="flex flex-wrap items-center gap-2 mt-6 overflow-x-auto pb-2">
        <!-- Semua -->
        <a 
            href="{{ route('portal.home', ['category' => 'all', 'q' => $searchTerm]) }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $selectedCategory === 'all' ? 'bg-navy-900 text-white shadow-md shadow-navy-900/20' : 'bg-white text-neutral-600 hover:text-navy-900 hover:bg-neutral-100 border border-neutral-200' }}"
        >
            <span>Semua Kategori</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $selectedCategory === 'all' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600' }}">
                {{ $categoryCounts['all'] }}
            </span>
        </a>

        <!-- Bekalan -->
        <a 
            href="{{ route('portal.home', ['category' => 'supply', 'q' => $searchTerm]) }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $selectedCategory === 'supply' ? 'bg-navy-900 text-white shadow-md shadow-navy-900/20' : 'bg-white text-neutral-600 hover:text-navy-900 hover:bg-neutral-100 border border-neutral-200' }}"
        >
            <svg class="w-4 h-4 {{ $selectedCategory === 'supply' ? 'text-white' : 'text-neutral-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <span>Bekalan</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $selectedCategory === 'supply' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600' }}">
                {{ $categoryCounts['supply'] }}
            </span>
        </a>

        <!-- Perkhidmatan -->
        <a 
            href="{{ route('portal.home', ['category' => 'service', 'q' => $searchTerm]) }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $selectedCategory === 'service' ? 'bg-navy-900 text-white shadow-md shadow-navy-900/20' : 'bg-white text-neutral-600 hover:text-navy-900 hover:bg-neutral-100 border border-neutral-200' }}"
        >
            <svg class="w-4 h-4 {{ $selectedCategory === 'service' ? 'text-white' : 'text-neutral-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            <span>Perkhidmatan</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $selectedCategory === 'service' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600' }}">
                {{ $categoryCounts['service'] }}
            </span>
        </a>

        <!-- Kerja / Pembinaan -->
        <a 
            href="{{ route('portal.home', ['category' => 'work', 'q' => $searchTerm]) }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $selectedCategory === 'work' ? 'bg-navy-900 text-white shadow-md shadow-navy-900/20' : 'bg-white text-neutral-600 hover:text-navy-900 hover:bg-neutral-100 border border-neutral-200' }}"
        >
            <svg class="w-4 h-4 {{ $selectedCategory === 'work' ? 'text-white' : 'text-neutral-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            <span>Kerja / Pembinaan</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $selectedCategory === 'work' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600' }}">
                {{ $categoryCounts['work'] }}
            </span>
        </a>

        <!-- Perunding -->
        <a 
            href="{{ route('portal.home', ['category' => 'consultancy', 'q' => $searchTerm]) }}" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $selectedCategory === 'consultancy' ? 'bg-navy-900 text-white shadow-md shadow-navy-900/20' : 'bg-white text-neutral-600 hover:text-navy-900 hover:bg-neutral-100 border border-neutral-200' }}"
        >
            <svg class="w-4 h-4 {{ $selectedCategory === 'consultancy' ? 'text-white' : 'text-neutral-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Perkhidmatan Perunding</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $selectedCategory === 'consultancy' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-600' }}">
                {{ $categoryCounts['consultancy'] }}
            </span>
        </a>
    </div>

    <!-- Active Tenders Grid -->
    @if($tenders->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            @foreach($tenders as $tender)
                @php
                    $isClosingSoon = $tender->closing_at && $tender->closing_at->isFuture() && $tender->closing_at->diffInDays(now()) <= 3;
                    $isClosed = $tender->isClosed();
                @endphp
                <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between group">
                    <div>
                        <!-- Card Top Bar: Category badge & Status -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 uppercase tracking-wider">
                                {{ strtoupper($tender->category) }}
                            </span>

                            @if($isClosed)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-600 border border-neutral-300">
                                    TUTUP
                                </span>
                            @elseif($isClosingSoon)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                    HAMPIR TUTUP
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    BUKA
                                </span>
                            @endif
                        </div>

                        <!-- Reference No -->
                        <div class="text-xs font-mono font-bold text-navy-800 tracking-wider">
                            {{ $tender->reference_no }}
                        </div>

                        <!-- Tender Title -->
                        <h3 class="text-base font-bold text-navy-950 mt-1 line-clamp-2 group-hover:text-navy-700 transition-colors">
                            <a href="{{ route('tenders.public-show', $tender->slug) }}">
                                {{ $tender->title }}
                            </a>
                        </h3>

                        <!-- Scope Summary -->
                        <p class="text-xs text-neutral-500 mt-2 line-clamp-3 leading-relaxed">
                            {{ $tender->description ?? $tender->scope_of_work ?? 'Rujuk kenyataan notis iklan untuk skop perolehan penuh.' }}
                        </p>

                        <!-- Eligibility Tags / Badges -->
                        <div class="flex flex-wrap items-center gap-1.5 mt-4">
                            @if(str_contains(strtolower($tender->eligibility_criteria ?? ''), 'mof'))
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">MOF</span>
                            @endif
                            @if(str_contains(strtolower($tender->eligibility_criteria ?? ''), 'cidb'))
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-orange-50 text-orange-700 border border-orange-200">CIDB</span>
                            @endif
                            @if(str_contains(strtolower($tender->eligibility_criteria ?? ''), 'bumiputera'))
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">Bumiputera</span>
                            @endif
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-neutral-100 text-neutral-600 border border-neutral-200">SSM Sah</span>
                        </div>
                    </div>

                    <!-- Card Bottom Details -->
                    <div class="mt-6 pt-4 border-t border-neutral-100 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-neutral-500">Harga Dokumen:</span>
                            <span class="font-bold text-navy-900">
                                @if($tender->document_price > 0)
                                    RM {{ number_format($tender->document_price, 2) }}
                                @else
                                    <span class="text-emerald-600">PERCUMA</span>
                                @endif
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-neutral-500">Tarikh Tutup:</span>
                            <span class="font-semibold {{ $isClosingSoon ? 'text-amber-700 font-bold' : 'text-neutral-800' }}">
                                {{ $tender->closing_at ? $tender->closing_at->format('d/m/Y (h:i A)') : 'Segera' }}
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2 pt-2">
                            <a 
                                href="{{ route('tenders.public-show', $tender->slug) }}" 
                                class="px-3 py-2 text-center text-xs font-semibold text-navy-800 bg-neutral-100 hover:bg-neutral-200 rounded-lg transition-colors"
                            >
                                Perincian
                            </a>

                            <a 
                                href="{{ route('tenders.public-show', $tender->slug) }}" 
                                class="px-3 py-2 text-center text-xs font-semibold text-white bg-navy-900 hover:bg-navy-800 rounded-lg shadow-sm transition-colors"
                            >
                                Beli & Bida
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $tenders->links() }}
        </div>
    @else
        <div class="mt-12 text-center py-16 bg-white rounded-2xl border border-neutral-200 p-8 shadow-sm">
            <div class="w-16 h-16 mx-auto rounded-full bg-neutral-100 text-neutral-400 flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-navy-900">Tiada Tender Ditemui</h3>
            <p class="text-sm text-neutral-500 mt-1 max-w-sm mx-auto">
                Tiada iklan tender aktif bagi kategori atau kata kunci yang dipilih buat masa ini.
            </p>
            <div class="mt-6">
                <a href="{{ route('portal.home') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-navy-900 text-white hover:bg-navy-800 transition-colors">
                    Lihat Semua Tender
                </a>
            </div>
        </div>
    @endif
</section>

<!-- Public Award Board (Papan Pengumuman Keputusan Rasmi) -->
<section id="award-board" class="bg-neutral-100 border-t border-b border-neutral-200 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-navy-900 text-white mb-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    PAPAN KETELUSAN KEPUTUSAN AWAM
                </div>
                <h2 class="text-2xl font-bold text-navy-900 tracking-tight">Pengumuman Keputusan Award Kontrak</h2>
                <p class="text-sm text-neutral-600 mt-1">Pendedahan terbuka tawaran yang telah disahkan dan dianugerahkan oleh Lembaga Perolehan.</p>
            </div>
        </div>

        @if($awardedTenders->count() > 0)
            <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-neutral-700">
                        <thead class="bg-navy-950 text-white text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-4">No. Tender / Perihal</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Syarikat Pemenang (Awardee)</th>
                                <th class="px-6 py-4 text-right">Nilai Kontrak</th>
                                <th class="px-6 py-4 text-center">Tarikh Award</th>
                                <th class="px-6 py-4 text-center">Integriti / SST</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach($awardedTenders as $tender)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-xs font-bold text-navy-800">{{ $tender->reference_no }}</div>
                                        <div class="font-semibold text-navy-950 mt-0.5">{{ $tender->title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-700 border border-neutral-200">
                                            {{ strtoupper($tender->category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($tender->awardedBid && $tender->awardedBid->vendor)
                                            <div class="font-bold text-navy-900">{{ $tender->awardedBid->vendor->company_name }}</div>
                                            <div class="text-xs text-neutral-500 font-mono">{{ $tender->awardedBid->vendor->registration_number }}</div>
                                        @else
                                            <span class="text-neutral-400 italic">Maklumat dimuktamadkan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap font-bold text-navy-950">
                                        @if($tender->awardedBid && $tender->awardedBid->decrypted_bid_amount)
                                            RM {{ number_format($tender->awardedBid->decrypted_bid_amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-xs text-neutral-600">
                                        {{ $tender->awarded_at ? $tender->awarded_at->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <a 
                                            href="{{ route('verify.document', ['type' => 'sst', 'ref' => $tender->reference_no]) }}" 
                                            class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                            <span>Sahkan SST</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="text-center py-10 bg-white rounded-2xl border border-neutral-200 text-neutral-500 text-sm">
                Belum ada pengumuman keputusan anugerah tender yang dimuktamadkan.
            </div>
        @endif
    </div>
</section>

<!-- Trust & Governance Architecture Highlights -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <h2 class="text-xs font-bold text-navy-800 uppercase tracking-widest">Prinsip Seni Bina Keselamatan</h2>
        <h3 class="text-2xl sm:text-3xl font-extrabold text-navy-950 mt-1">Direka Khusus Untuk Keadilan & Bebas Manipulasi</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Feature 1 -->
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-navy-50 text-navy-800 flex items-center justify-center font-bold mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h4 class="text-base font-bold text-navy-900">Bidaan Bertutup (Sealed)</h4>
            <p class="text-xs text-neutral-600 mt-2 leading-relaxed">
                Dokumen kewangan dan nilai harga disulitkan secara kriptografi (*AES-256*) dan tidak boleh dilihat sebelum masa buka rasmi.
            </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-navy-50 text-navy-800 flex items-center justify-center font-bold mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h4 class="text-base font-bold text-navy-900">Kunci Dua Saksi (Dual-Witness)</h4>
            <p class="text-xs text-neutral-600 mt-2 leading-relaxed">
                Peti tender hanya boleh dinyahsulitkan apabila sekurang-kurangnya dua (2) pegawai saksi digital yang sah hadir serentak.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-navy-50 text-navy-800 flex items-center justify-center font-bold mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <h4 class="text-base font-bold text-navy-900">Penilaian Double-Blind</h4>
            <p class="text-xs text-neutral-600 mt-2 leading-relaxed">
                Penilai teknikal hanya melihat kod rawak (*PEMBIDA-A*) tanpa harga atau nama syarikat. Penilaian kewangan hanya untuk yang lulus teknikal.
            </p>
        </div>

        <!-- Feature 4 -->
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-navy-50 text-navy-800 flex items-center justify-center font-bold mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h4 class="text-base font-bold text-navy-900">Arkib MinIO Hibrid</h4>
            <p class="text-xs text-neutral-600 mt-2 leading-relaxed">
                Dokumen tender selesai dipindahkan secara automatik ke kontena MinIO Cold Storage beserta semakan checksum SHA-256.
            </p>
        </div>
    </div>
</section>

@endsection
