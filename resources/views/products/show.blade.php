@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Detail Barang</h4>
            <p class="text-muted mb-0">Informasi lengkap untuk {{ $product->name }}.</p>
        </div>
    </div>

    <style>
        .detail-image {
            width: 100%;
            min-height: 220px;
            border-radius: 1rem;
            border: 1px solid var(--bs-border-color);
            background-color: var(--bs-body-tertiary);
        }

        .detail-image img {
            max-width: 100%;
            max-height: 350px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .stat-box {
            padding: 1rem;
            border-radius: 1rem;
            border: 1px solid var(--bs-border-color);
            background-color: var(--bs-body-tertiary);
            min-height: 100px;
        }

        .stat-box span {
            display: block;
            margin-bottom: 0.5rem;
        }
    </style>

    <div class="row g-4">
        <div class="col-lg-4 text-center">
            <div class="detail-image d-flex align-items-center justify-content-center overflow-hidden p-2">
                @if($product->image)
                    @php
                        // Cek apakah gambar menggunakan URL Cloudinary / Eksternal
                        $imageSrc = \Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://'])
                            ? $product->image
                            : asset('storage/' . ltrim($product->image, '/'));
                    @endphp
                    <img src="{{ $imageSrc }}"
                         alt="{{ $product->name }}"
                         class="rounded-3"
                         onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=Gambar+Error';">
                @else
                    <i class="bi bi-box2 display-4 text-muted py-5"></i>
                @endif
            </div>
        </div>
        <div class="col-lg-8">
            <h3 class="fw-semibold">{{ $product->name }}</h3>
            <p class="text-muted">{{ $product->description ?: 'Tidak ada deskripsi tersedia.' }}</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="stat-box">
                        <span class="text-muted">Kategori</span>
                        <div class="fw-semibold">{{ $product->category }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-box">
                        <span class="text-muted">Ruangan</span>
                        <div class="fw-semibold">{{ $product->room ?: 'Belum diisi' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-box">
                        <span class="text-muted">Sub Kategori</span>
                        <div class="fw-semibold">{{ $product->subcategory ?: 'Tidak ada' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-box">
                        <span class="text-muted">Jumlah</span>
                        <div class="fw-semibold">{{ $product->stock ?? 0 }}</div>
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