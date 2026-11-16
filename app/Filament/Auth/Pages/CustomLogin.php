<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomLogin extends BaseLogin
{
    public function mount(): void
    {
        if (Filament::auth()->check()) {
            $user = auth()->user();
            $targetUrl = match ($user?->role) {
                'admin', 'urus_setia' => url('/admin'),
                'vendor' => url('/vendor'),
                'penilai_teknikal', 'penilai_kewangan', 'saksi_pembuka' => url('/penilai'),
                default => Filament::getUrl(),
            };

            redirect()->intended($targetUrl);
        }

        $this->form->fill();
    }

    protected function isUserAllowedToAccessPanel(Authenticatable $user): bool
    {
        if (! ($user instanceof FilamentUser)) {
            return true;
        }

        if (! $user->is_active) {
            return false;
        }

        // Allow any active user with an existing role to authenticate through the universal login
        return in_array($user->role, [
            'admin',
            'urus_setia',
            'vendor',
            'penilai_teknikal',
            'penilai_kewangan',
            'saksi_pembuka',
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        $user = auth()->user();

        if ($user && method_exists($user, 'canAccessPanel')) {
            $currentPanel = Filament::getCurrentOrDefaultPanel();

            if ($currentPanel && ! $user->canAccessPanel($currentPanel)) {
                $targetUrl = match ($user->role) {
                    'admin', 'urus_setia' => url('/admin'),
                    'vendor' => url('/vendor'),
                    'penilai_teknikal', 'penilai_kewangan', 'saksi_pembuka' => url('/penilai'),
                    default => url('/'),
                };

                return new class($targetUrl) implements LoginResponse
                {
                    public function __construct(protected string $url) {}

                    public function toResponse($request)
                    {
                        return redirect()->to($this->url);
                    }
                };
            }
        }

        return $response;
    }
}
