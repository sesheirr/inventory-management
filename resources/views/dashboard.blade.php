@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Dashboard</h4>
            <p class="text-muted mb-0">Ringkasan inventaris, statistik, dan mutasi barang.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-box p-4 rounded-4 shadow-sm">
                <span class="text-muted">Total Barang</span>
                <div class="fs-3 fw-semibold">{{ $totalProducts }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box p-4 rounded-4 shadow-sm">
                <span class="text-muted">Total Kategori</span>
                <div class="fs-3 fw-semibold">{{ $totalCategories }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box p-4 rounded-4 shadow-sm">
                <span class="text-muted">Total Ruangan</span>
                <div class="fs-3 fw-semibold">{{ $totalRooms }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box p-4 rounded-4 shadow-sm">
                <span class="text-muted">Total Mutasi</span>
                <div class="fs-3 fw-semibold">{{ $totalMutations }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box p-4 rounded-4 shadow-sm">
                <span class="text-muted">Barang Rusak</span>
                <div class="fs-3 fw-semibold">{{ $totalDamaged }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box p-4 rounded-4 shadow-sm">
                <span class="text-muted">Barang Aktif</span>
                <div class="fs-3 fw-semibold">{{ $totalActive }}</div>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-4 rounded-4 shadow-sm">
        <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Dari</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ruangan</label>
                <select name="room_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected($roomId == $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kondisi</label>
                <select name="condition" class="form-select">
                    <option value="all" @selected($condition === 'all')>Semua</option>
                    <option value="active" @selected($condition === 'active')>Aktif</option>
                    <option value="inactive" @selected($condition === 'inactive')>Tidak Aktif</option>
                    <option value="out_of_stock" @selected($condition === 'out_of_stock')>Stok Habis</option>
                </select>
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary rounded-pill">Saring</button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold mb-3">Barang per Kategori</h5>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold mb-3">Mutasi per Bulan</h5>
                <canvas id="mutationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const categoryLabels = @json($categoryLabels);
    const categoryValues = @json($categoryValues);
    const mutationLabels = @json($mutationLabels);
    const mutationValues = @json($mutationValues);

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Barang',
                data: categoryValues,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    new Chart(document.getElementById('mutationChart'), {
        type: 'line',
        data: {
            labels: mutationLabels,
            datasets: [{
                label: 'Mutasi',
                data: mutationValues,
                fill: true,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>
@endsection
