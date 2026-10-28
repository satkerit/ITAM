@extends('layouts.app')

@section('title', 'Scan QR Code Aset')
@section('header', 'Audit Fisik & Pemindai QR Code')

@section('content')
    <div class="max-w-3xl mx-auto space-y-8">
        <!-- Scanner Component -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6" x-data="qrScanner()" x-init="init()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Kamera Pemindai QR Code</h2>
                    <p class="text-xs text-slate-500">Arahkan kamera perangkat ke stiker barcode / QR code pada unit hardware</p>
                </div>
            </div>

            <div id="reader" class="w-full rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 min-h-[300px]"></div>

            <template x-if="error">
                <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm" x-text="error"></div>
            </template>

            <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <form action="{{ route('web.assets.scan') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-2">
                    <input type="text" name="code" value="{{ request('code') }}" placeholder="Atau ketik manual Kode Aset (cth: LPT-2026-001)..." class="flex-1 text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border font-mono">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl transition text-center">
                        Cari Manual
                    </button>
                </form>
            </div>
        </div>

        <!-- Scan Result Card -->
        @if($foundAsset)
            <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-2.5 py-1 rounded-md bg-emerald-600 text-white font-mono text-xs font-bold">
                        DITEMUKAN: {{ $foundAsset->asset_code }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white text-emerald-800 border border-emerald-300">
                        Status: {{ ucfirst($foundAsset->status->value) }}
                    </span>
                </div>

                <h3 class="text-xl font-bold text-slate-900 mb-1">{{ $foundAsset->name }}</h3>
                <p class="text-xs text-slate-600 mb-4">{{ $foundAsset->category->name }} &bull; Lokasi Terdata: {{ $foundAsset->location->name }}</p>

                @if($foundAsset->activeAssignment)
                    <div class="p-3 bg-white/80 rounded-xl mb-4 text-xs text-slate-700 border border-emerald-100">
                        <span class="font-bold text-indigo-700">Sedang Dipegang Oleh:</span> {{ $foundAsset->activeAssignment->user->name }}
                        ({{ $foundAsset->activeAssignment->assigned_date->format('d M Y') }})
                    </div>
                @endif

                <div class="flex gap-3">
                    <a href="{{ route('web.assets.show', $foundAsset) }}" class="flex-1 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-center font-semibold text-xs shadow-xs transition">
                        Buka Kartu Riwayat Aset &rarr;
                    </a>
                </div>
            </div>
        @elseif(request('code'))
            <div class="bg-rose-50 rounded-2xl border border-rose-200 p-6 text-center text-rose-800">
                <svg class="w-10 h-10 text-rose-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="font-bold text-sm">Kode Aset "{{ request('code') }}" tidak ditemukan dalam sistem!</p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function qrScanner() {
            return {
                error: '',
                scanned: false,
                scanner: null,
                init() {
                    this.$nextTick(() => {
                        try {
                            this.scanner = new Html5QrcodeScanner(
                                'reader',
                                { fps: 10, qrbox: { width: 250, height: 250 }, rememberLastUsedCamera: true },
                                false
                            );
                            this.scanner.render(
                                (decodedText) => this.onScan(decodedText),
                                (errorMessage) => {
                                    // Ignored: frame-by-frame decode misses are noisy.
                                    if (typeof errorMessage === 'string' && /permission|denied|not allowed|notfound: could not query|secure context|https/i.test(errorMessage)) {
                                        this.showError(errorMessage);
                                    }
                                }
                            );
                        } catch (e) {
                            this.showError(e && e.message ? e.message : 'Scanner gagal dimuat.');
                        }
                    });
                },
                onScan(decodedText) {
                    if (this.scanned) return;
                    this.scanned = true;
                    try { this.scanner && this.scanner.clear(); } catch (e) { /* ignore */ }
                    window.location.href = "{{ route('web.assets.scan') }}?code=" + encodeURIComponent(decodedText);
                },
                showError(message) {
                    const isHttp = window.location.protocol === 'http:' && !['localhost', '127.0.0.1'].includes(window.location.hostname);
                    if (isHttp) {
                        this.error = 'Kamera tidak dapat diakses: kamera wajib HTTPS. Buka aplikasi via https:// atau gunakan input manual di bawah.';
                    } else {
                        this.error = 'Kamera tidak dapat diakses: ' + message + ' Pastikan izin kamera diberikan, atau gunakan input manual di bawah.';
                    }
                }
            }
        }
    </script>
    @endpush
@endsection
