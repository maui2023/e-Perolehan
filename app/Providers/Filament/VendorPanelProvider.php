<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Pages\CustomLogin;
use App\Filament\Vendor\Pages\VendorProfile;
use App\Models\OrganizationSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
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

class VendorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendor')
            ->path('vendor')
            ->spa()
            ->login(CustomLogin::class)
            ->registration()
            ->profile(VendorProfile::class, isSimple: false)
            ->brandName(fn () => OrganizationSetting::current()->org_name.' - Portal Pembekal')
            ->brandLogo(fn () => view('filament.brand', [
                'title' => OrganizationSetting::current()->org_short_name ?? 'e-Perolehan Universal',
                'subtitle' => 'Portal Pembekal & Vendor',
            ]))
            ->brandLogoHeight('2.75rem')
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_START,
                fn () => view('filament.login-header-logo', [
                    'role' => 'Portal Pembekal & Kontraktor',
                ]),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.vendor.components.mobile-head'),
            )
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn () => view('filament.vendor.components.mobile-styles'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.vendor.components.mobile-bottom-nav'),
            )
            ->navigationItems([
                NavigationItem::make('Profil Syarikat & Sijil')
                    ->url(fn (): string => route('filament.vendor.auth.profile'))
                    ->icon('heroicon-o-building-office-2')
                    ->sort(10)
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.vendor.auth.profile')),
            ])
            ->colors([
                'primary' => Color::hex('#1e3a8a'),
            ])
            ->discoverResources(in: app_path('Filament/Vendor/Resources'), for: 'App\Filament\Vendor\Resources')
            ->discoverPages(in: app_path('Filament/Vendor/Pages'), for: 'App\Filament\Vendor\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Vendor/Widgets'), for: 'App\Filament\Vendor\Widgets')
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
