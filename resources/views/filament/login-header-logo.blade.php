@php
    $currentPath = request()->path();
    $isVendor = str_contains($currentPath, 'vendor');
    $isPenilai = str_contains($currentPath, 'penilai');
    $isAdmin = ! $isVendor && ! $isPenilai;
@endphp

<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; margin-bottom: 1.25rem; width: 100%;">
    <!-- Active Portal Indicator Badge -->
    <div style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.35rem 0.95rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background-color: #eff6ff; color: #1e3a8a; border: 1px solid #bfdbfe; margin-bottom: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <span style="width: 8px; height: 8px; border-radius: 9999px; background-color: #10b981; display: inline-block;"></span>
        <span>{{ $role ?? 'Pentadbir Perolehan & Kontrak' }}</span>
    </div>

    <!-- Portal Navigation Switcher -->
    <div style="display: flex; gap: 0.35rem; margin-bottom: 0.85rem; background: #f1f5f9; padding: 4px; border-radius: 0.5rem; border: 1px solid #cbd5e1; width: 100%; max-width: 24rem; justify-content: space-between;">
        <a href="/admin/login" 
           style="flex: 1; padding: 5px 8px; font-size: 0.72rem; font-weight: 600; text-decoration: none; border-radius: 0.375rem; text-align: center; transition: all 0.15s ease; {{ $isAdmin ? 'background: #1e3a8a; color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.12);' : 'color: #475569; background: transparent;' }}">
           🏢 Pentadbir
        </a>
        <a href="/vendor/login" 
           style="flex: 1; padding: 5px 8px; font-size: 0.72rem; font-weight: 600; text-decoration: none; border-radius: 0.375rem; text-align: center; transition: all 0.15s ease; {{ $isVendor ? 'background: #1e3a8a; color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.12);' : 'color: #475569; background: transparent;' }}">
           🏭 Pembekal
        </a>
        <a href="/penilai/login" 
           style="flex: 1; padding: 5px 8px; font-size: 0.72rem; font-weight: 600; text-decoration: none; border-radius: 0.375rem; text-align: center; transition: all 0.15s ease; {{ $isPenilai ? 'background: #1e3a8a; color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.12);' : 'color: #475569; background: transparent;' }}">
           ⚖️ Penilai
        </a>
    </div>

    <!-- Credentials & One-Click Demo Quick Fill Box -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.65rem; padding: 0.75rem 1rem; font-size: 0.75rem; color: #334155; width: 100%; max-width: 24rem; text-align: left; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.4rem;">
            <div style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                <span>🔑</span>
                <span>Panduan Log Masuk (Kata laluan: <code style="background-color: #e2e8f0; padding: 1px 4px; border-radius: 4px; font-family: monospace; font-size: 0.72rem; font-weight: 600;">password</code>)</span>
            </div>
            <span style="font-size: 0.65rem; color: #059669; font-weight: 600; background: #d1fae5; padding: 1px 6px; border-radius: 4px;">Smart Routing Aktif</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.75rem;">
            <!-- Pentadbir Row -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: {{ $isAdmin ? '#eff6ff' : '#ffffff' }}; padding: 4px 8px; border-radius: 4px; border: 1px solid {{ $isAdmin ? '#bfdbfe' : '#f1f5f9' }};">
                <div>
                    <strong>• Pentadbir:</strong> 
                    <code style="color: #1e3a8a; font-family: monospace; font-size: 0.72rem;">admin@organisasi.my</code>
                </div>
                <button type="button" 
                        onclick="fillDemoLogin('admin@organisasi.my', 'password')" 
                        style="cursor: pointer; background: #1e3a8a; color: #ffffff; border: none; border-radius: 4px; padding: 2px 7px; font-size: 0.65rem; font-weight: 600;">
                    ⚡ Isi
                </button>
            </div>

            <!-- Pembekal Row -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: {{ $isVendor ? '#eff6ff' : '#ffffff' }}; padding: 4px 8px; border-radius: 4px; border: 1px solid {{ $isVendor ? '#bfdbfe' : '#f1f5f9' }};">
                <div>
                    <strong>• Pembekal:</strong> 
                    <code style="color: #1e3a8a; font-family: monospace; font-size: 0.72rem;">vendor1@bina-maju.com.my</code>
                </div>
                <button type="button" 
                        onclick="fillDemoLogin('vendor1@bina-maju.com.my', 'password')" 
                        style="cursor: pointer; background: #0284c7; color: #ffffff; border: none; border-radius: 4px; padding: 2px 7px; font-size: 0.65rem; font-weight: 600;">
                    ⚡ Isi
                </button>
            </div>

            <!-- Penilai Row -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: {{ $isPenilai ? '#eff6ff' : '#ffffff' }}; padding: 4px 8px; border-radius: 4px; border: 1px solid {{ $isPenilai ? '#bfdbfe' : '#f1f5f9' }};">
                <div>
                    <strong>• Penilai:</strong> 
                    <code style="color: #1e3a8a; font-family: monospace; font-size: 0.72rem;">penilai.teknikal@organisasi.my</code>
                </div>
                <button type="button" 
                        onclick="fillDemoLogin('penilai.teknikal@organisasi.my', 'password')" 
                        style="cursor: pointer; background: #059669; color: #ffffff; border: none; border-radius: 4px; padding: 2px 7px; font-size: 0.65rem; font-weight: 600;">
                    ⚡ Isi
                </button>
            </div>
        </div>

        <div style="margin-top: 0.5rem; font-size: 0.68rem; color: #64748b; line-height: 1.25; border-top: 1px dashed #e2e8f0; padding-top: 0.4rem;">
            ℹ️ Klik butang <strong>⚡ Isi</strong> untuk mengisi emel & kata laluan secara automatik. Sistem akan menghalakan anda ke portal yang betul secara pintar tanpa sebarang ralat.
        </div>
    </div>
</div>

<script>
    function fillDemoLogin(email, password) {
        // Find inputs by id or wire:model
        const emailInput = document.getElementById('form.email') || document.querySelector('input[type="email"]');
        const passInput = document.getElementById('form.password') || document.querySelector('input[type="password"]');

        if (emailInput) {
            emailInput.value = email;
            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
            emailInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (passInput) {
            passInput.value = password;
            passInput.dispatchEvent(new Event('input', { bubbles: true }));
            passInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
</script>
