<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Pages\CustomLogin;
use App\Models\OrganizationSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PenilaiPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('penilai')
            ->path('penilai')
            ->spa()
            ->login(CustomLogin::class)
            ->brandName(fn () => OrganizationSetting::current()->org_name.' - Penilai')
            ->brandLogo(fn () => view('filament.brand', [
                'title' => OrganizationSetting::current()->org_short_name ?? 'e-Perolehan Universal',
                'subtitle' => 'Panel Penilai Tender',
            ]))
            ->brandLogoHeight('2.75rem')
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_START,
                fn () => view('filament.login-header-logo', [
                    'role' => 'Jawatankuasa Penilaian Perolehan',
                ]),
            )
            ->colors([
                'primary' => Color::hex('#1e3a8a'),
            ])
            ->discoverResources(in: app_path('Filament/Penilai/Resources'), for: 'App\Filament\Penilai\Resources')
            ->discoverPages(in: app_path('Filament/Penilai/Pages'), for: 'App\Filament\Penilai\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Penilai/Widgets'), for: 'App\Filament\Penilai\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
