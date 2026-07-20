@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Detail Kategori</h4>
            <p class="text-muted mb-0">Tinjau informasi kategori.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="stat-box p-4">
                <span class="text-muted">Nama</span>
                <div class="fw-semibold">{{ $category->name }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-box p-4">
                <span class="text-muted">Jumlah Barang</span>
                <div class="fw-semibold">{{ $category->products->count() }}</div>
            </div>
        </div>
        <div class="col-12">
            <div class="stat-box p-4">
                <span class="text-muted">Deskripsi</span>
                <div class="fw-semibold">{{ $category->description }}</div>
            </div>
        </div>
    </div>
</div>
@endsection