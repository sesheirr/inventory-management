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
            <div class="detail-image d-flex align-items-center justify-content-center overflow-hidden p-2 bg-light rounded-4 border" style="width: 100%; min-height: 200px; height: auto;">
                @if($product->image)
                    @php
                        // Cek apakah gambar menggunakan URL Cloudinary / Eksternal
                        $imageSrc = \Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://']) 
                            ? $product->image 
                            : asset('storage/' . ltrim($product->image, '/'));
                    @endphp
                    <img src="{{ $imageSrc }}" 
                         alt="{{ $product->name }}" 
                         class="img-fluid rounded-3"
                         style="max-width: 100%; max-height: 350px; width: auto; height: auto; object-fit: contain;"
                         onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=Gambar+Error';">
                @else
                    <i class="bi bi-box2 display-4 text-muted py-5"></i>
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