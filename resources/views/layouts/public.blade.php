<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem e-Perolehan & Pengurusan Bidaan Universal') - {{ $setting->org_name ?? config('app.name') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite: Tailwind CSS v4 + Alpine.js -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-neutral-50 text-neutral-800 antialiased selection:bg-navy-800 selection:text-white"
      x-data="{ mobileOpen: false, infoOpen: false }">

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-neutral-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Brand / Logo -->
                <a href="{{ route('portal.home') }}" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-xl bg-linear-to-br from-navy-800 to-navy-950 flex items-center justify-center text-white shadow-md shadow-navy-900/10 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-navy-900 tracking-tight leading-none group-hover:text-navy-700 transition-colors">
                            <span class="md:hidden">{{ $setting->org_acronym ?? 'LPKU' }}</span>
                            <span class="hidden md:inline">{{ $setting->org_name ?? 'Lembaga Perolehan & Kontrak Universal' }}</span>
                        </div>
                        <div class="text-xs text-neutral-500 font-medium mt-1">
                            {{ $setting->org_short_name ?? 'Universal Procurement & Sealed Bidding' }}
                        </div>
                    </div>
                </a>

                <!-- Right Action Buttons (Desktop) -->
                <nav class="hidden md:flex items-center gap-3">
                    <!-- Info Popup Button -->
                    <div class="relative" x-data @click.away="infoOpen = false">
                        <button
                            @click="infoOpen = !infoOpen"
                            :aria-expanded="infoOpen"
                            aria-label="Maklumat Platform"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200/80 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Telus &amp; Diaudit
                            <svg class="w-3 h-3 text-emerald-500 transition-transform" :class="infoOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Info Dropdown Popup -->
                        <div
                            x-show="infoOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 top-full mt-2 w-80 bg-navy-950 text-neutral-300 rounded-xl shadow-2xl shadow-navy-950/40 border border-navy-800 z-50 overflow-hidden"
                            style="display:none;"
                        >
                            <!-- Popup Header -->
                            <div class="px-4 pt-4 pb-3 border-b border-navy-800 flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    SISTEM TELUS &amp; DIAUDIT
                                </span>
                            </div>
                            <!-- Popup Body -->
                            <div class="px-4 py-3 space-y-3 text-xs">
                                <p class="text-neutral-400 leading-relaxed">
                                    Platform Bidaan Bertutup (Sealed-Bidding) &amp; Pembukaan Peti Dua Saksi Digital
                                </p>
                                <div class="space-y-2 pt-1 border-t border-navy-800">
                                    <div class="flex items-center gap-2 text-neutral-400">
                                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ $setting->org_email ?? 'perolehan@organisasi.my' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-neutral-400">
                                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span>{{ $setting->org_phone ?? '+60 3-8888 0000' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 pt-1 text-[11px] text-emerald-400">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Sealed-Bidding &amp; Dual-Witness Protocol</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-neutral-200 mx-1"></div>
                    <a href="{{ route('portal.home') }}" class="px-3.5 py-2 text-sm font-semibold {{ request()->routeIs('portal.home') ? 'text-navy-800 border-b-2 border-navy-800' : 'text-neutral-600 hover:text-navy-900' }} transition-colors">
                        Senarai Tender
                    </a>

                    <a href="{{ route('portal.home') }}#award-board" class="px-3.5 py-2 text-sm font-semibold text-neutral-600 hover:text-navy-900 transition-colors">
                        Keputusan Anugerah
                    </a>

                    <div class="h-6 w-px bg-neutral-200 mx-1"></div>

                    <!-- Vendor Portal Link -->
                    <a href="/vendor" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-navy-800 bg-navy-50 hover:bg-navy-100 rounded-lg border border-navy-200/80 transition-colors">
                        <svg class="w-4 h-4 text-navy-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Portal Pembekal
                    </a>

                    <!-- Penilai Portal Link -->
                    <a href="/penilai" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-neutral-700 hover:text-navy-900 hover:bg-neutral-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Portal Penilai
                    </a>

                    <!-- Admin Portal Link -->
                    <a href="/admin" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-navy-900 hover:bg-navy-800 rounded-lg shadow-sm shadow-navy-900/20 transition-all hover:shadow-md">
                        <svg class="w-4 h-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Urus Setia / Admin
                    </a>
                </nav>

                <!-- Mobile Menu Button (Alpine.js) -->
                <div class="flex items-center gap-2 md:hidden">
                    <button
                        @click="mobileOpen = !mobileOpen"
                        class="p-2 rounded-lg text-neutral-600 hover:text-navy-900 hover:bg-neutral-100 transition-colors"
                        :aria-expanded="mobileOpen"
                        aria-label="Buka menu navigasi"
                    >
                        <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden border-t border-neutral-200 bg-white shadow-lg"
            style="display: none;"
            @click.away="mobileOpen = false"
        >
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('portal.home') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold text-navy-800 bg-navy-50" @click="mobileOpen = false">
                    Senarai Tender
                </a>
                <a href="{{ route('portal.home') }}#award-board" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-neutral-700 hover:bg-neutral-50" @click="mobileOpen = false">
                    Keputusan Anugerah
                </a>
                <div class="my-2 border-t border-neutral-100"></div>
                <a href="/vendor" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-navy-800 hover:bg-navy-50 border border-navy-100" @click="mobileOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Portal Pembekal
                </a>
                <a href="/penilai" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-neutral-700 hover:bg-neutral-50" @click="mobileOpen = false">
                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Portal Penilai
                </a>
                <a href="/admin" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold text-white bg-navy-900 hover:bg-navy-800" @click="mobileOpen = false">
                    <svg class="w-4 h-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Urus Setia / Admin
                </a>
            </div>
        </div>
    </header>

    <!-- Flash Messages / Toast Alerts -->
    @if(session('success') || session('error') || session('info') || session('warning'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-sm font-medium">{{ session('error') }}</div>
                </div>
            @endif
            @if(session('info'))
                <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-sm font-medium">{{ session('info') }}</div>
                </div>
            @endif
            @if(session('warning'))
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div class="text-sm font-medium">{{ session('warning') }}</div>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="grow">
        @yield('content')
    </main>

    <!-- Footer Area (Corporate, White-Label & Trust Standards) -->
    <footer class="bg-navy-950 text-neutral-400 text-sm mt-20 border-t border-navy-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <!-- Col 1: Org Info -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-navy-800 text-white flex items-center justify-center font-bold">
                            EP
                        </div>
                        <span class="text-lg font-bold text-white tracking-tight">
                            <span class="md:hidden">{{ $setting->org_acronym ?? 'LPKU' }}</span>
                            <span class="hidden md:inline">{{ $setting->org_name ?? 'Lembaga Perolehan & Kontrak Universal' }}</span>
                        </span>
                    </div>
                    <p class="text-xs text-neutral-400 leading-relaxed max-w-md">
                        {{ $setting->org_address ?? 'Kompleks Pentadbiran & Perolehan' }}
                    </p>
                    <div class="text-xs text-neutral-400 space-y-1">
                        <div>No. Pendaftaran: <span class="text-neutral-300 font-semibold">{{ $setting->org_reg_no ?? '-' }}</span></div>
                        <div>Emel Rasmi: <span class="text-neutral-300 font-semibold">{{ $setting->org_email ?? 'perolehan@organisasi.my' }}</span></div>
                        <div>Talian Bantuan: <span class="text-neutral-300 font-semibold">{{ $setting->org_phone ?? '-' }}</span></div>
                    </div>
                </div>

                <!-- Col 2: Akses Pantas Portal -->
                <div>
                    <h3 class="text-xs font-semibold text-white tracking-wider uppercase mb-4">Navigasi Portal</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('portal.home') }}" class="hover:text-white transition-colors">Senarai Tender Aktif</a></li>
                        <li><a href="{{ route('portal.home') }}#award-board" class="hover:text-white transition-colors">Papan Keputusan Anugerah</a></li>
                        <li><a href="/vendor" class="hover:text-white transition-colors">Pendaftaran & Portal Vendor</a></li>
                        <li><a href="/penilai" class="hover:text-white transition-colors">Portal Penilai & Saksi</a></li>
                        <li><a href="/admin" class="hover:text-white transition-colors">Panel Urus Setia & Pentadbir</a></li>
                    </ul>
                </div>

                <!-- Col 3: Keselamatan & Integriti -->
                <div>
                    <h3 class="text-xs font-semibold text-white tracking-wider uppercase mb-4">Pematuhan & Integriti</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed mb-3">
                        {{ Str::limit($setting->anti_bribery_policy_text ?? 'Polisi toleransi sifar terhadap sebarang suapan atau rasuah selaras dengan piawaian tadbir urus terbaik.', 150) }}
                    </p>
                    <div class="flex items-center gap-2 text-[11px] text-emerald-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span>Sealed-Bidding & Dual-Witness</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Line -->
            <div class="mt-12 pt-8 border-t border-navy-900 flex flex-col sm:flex-row justify-between items-center text-xs text-neutral-500 gap-4">
                <div>
                    &copy; {{ date('Y') }} {{ $setting->org_name ?? 'Universal Procurement Portal' }}. Hak cipta terpelihara.
                </div>
                <div class="flex items-center gap-6">
                    <span>{{ $setting->letterhead_footer_text ?? 'Sistem e-Perolehan Berintegriti Tinggi' }}</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
