@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Detail Mutasi</h4>
            <p class="text-muted mb-0">Informasi lengkap mutasi barang.</p>
        </div>
        <a href="{{ route('mutations.index') }}" class="btn btn-secondary rounded-pill">Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold">Barang</h5>
                <p class="mb-1">{{ $mutation->product->name ?? '-' }}</p>
                <small class="text-muted">{{ $mutation->product->kode_barang ?? '' }}</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold">Jenis Mutasi</h5>
                <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $mutation->type)) }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold">Jumlah</h5>
                <p class="mb-0">{{ $mutation->quantity }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold">Tanggal Mutasi</h5>
                <p class="mb-0">{{ optional($mutation->mutation_date)->format('Y-m-d H:i') }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold">Dari Ruang</h5>
                <p class="mb-0">{{ $mutation->fromRoom?->name ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold">Ke Ruang</h5>
                <p class="mb-0">{{ $mutation->toRoom?->name ?? '-' }}</p>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold">Catatan</h5>
                <p class="mb-0">{{ $mutation->note ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
