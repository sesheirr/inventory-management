@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header & Export Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Laporan & Statistik Inventaris</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Analisis data statistik, grafik distribusi barang, dan ekspor dokumen</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.exportExcel', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800/40 shadow-xs transition-colors">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Ekspor Excel</span>
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-xs transition-colors cursor-pointer">
                <i class="bi bi-printer"></i>
                <span>Cetak Laporan</span>
            </button>
        </div>
    </div>

    {{-- 5 Metric Cards Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Barang</span>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($totalProducts) }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Kategori</span>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($totalCategories) }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Ruangan</span>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($totalRooms) }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Mutasi</span>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($totalMutations) }}</div>
        </div>

        <div class="col-span-2 md:col-span-1 p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Barang Aktif</span>
            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($totalActive) }}</div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-5">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
            <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
            </div>

            <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
            </div>

            <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ruangan</label>
                <select name="room_id" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                    <option value="">Semua Ruangan</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected($roomId == $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kondisi</label>
                <select name="condition" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                    <option value="all" @selected($condition === 'all')>Semua</option>
                    <option value="active" @selected($condition === 'active')>Aktif</option>
                    <option value="inactive" @selected($condition === 'inactive')>Tidak Aktif</option>
                    <option value="out_of_stock" @selected($condition === 'out_of_stock')>Stok Habis</option>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all cursor-pointer">
                    Saring Data
                </button>
            </div>
        </form>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-bar-chart text-blue-600"></i> Distribusi Barang per Kategori
            </h2>
            <div class="h-64 relative">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-graph-up text-indigo-600"></i> Tren Mutasi per Bulan
            </h2>
            <div class="h-64 relative">
                <canvas id="mutationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryLabels = @json($categoryLabels);
    const categoryValues = @json($categoryValues);
    const mutationLabels = @json($mutationLabels);
    const mutationValues = @json($mutationValues);

    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Jumlah Barang',
                data: categoryValues,
                backgroundColor: 'rgba(37, 99, 235, 0.8)',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: textColor },
                    grid: { color: gridColor }
                },
                x: {
                    ticks: { color: textColor },
                    grid: { display: false }
                }
            }
        }
    });

    new Chart(document.getElementById('mutationChart'), {
        type: 'line',
        data: {
            labels: mutationLabels,
            datasets: [{
                label: 'Total Mutasi',
                data: mutationValues,
                fill: true,
                backgroundColor: 'rgba(79, 70, 229, 0.15)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 2,
                tension: 0.35,
                pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: textColor },
                    grid: { color: gridColor }
                },
                x: {
                    ticks: { color: textColor },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endsection