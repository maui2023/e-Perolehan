@extends('layouts.public')

@section('title', 'Pengesahan Ketulenan Dokumen Digital')

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-white rounded-3xl border border-neutral-200 shadow-xl overflow-hidden">
        
        <!-- Verification Banner Header -->
        <div class="p-8 text-center {{ $verified ? 'bg-gradient-to-b from-emerald-50 to-white border-b border-emerald-100' : 'bg-gradient-to-b from-rose-50 to-white border-b border-rose-100' }}">
            <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center {{ $verified ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} shadow-inner mb-4">
                @if($verified)
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                @else
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                @endif
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2 {{ $verified ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                {{ $verified ? 'DOKUMEN SAH & TULEN' : 'DOKUMEN TIDAK DITEMUI' }}
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-navy-950">
                {{ $title }}
            </h1>
            <p class="text-xs text-neutral-500 font-mono mt-1">
                KOD RUJUKAN: <span class="font-bold text-navy-900">{{ $ref }}</span>
            </p>
        </div>

        <!-- Verification Table -->
        <div class="p-8">
            @if($verified)
                <div class="rounded-2xl border border-neutral-200 overflow-hidden shadow-sm">
                    <table class="w-full text-left text-sm divide-y divide-neutral-200">
                        @foreach($details as $label => $val)
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <th class="w-1/3 px-6 py-3.5 bg-neutral-50 text-xs font-bold text-neutral-600 uppercase tracking-wider">
                                    {{ $label }}
                                </th>
                                <td class="px-6 py-3.5 font-medium text-neutral-800">
                                    {{ $val }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                @if($auditLog)
                    <!-- Tamper-evident Audit Trail Fingerprint -->
                    <div class="mt-8 p-5 rounded-2xl bg-navy-950 text-neutral-300 border border-navy-800 space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-400">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span>REKOD AUDIT BERANTAI (HASH CHAIN VERIFICATION)</span>
                        </div>
                        <div class="text-[11px] text-neutral-400 font-mono space-y-1">
                            <div>Peristiwa: <span class="text-neutral-200">{{ $auditLog->event_type }}</span></div>
                            <div>Cap Masa Log: <span class="text-neutral-200">{{ $auditLog->created_at->toIso8601String() }}</span></div>
                            <div>Hash Integriti (SHA-256): <span class="text-emerald-400 break-all">{{ $auditLog->hash }}</span></div>
                            <div>Prev Hash: <span class="text-neutral-400 break-all">{{ $auditLog->prev_hash }}</span></div>
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-8 text-neutral-600 text-sm">
                    Nombor rujukan dokumen ini tidak sah atau belum didaftarkan di dalam pangkalan data rasmi {{ $setting->org_name }}. Sila pastikan anda menyemak daripada sumber yang sah.
                </div>
            @endif

            <div class="mt-8 text-center">
                <a href="{{ route('portal.home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-navy-800 bg-neutral-100 hover:bg-neutral-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Kembali ke Laman Utama</span>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-neutral-50 px-8 py-4 border-t border-neutral-100 text-center text-xs text-neutral-400">
            Sistem e-Perolehan Bersepadu & Ketelusan Awam &bull; {{ $setting->org_name }}
        </div>
    </div>
</div>

@endsection
