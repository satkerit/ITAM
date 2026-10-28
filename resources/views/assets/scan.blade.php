@extends('layouts.app')

@section('title', 'Scan QR Code Aset')
@section('header', 'Audit Fisik & Pemindai QR Code')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6" x-data="qrScanner()" x-init="init()">
        <!-- Scanner Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 pb-4">
                <h2 class="text-base font-bold text-slate-900">Pindai QR Code Aset</h2>
                <p class="text-xs text-slate-500 mt-0.5">Arahkan kamera ke stiker QR Code / barcode pada unit hardware</p>
            </div>

            <!-- Camera Viewport -->
            <div class="relative bg-slate-950">
                <div class="relative w-full aspect-[4/3] sm:aspect-video max-h-[70vh] overflow-hidden">
                    <video id="qr-video" class="absolute inset-0 w-full h-full object-cover" autoplay muted playsinline></video>

                    <!-- Idle placeholder -->
                    <div x-show="!isScanning" class="absolute inset-0 flex flex-col items-center justify-center gap-4 text-center px-6">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 border border-indigo-400/30 flex items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <p class="text-sm text-slate-300 font-medium">Kamera belum aktif</p>
                        <p class="text-xs text-slate-500 max-w-xs">Tekan tombol "Mulai Scan" di bawah untuk mengaktifkan kamera perangkat.</p>
                    </div>

                    <!-- Scan frame overlay -->
                    <div x-show="isScanning" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <div class="relative w-[70%] max-w-[280px] aspect-square">
                            <div class="absolute inset-0 border-2 border-white/80 rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.45)]"></div>
                            <div class="absolute -top-0.5 -left-0.5 w-6 h-6 border-t-4 border-l-4 border-indigo-400 rounded-tl-2xl"></div>
                            <div class="absolute -top-0.5 -right-0.5 w-6 h-6 border-t-4 border-r-4 border-indigo-400 rounded-tr-2xl"></div>
                            <div class="absolute -bottom-0.5 -left-0.5 w-6 h-6 border-b-4 border-l-4 border-indigo-400 rounded-bl-2xl"></div>
                            <div class="absolute -bottom-0.5 -right-0.5 w-6 h-6 border-b-4 border-r-4 border-indigo-400 rounded-br-2xl"></div>
                            <div class="absolute left-3 right-3 top-1/2 h-0.5 bg-indigo-400/80 animate-pulse"></div>
                        </div>
                    </div>

                    <!-- Status badge -->
                    <div x-show="isScanning" class="absolute top-3 left-1/2 -translate-x-1/2 px-3 py-1.5 rounded-full bg-slate-900/80 backdrop-blur text-white text-[11px] font-semibold flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Memindai...
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="p-5 space-y-3">
                <template x-if="error">
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="error"></span>
                    </div>
                </template>

                <div class="flex gap-2">
                    <button @click="startScan()" x-show="!isScanning" :disabled="starting"
                        class="flex-1 min-h-[48px] px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-60 text-white font-semibold text-sm shadow-md transition flex items-center justify-center gap-2">
                        <svg x-show="starting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="starting ? 'Memuat Kamera...' : 'Mulai Scan'"></span>
                    </button>
                    <button @click="stopScan()" x-show="isScanning"
                        class="flex-1 min-h-[48px] px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-sm transition">
                        Hentikan
                    </button>
                    <button @click="switchCamera()" x-show="isScanning && cameraCount > 1" title="Ganti Kamera"
                        class="min-h-[48px] w-[48px] px-3 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>

                <!-- Manual input -->
                <form @submit.prevent="submitManual()" class="flex gap-2 pt-2 border-t border-slate-100">
                    <input type="text" name="code" x-model="manualCode" placeholder="Atau ketik Kode Aset (cth: LPT-2026-001)"
                        class="flex-1 min-h-[44px] text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 px-3.5 py-2.5 border font-mono uppercase">
                    <button type="submit"
                        class="min-h-[44px] px-5 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        <!-- Scan Result Card -->
        @if($foundAsset)
            <div class="bg-white rounded-2xl border border-emerald-200 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <span class="px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800 font-mono text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ $foundAsset->asset_code }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white text-slate-700 border border-slate-200">
                        {{ ucfirst($foundAsset->status->value) }}
                    </span>
                </div>

                <h3 class="text-lg font-bold text-slate-900">{{ $foundAsset->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5 mb-4">{{ $foundAsset->category->name }} &bull; {{ $foundAsset->location->name }}</p>

                @if($foundAsset->activeAssignment)
                    <div class="p-3 bg-indigo-50 rounded-xl mb-4 text-xs text-slate-700 border border-indigo-100">
                        <span class="font-bold text-indigo-700">Dipegang oleh:</span> {{ $foundAsset->activeAssignment->user->name }}
                        ({{ $foundAsset->activeAssignment->assigned_date->format('d M Y') }})
                    </div>
                @endif

                <a href="{{ route('web.assets.show', $foundAsset) }}" class="block min-h-[48px] py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-center font-semibold text-sm shadow-xs transition">
                    Buka Riwayat Aset &rarr;
                </a>
            </div>
        @elseif(request('code'))
            <div class="bg-white rounded-2xl border border-rose-200 p-6 text-center">
                <svg class="w-10 h-10 text-rose-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="font-bold text-sm text-rose-800">Kode "{{ request('code') }}" tidak ditemukan</p>
                <p class="text-xs text-slate-500 mt-1">Periksa kembali kode aset, atau scan ulang QR Code.</p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function qrScanner() {
            return {
                scanner: null,
                isScanning: false,
                starting: false,
                scanned: false,
                error: '',
                manualCode: '',
                cameraCount: 0,
                cameraIndex: 0,
                cameras: [],

                init() {
                    // Parse URL query (?code=...) inside x-data context
                    const params = new URLSearchParams(window.location.search);
                    this.manualCode = params.get('code') || '';
                },

                async startScan() {
                    if (this.isScanning || this.starting) return;
                    this.starting = true;
                    this.error = '';

                    try {
                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            throw new Error('Browser tidak mendukung akses kamera. Gunakan Chrome/Safari terbaru.');
                        }

                        this.scanner = new Html5Qrcode('qr-video', {
                            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE, Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39, Html5QrcodeSupportedFormats.EAN_13],
                            verbose: false,
                        });

                        this.cameras = await Html5Qrcode.getCameras();
                        this.cameraCount = this.cameras.length;
                        this.cameraIndex = this.cameras.length - 1; // prefer back camera

                        await this.openCamera();
                        this.isScanning = true;
                    } catch (e) {
                        this.showError(e && e.message ? e.message : String(e));
                    } finally {
                        this.starting = false;
                    }
                },

                async openCamera() {
                    const camera = this.cameras[this.cameraIndex];
                    const boxSize = Math.min(window.innerWidth * 0.7, 280);
                    await this.scanner.start(
                        camera ? camera.id : { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: boxSize, height: boxSize }, aspectRatio: 4 / 3 },
                        (decodedText) => this.onScan(decodedText),
                        () => { /* per-frame decode miss — ignore */ }
                    );
                },

                async stopScan() {
                    this.isScanning = false;
                    if (!this.scanner) return;
                    try { await this.scanner.stop(); this.scanner.clear(); } catch (e) { /* ignore */ }
                    this.scanner = null;
                },

                async switchCamera() {
                    if (this.cameraCount < 2) return;
                    this.cameraIndex = (this.cameraIndex + 1) % this.cameraCount;
                    try {
                        await this.scanner.stop();
                        await this.openCamera();
                    } catch (e) {
                        this.showError(e && e.message ? e.message : String(e));
                    }
                },

                onScan(decodedText) {
                    if (this.scanned) return;
                    this.scanned = true;
                    this.stopScan();
                    // Small delay so UI feels responsive before navigation
                    setTimeout(() => {
                        window.location.href = "{{ route('web.assets.scan') }}?code=" + encodeURIComponent(decodedText);
                    }, 150);
                },

                submitManual() {
                    const code = this.manualCode.trim().toUpperCase();
                    if (!code) return;
                    window.location.href = "{{ route('web.assets.scan') }}?code=" + encodeURIComponent(code);
                },

                showError(message) {
                    const isHttp = window.location.protocol === 'http:' && !['localhost', '127.0.0.1'].includes(window.location.hostname);
                    if (isHttp) {
                        this.error = 'Kamera tidak dapat diakses via HTTP. Buka aplikasi melalui https:// atau gunakan pencarian manual di bawah.';
                    } else if (/permission|denied|not allowed/i.test(message)) {
                        this.error = 'Izin kamera ditolak. Buka pengaturan browser Anda dan izinkan akses kamera untuk situs ini.';
                    } else {
                        this.error = 'Gagal mengakses kamera: ' + message + ' Gunakan pencarian manual di bawah.';
                    }
                }
            }
        }
    </script>
    @endpush
@endsection
