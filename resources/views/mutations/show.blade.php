@extends('layouts.app')

@section('content')
<div class="card border-0 shadow-sm p-3 p-md-4 rounded-4">
    {{-- Header dengan Tombol Back (Hanya muncul di Mobile) --}}
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('mutations.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle d-md-none d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Kembali">
            <i class="bi bi-arrow-left fs-6"></i>
        </a>
        <div>
            <h4 class="fw-semibold mb-1">Detail Mutasi</h4>
            <p class="text-muted mb-0 small">Informasi lengkap mutasi barang.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-3 rounded-4 bg-light border-0">
                <small class="text-muted">Barang</small>
                <div class="fw-semibold">{{ $mutation->product->name ?? '-' }}</div>
                <small class="text-muted">{{ $mutation->product->kode_barang ?? '' }}</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 rounded-4 bg-light border-0">
                <small class="text-muted">Jenis Mutasi</small>
                <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $mutation->type)) }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 rounded-4 bg-light border-0">
                <small class="text-muted">Jumlah</small>
                <div class="fw-semibold">{{ $mutation->quantity }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 rounded-4 bg-light border-0">
                <small class="text-muted">Tanggal Mutasi</small>
                <div class="fw-semibold">{{ optional($mutation->mutation_date)->format('d/m/Y') }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 rounded-4 bg-light border-0">
                <small class="text-muted">Dari Ruang</small>
                <div class="fw-semibold">{{ $mutation->fromRoom?->name ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 rounded-4 bg-light border-0">
                <small class="text-muted">Ke Ruang</small>
                <div class="fw-semibold">{{ $mutation->toRoom?->name ?? '-' }}</div>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-3 rounded-4 bg-light border-0">
                <small class="text-muted">Catatan</small>
                <div class="fw-semibold">{{ $mutation->note ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection