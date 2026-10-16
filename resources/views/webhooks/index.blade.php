@extends('layouts.app')

@section('page-title', 'Admin Webhook Studio')
@section('page-subtitle', 'Integrasi real-time event inventaris & mutasi ke sistem eksternal Diskominfo dengan tanda tangan HMAC-SHA256.')

@section('content')
<div class="space-y-6" id="v2-webhook-studio">
    {{-- Header Banner --}}
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 dark:bg-blue-950/60 px-3 py-1 text-xs font-bold text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">
                <i class="bi bi-broadcast-pin"></i>
                <span>Admin & Super Admin Only • Outgoing Webhook Dispatcher</span>
            </div>
            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white">
                Manajemen Webhook & Audit Pengiriman Event
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Setiap payload JSON dikirim menggunakan <code class="font-mono text-xs px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800">X-Diskominfo-Signature-256</code> (HMAC-SHA256).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('observability.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-blue-500/40 transition-all">
                <i class="bi bi-activity text-blue-600 dark:text-blue-400"></i>
                <span>Observability & Telescope</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Form Tambah Endpoint Webhook --}}
        <div class="lg:col-span-5">
            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-plus-circle-fill text-blue-600 dark:text-blue-400"></i>
                    <span>Daftarkan Endpoint Webhook Baru</span>
                </h3>

                <form action="{{ route('webhooks.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Sistem / Integrasi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Contoh: SI-ASET Pemerintah Kabupaten Garut"
                               value="{{ old('name') }}"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            URL Tujuan (HTTPS/HTTP) <span class="text-rose-500">*</span>
                        </label>
                        <input type="url"
                               name="target_url"
                               required
                               placeholder="https://integrasi.garutkab.go.id/webhooks/inventaris"
                               value="{{ old('target_url') }}"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 px-3.5 py-2.5 font-mono text-xs text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            HMAC Secret Key (Opsional — Otomatis dibuat jika kosong)
                        </label>
                        <input type="text"
                               name="secret"
                               placeholder="whsec_..."
                               value="{{ old('secret') }}"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 px-3.5 py-2.5 font-mono text-xs text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            Langganan Event <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($availableEvents as $eventKey => $eventLabel)
                                <label class="flex items-start gap-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 p-2.5 text-xs cursor-pointer hover:border-blue-500/50 transition-all">
                                    <input type="checkbox"
                                           name="events[]"
                                           value="{{ $eventKey }}"
                                           checked
                                           class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $eventLabel }}</div>
                                        <div class="font-mono text-[10px] text-slate-400">{{ $eventKey }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-600/20 transition-all cursor-pointer">
                        <i class="bi bi-shield-check"></i>
                        <span>Simpan Endpoint Webhook</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar Endpoint & Riwayat Pengiriman --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center justify-between">
                    <span>Endpoint Aktif ({{ $endpoints->count() }})</span>
                </h3>

                <div class="mt-4 space-y-3">
                    @forelse($endpoints as $endpoint)
                        <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 p-4 space-y-3">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $endpoint->name }}</span>
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $endpoint->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                            {{ $endpoint->is_active ? 'Aktif' : 'Dijeda' }}
                                        </span>
                                    </div>
                                    <div class="mt-1 font-mono text-xs text-slate-500 dark:text-slate-400 break-all">
                                        {{ $endpoint->target_url }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <form action="{{ route('webhooks.ping', $endpoint) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                title="Kirim Test Ping"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 px-2.5 py-1.5 text-xs font-bold text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition-colors cursor-pointer">
                                            <i class="bi bi-send-check"></i>
                                            <span>Test Ping</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('webhooks.toggle', $endpoint) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                title="{{ $endpoint->is_active ? 'Jeda Webhook' : 'Aktifkan Webhook' }}"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-blue-500/40 transition-colors cursor-pointer">
                                            <i class="bi {{ $endpoint->is_active ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('webhooks.destroy', $endpoint) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Hapus Webhook"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5">
                                @foreach((array) $endpoint->events as $ev)
                                    <span class="rounded-md bg-slate-100 dark:bg-slate-800 px-2 py-0.5 font-mono text-[10px] font-semibold text-slate-600 dark:text-slate-300">
                                        {{ $ev }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 p-6 text-center text-xs text-slate-500">
                            Belum ada endpoint Webhook yang terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Log Pengiriman Webhook --}}
            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                    Audit Pengiriman Terakhir ({{ $recentDeliveries->count() }})
                </h3>
                <div class="mt-3 space-y-2">
                    @forelse($recentDeliveries as $delivery)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-800 px-3.5 py-2.5 text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full {{ $delivery->status === 'delivered' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' }}">
                                    <i class="bi {{ $delivery->status === 'delivered' ? 'bi-check2' : 'bi-x-lg' }}"></i>
                                </span>
                                <div>
                                    <div class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                        {{ $delivery->event_name }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $delivery->endpoint?->name ?? 'Endpoint' }} • {{ $delivery->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right font-mono">
                                <span class="font-bold {{ $delivery->status === 'delivered' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    HTTP {{ $delivery->response_status ?? 'ERR' }}
                                </span>
                                <div class="text-[10px] text-slate-400">{{ $delivery->duration_ms ?? 0 }} ms</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-xs text-slate-500">
                            Belum ada riwayat pengiriman webhook.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
