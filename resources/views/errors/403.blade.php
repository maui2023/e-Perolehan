@php
    use App\Models\OrganizationSetting;

    $setting = rescue(fn () => OrganizationSetting::current(), null, false);
    $orgName = $setting->org_name ?? 'Lembaga Perolehan & Kontrak Universal';
    $orgShortName = $setting->org_short_name ?? 'e-Perolehan Universal';
    $orgAcronym = $setting->org_acronym ?? 'LPKU';
    $orgEmail = $setting->org_email ?? 'perolehan@organisasi.my';
    $orgPhone = $setting->org_phone ?? '+60 3-8888 0000';

    $user = auth()->user();
    $isAuthenticated = auth()->check() && $user !== null;
    $userRole = $user?->role;

    // Detect user level / role and map appropriate destination
    if ($isAuthenticated) {
        $roleTitle = match ($userRole) {
            'admin' => 'Pentadbir Sistem (Admin)',
            'urus_setia' => 'Urus Setia Perolehan & Kontrak',
            'penilai_teknikal' => 'Jawatankuasa Penilai Teknikal',
            'penilai_kewangan' => 'Jawatankuasa Penilai Kewangan',
            'saksi_pembuka' => 'Saksi Digital Pembuka Peti',
            'vendor' => 'Pembekal / Kontraktor Berdaftar',
            default => 'Pengguna Berdaftar (' . ucwords(str_replace('_', ' ', (string) $userRole)) . ')',
        };

        $roleBadgeClasses = match ($userRole) {
            'admin', 'urus_setia' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'penilai_teknikal', 'penilai_kewangan', 'saksi_pembuka' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
            'vendor' => 'bg-success-subtle text-success border border-success-subtle',
            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };

        $targetUrl = match ($userRole) {
            'admin', 'urus_setia' => url('/admin'),
            'penilai_teknikal', 'penilai_kewangan', 'saksi_pembuka' => url('/penilai'),
            'vendor' => url('/vendor'),
            default => url('/'),
        };

        $targetLabel = match ($userRole) {
            'admin', 'urus_setia' => 'Dashboard Pentadbir',
            'penilai_teknikal', 'penilai_kewangan', 'saksi_pembuka' => 'Dashboard Penilai',
            'vendor' => 'Dashboard Pembekal',
            default => 'Laman Utama Portal',
        };

        $logoutRoute = match (true) {
            in_array($userRole, ['admin', 'urus_setia']) => route('filament.admin.auth.logout'),
            in_array($userRole, ['penilai_teknikal', 'penilai_kewangan', 'saksi_pembuka']) => route('filament.penilai.auth.logout'),
            default => route('filament.vendor.auth.logout'),
        };
    } else {
        $roleTitle = 'Pelawat (Belum Log Masuk)';
        $roleBadgeClasses = 'bg-secondary-subtle text-secondary border border-secondary-subtle';

        $currentPath = request()->path();
        $targetUrl = match (true) {
            str_starts_with($currentPath, 'admin') => url('/admin/login'),
            str_starts_with($currentPath, 'penilai') => url('/penilai/login'),
            default => url('/vendor/login'),
        };

        $targetLabel = match (true) {
            str_starts_with($currentPath, 'admin') => 'Laman Log Masuk Pentadbir',
            str_starts_with($currentPath, 'penilai') => 'Laman Log Masuk Penilai',
            default => 'Laman Log Masuk Pembekal',
        };

        $logoutRoute = null;
    }

    $attemptedPath = '/' . ltrim(request()->path(), '/');
    $exceptionMessage = isset($exception) ? trim((string) $exception->getMessage()) : '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>403 - Akses Ditolak | {{ $orgShortName }} - {{ $orgName }}</title>

    <!-- Auto Redirect: 3 saat ke pages login atau terus ke dashboard mengikut tahap capaian -->
    <meta http-equiv="refresh" content="3;url={{ $targetUrl }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --ep-navy: #0f2744;
            --ep-navy-dark: #0a192f;
            --ep-blue-primary: #1e3a8a;
            --ep-blue-hover: #1e40af;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand-badge {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1e3a8a, #0a192f);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.15);
            flex-shrink: 0;
        }
        .btn-ep-primary {
            background-color: var(--ep-blue-primary);
            border-color: var(--ep-blue-primary);
            color: #ffffff;
            font-weight: 600;
        }
        .btn-ep-primary:hover, .btn-ep-primary:focus {
            background-color: var(--ep-blue-hover);
            border-color: var(--ep-blue-hover);
            color: #ffffff;
        }
        .card-custom {
            border: 1px solid #e2e8f0;
            border-top: 4px solid #dc3545;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
        }
        .icon-circle-danger {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background-color: #fee2e2;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: var(--ep-navy); border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <div class="container py-1">
                <a class="navbar-brand d-flex align-items-center gap-3 py-1 text-decoration-none" href="{{ route('portal.home') }}">
                    <div class="navbar-brand-badge">
                        <svg style="width: 20px; height: 20px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="fw-bold text-white lh-1 fs-6 d-flex align-items-center gap-1">
                            <span>{{ $orgAcronym }}</span>
                            <span class="text-white-50 d-none d-sm-inline">•</span>
                            <span class="text-white-50 fw-normal small d-none d-sm-inline">{{ $orgShortName }}</span>
                        </div>
                        <div class="text-white-50 small" style="font-size: 0.725rem;">
                            Sistem Kawalan Keselamatan Perolehan (RBAC)
                        </div>
                    </div>
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow-1 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8 col-xl-7">

                    <!-- Main Error Card -->
                    <div class="card card-custom rounded-4 bg-white">
                        <div class="card-body p-4 p-sm-5">

                            <!-- Status Hero -->
                            <div class="text-center mb-4">
                                <div class="icon-circle-danger mb-3 shadow-sm">
                                    <i class="bi bi-shield-lock-fill fs-1"></i>
                                </div>
                                <h1 class="h3 fw-bold text-dark mb-1">
                                    403 — Akses Ditolak
                                </h1>
                                <p class="text-muted small mb-0">
                                    Capaian ke modul atau sumber ini dihadkan oleh dasar keselamatan sistem.
                                </p>
                            </div>

                            <!-- Auto-Redirect Countdown Notice -->
                            <div class="alert alert-primary bg-primary-subtle border border-primary-subtle text-primary-emphasis rounded-3 mb-4 p-3 shadow-sm" role="status">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="spinner-border spinner-border-sm text-primary flex-shrink-0" role="status">
                                            <span class="visually-hidden">Memuatkan...</span>
                                        </div>
                                        <div class="small">
                                            <div class="fw-bold mb-1">Peralihan Automatik (3 Saat)</div>
                                            <div>
                                                @if ($isAuthenticated)
                                                    Mengalihkan secara automatik terus ke <strong>{{ $targetLabel }}</strong> dalam
                                                @else
                                                    Mengalihkan secara automatik ke <strong>{{ $targetLabel }}</strong> dalam
                                                @endif
                                                <span id="countdown-seconds" class="badge bg-primary text-white fs-6 px-2 py-0.5 mx-1 fw-bold">3</span> saat...
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ $targetUrl }}" class="btn btn-sm btn-primary py-1 px-3 fw-semibold text-nowrap">
                                        Beralih Sekarang
                                    </a>
                                </div>
                            </div>

                            <!-- Exception / Alert Notification -->
                            @if (!empty($exceptionMessage))
                                <div class="alert alert-danger d-flex align-items-start gap-3 rounded-3 mb-4" role="alert">
                                    <i class="bi bi-exclamation-octagon-fill fs-5 mt-1 flex-shrink-0"></i>
                                    <div>
                                        <div class="fw-bold">Maklumat Penolakan:</div>
                                        <div class="small">{{ $exceptionMessage }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning d-flex align-items-start gap-3 rounded-3 mb-4" role="alert">
                                    <i class="bi bi-shield-exclamation fs-5 mt-1 text-warning-emphasis flex-shrink-0"></i>
                                    <div class="small">
                                        Laluan ini memerlukan kebenaran peranan tertentu. Bagi memelihara integriti bidaan bertutup dan ketelusan tatakelola perolehan, akses rentas peranan disekat mengikut prinsip pengasingan tugas (Separation of Duties).
                                    </div>
                                </div>
                            @endif

                            <!-- Context & Session Information Table -->
                            <div class="card bg-light border-0 rounded-3 mb-4">
                                <div class="card-body p-3 p-sm-4">
                                    <div class="text-uppercase text-secondary fw-bold small mb-3 d-flex align-items-center gap-1" style="letter-spacing: 0.05em; font-size: 0.75rem;">
                                        <i class="bi bi-info-circle-fill"></i> Ringkasan Sesi &amp; Capaian
                                    </div>

                                    <table class="table table-sm table-borderless mb-0 small">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="text-muted fw-normal py-2" style="width: 35%;">Laluan Diminta</th>
                                                <td class="py-2">
                                                    <code class="text-danger fw-semibold bg-white px-2 py-1 rounded border">{{ $attemptedPath }}</code>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="text-muted fw-normal py-2">Pengguna Semasa</th>
                                                <td class="py-2">
                                                    @if ($isAuthenticated)
                                                        <span class="fw-bold text-dark">{{ $user->name }}</span>
                                                        <span class="text-muted">({{ $user->email }})</span>
                                                    @else
                                                        <span class="text-muted fst-italic">Pelawat (Belum Log Masuk)</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="text-muted fw-normal py-2">Peranan Semasa</th>
                                                <td class="py-2">
                                                    <span class="badge {{ $roleBadgeClasses }} fw-semibold px-2 py-1">
                                                        {{ $roleTitle }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="text-muted fw-normal py-2">Kawalan Akses</th>
                                                <td class="py-2 text-secondary">
                                                    Pengasingan Tugas (Separation of Duties) &amp; RBAC
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Actions Navigation -->
                            <div class="d-flex flex-column gap-2 mb-4">
                                <!-- Primary Action: Navigate to appropriate dashboard / login -->
                                <a id="primary-redirect-btn" href="{{ $targetUrl }}" class="btn btn-ep-primary btn-lg w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-sm text-decoration-none">
                                    @if ($isAuthenticated)
                                        <i class="bi bi-speedometer2"></i>
                                    @else
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    @endif
                                    <span>{{ $targetLabel }}</span>
                                    <span class="badge bg-white text-primary rounded-pill px-2 py-0.5 ms-1 fw-bold" id="btn-countdown">3s</span>
                                </a>

                                <!-- Secondary Actions: Back & Portal Home -->
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('portal.home') }}'" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 small fw-semibold">
                                            <i class="bi bi-arrow-left"></i>
                                            <span>Halaman Sebelumnya</span>
                                        </button>
                                    </div>
                                    <div class="col-sm-6">
                                        <a href="{{ route('portal.home') }}" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 small fw-semibold text-decoration-none">
                                            <i class="bi bi-house"></i>
                                            <span>Laman Utama Portal</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Logout / Switch Account if Authenticated -->
                                @if ($isAuthenticated && $logoutRoute)
                                    <form method="POST" action="{{ $logoutRoute }}" class="w-100 pt-1">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 small fw-semibold">
                                            <i class="bi bi-box-arrow-right"></i>
                                            <span>Log Keluar / Tukar Akaun</span>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Helpdesk & Support Box -->
                            <div class="border-top pt-3 text-center text-muted" style="font-size: 0.8rem;">
                                <div>
                                    Perlukan bantuan kebenaran akses? Hubungi Pentadbir Sistem di
                                    <a href="mailto:{{ $orgEmail }}" class="text-decoration-underline fw-semibold text-dark">{{ $orgEmail }}</a>
                                    @if (!empty($orgPhone))
                                        atau talian <strong class="text-dark">{{ $orgPhone }}</strong>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-3 bg-white border-top text-center text-muted small mt-auto">
        <div class="container">
            <div class="fw-medium text-secondary">
                &copy; {{ date('Y') }} {{ $orgName }}. Hak Cipta Terpelihara.
            </div>
            <div class="text-muted" style="font-size: 0.75rem;">
                Platform e-Perolehan &amp; Pengurusan Bidaan Bertutup Berintegriti
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Auto-Redirect Countdown Script (3 Saat) -->
    <script>
        (function () {
            var secondsLeft = 3;
            var countdownEl = document.getElementById('countdown-seconds');
            var btnCountdownEl = document.getElementById('btn-countdown');
            var targetUrl = {!! json_encode($targetUrl) !!};

            var timer = setInterval(function () {
                secondsLeft--;
                if (countdownEl) {
                    countdownEl.textContent = Math.max(0, secondsLeft);
                }
                if (btnCountdownEl) {
                    btnCountdownEl.textContent = Math.max(0, secondsLeft) + 's';
                }
                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    window.location.replace(targetUrl);
                }
            }, 1000);
        })();
    </script>
</body>
</html>