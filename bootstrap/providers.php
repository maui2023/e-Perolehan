<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\PenilaiPanelProvider;
use App\Providers\Filament\VendorPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    PenilaiPanelProvider::class,
    VendorPanelProvider::class,
];
