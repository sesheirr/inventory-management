@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Persetujuan Mutasi Barang (Approval)</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar permohonan mutasi barang yang memerlukan verifikasi administrator</p>
        </div>

        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
            Kembali
        </a>
    </div>

    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-5 py-3.5">Nama Barang</th>
                        <th class="px-4 py-3.5">Ruangan Asal</th>
                        <th class="px-4 py-3.5">Ruangan Tujuan</th>
                        <th class="px-4 py-3.5">Pengaju</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @forelse($mutations as $mutation)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">
                                {{ $mutation->product?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $mutation->fromRoom?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $mutation->toRoom?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-700 dark:text-slate-300">
                                {{ $mutation->user?->name ?? 'Staf' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                {{ $mutation->mutation_date ? $mutation->mutation_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $mutation->status === 'pending' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($mutation->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($mutation->status === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('mutations.approve', $mutation) }}" onsubmit="return confirm('Setujui mutasi ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition-colors cursor-pointer">
                                                Setujui
                                            </button>
                                        </form>

                                        <button type="button" data-modal-target="rejectModal{{ $mutation->id }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors cursor-pointer">
                                            Tolak
                                        </button>
                                    </div>

                                    {{-- Rejection Modal --}}
                                    <div id="rejectModal{{ $mutation->id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm text-left">
                                        <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 animate-fade-in">
                                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                                                <h3 class="text-base font-bold text-rose-600">Tolak Permohonan Mutasi</h3>
                                                <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                                                    <i class="bi bi-x-lg text-sm"></i>
                                                </button>
                                            </div>

                                            <form method="POST" action="{{ route('mutations.reject', $mutation) }}" class="space-y-4">
                                                @csrf
                                                @method('PATCH')
                                                <div class="space-y-1.5">
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alasan Penolakan <span class="text-rose-500">*</span></label>
                                                    <textarea name="rejection_note" rows="3" required placeholder="Tulis alasan penolakan..." 
                                                              class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500/40 focus:border-rose-500 transition-colors"></textarea>
                                                </div>
                                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                                                    <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800">
                                                        Batal
                                                    </button>
                                                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm">
                                                        Tolak Mutasi
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="bi bi-check-circle text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Tidak ada permohonan mutasi yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
