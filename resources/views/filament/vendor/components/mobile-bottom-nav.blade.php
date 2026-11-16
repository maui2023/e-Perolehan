@if(auth()->check())
@php
    $openTendersCount = \Illuminate\Support\Facades\Cache::remember('vendor_nav_open_tenders_count', 60, function () {
        return \App\Models\Tender::where('status', 'published')
            ->where('closing_at', '>', now())
            ->count();
    });
    $vendor = auth()->user()->vendor;
    $myBidsCount = $vendor ? \Illuminate\Support\Facades\Cache::remember("vendor_bids_count_{$vendor->id}", 60, fn () => $vendor->bids()->count()) : 0;
    $complianceStatus = $vendor?->compliance_status ?? 'pending';

    $isDashboardActive = request()->routeIs('filament.vendor.pages.dashboard') || request()->is('vendor');
    $isTendersActive = request()->routeIs('filament.vendor.resources.tenders.*') || request()->is('vendor/tenders*');
    $isBidsActive = request()->routeIs('filament.vendor.resources.bids.*') || request()->is('vendor/bids*');
    $isProfileActive = request()->routeIs('filament.vendor.auth.profile') || request()->is('vendor/profile*');
@endphp

<!-- Vendor Mobile Bottom Navigation Bar -->
<nav
    id="vendor-mobile-bottom-nav"
    class="vendor-mobile-bottom-nav"
    style="position: fixed !important; bottom: 0 !important; left: 0 !important; right: 0 !important; width: 100% !important; z-index: 999 !important; margin: 0 !important; box-sizing: border-box !important;"
    aria-label="Navigasi Bawah Mudah Alih Portal Vendor"
>
    <div class="vendor-nav-container">
        <!-- 1. TAB UTAMA -->
        <a
            href="{{ route('filament.vendor.pages.dashboard') }}"
            class="vendor-nav-item {{ $isDashboardActive ? 'is-active' : '' }}"
            aria-label="Laman Utama Vendor"
        >
            <div class="vendor-nav-icon-wrap">
                <svg
                    class="vendor-nav-icon"
                    width="22"
                    height="22"
                    fill="{{ $isDashboardActive ? 'currentColor' : 'none' }}"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    stroke-width="{{ $isDashboardActive ? '2' : '1.75' }}"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </div>
            <span class="vendor-nav-label">Utama</span>
            @if($isDashboardActive)
                <span class="vendor-nav-active-dot"></span>
            @endif
        </a>

        <!-- 2. TAB TENDER AKTIF -->
        <a
            href="{{ route('filament.vendor.resources.tenders.index') }}"
            class="vendor-nav-item {{ $isTendersActive ? 'is-active' : '' }}"
            aria-label="Senarai Tender Dibuka"
        >
            <div class="vendor-nav-icon-wrap">
                <svg
                    class="vendor-nav-icon"
                    width="22"
                    height="22"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    stroke-width="{{ $isTendersActive ? '2.25' : '1.75' }}"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                </svg>
                @if($openTendersCount > 0)
                    <span class="vendor-nav-badge" title="{{ $openTendersCount }} tender dibuka">
                        {{ $openTendersCount > 99 ? '99+' : $openTendersCount }}
                    </span>
                @endif
            </div>
            <span class="vendor-nav-label">Tender</span>
            @if($isTendersActive)
                <span class="vendor-nav-active-dot"></span>
            @endif
        </a>

        <!-- 3. TAB BIDAAN SAYA -->
        <a
            href="{{ route('filament.vendor.resources.bids.index') }}"
            class="vendor-nav-item {{ $isBidsActive ? 'is-active' : '' }}"
            aria-label="Penyertaan Bidaan Saya"
        >
            <div class="vendor-nav-icon-wrap">
                <svg
                    class="vendor-nav-icon"
                    width="22"
                    height="22"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    stroke-width="{{ $isBidsActive ? '2.25' : '1.75' }}"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                @if($myBidsCount > 0)
                    <span class="vendor-nav-badge vendor-nav-badge-amber" title="{{ $myBidsCount }} bidaan saya">
                        {{ $myBidsCount > 99 ? '99+' : $myBidsCount }}
                    </span>
                @endif
            </div>
            <span class="vendor-nav-label">Bidaan</span>
            @if($isBidsActive)
                <span class="vendor-nav-active-dot"></span>
            @endif
        </a>

        <!-- 4. TAB PROFIL SYARIKAT & SIJIL -->
        <a
            href="{{ route('filament.vendor.auth.profile') }}"
            class="vendor-nav-item {{ $isProfileActive ? 'is-active' : '' }}"
            aria-label="Profil Syarikat dan Pematuhan"
        >
            <div class="vendor-nav-icon-wrap">
                <svg
                    class="vendor-nav-icon"
                    width="22"
                    height="22"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    stroke-width="{{ $isProfileActive ? '2.25' : '1.75' }}"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <!-- Compliance Status Dot -->
                <span
                    class="vendor-compliance-indicator {{ $complianceStatus }}"
                    title="Status Pematuhan: {{ ucfirst($complianceStatus) }}"
                ></span>
            </div>
            <span class="vendor-nav-label">Profil</span>
            @if($isProfileActive)
                <span class="vendor-nav-active-dot"></span>
            @endif
        </a>
    </div>
</nav>
@endif
