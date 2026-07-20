@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Detail Barang</h4>
            <p class="text-muted mb-0">Informasi lengkap untuk {{ $product->name }}.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4 text-center">
            <div class="detail-image">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                @else
                    <i class="bi bi-box2"></i>
                @endif
            </div>
        </div>
        <div class="col-lg-8">
            <h3 class="fw-semibold">{{ $product->name }}</h3>
            <p class="text-muted">{{ $product->description }}</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="stat-box">
                        <span class="text-muted">Kategori</span>
                        <div class="fw-semibold">{{ $product->category }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-box">
                        <span class="text-muted">Sub Kategori</span>
                        <div class="fw-semibold">{{ $product->subcategory }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="stat-box">
                        <span class="text-muted">Jumlah</span>
                        <div class="fw-semibold">{{ $product->stock }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-box">
                        <span class="text-muted">Status</span>
                        <div class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $product->status) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection