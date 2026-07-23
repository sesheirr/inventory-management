@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <style>
        .category-card {
            background: #ffffff;
            color: #111827;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 16px;
            transition: background .2s ease, border-color .2s ease, color .2s ease;
        }

        html.dark .category-card {
            background: #111827;
            color: #f9fafb;
            border-color: rgba(255,255,255,0.12);
        }

        .category-card .fw-semibold,
        .category-card h5 {
            color: #111827;
        }

        html.dark .category-card .fw-semibold,
        html.dark .category-card h5 {
            color: #f9fafb !important;
        }

        .category-card .text-muted,
        .category-product-meta {
            color: #6b7280;
        }

        html.dark .category-card .text-muted,
        html.dark .category-product-meta {
            color: #d1d5db !important;
        }

        .category-product-item {
            padding: 16px 0;
            border-bottom: 1px solid rgba(15, 23, 42, 0.12);
        }

        html.dark .category-product-item {
            border-color: rgba(255,255,255,0.12);
        }

        .category-product-item:last-child {
            border-bottom: none;
        }

        html.dark .badge {
            background-color: rgba(59, 130, 246, 0.2) !important;
            color: #dbeafe !important;
        }
    </style>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Kategori</h4>
            <p class="text-muted mb-0">Filter dan lihat barang berdasarkan kategori utama.</p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-lg-auto">
            <form action="{{ route('categories.index') }}" method="GET" class="position-relative search-box" style="max-width: 420px; width: 100%;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="search" value="{{ $query ?? '' }}" class="form-control rounded-pill" placeholder="Cari kategori atau barang..." style="padding-left: 2.7rem;">
            </form>
        </div>
    </div>

    <div class="row g-4">
        @foreach($categories as $category)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="category-card p-4 h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-semibold mb-1">{{ $category->name }}</h5>
                            <p class="text-muted mb-0">{{ $category->count }} barang</p>
                        </div>
                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-3 py-2">Kategori Tetap</span>
                    </div>

                    @if($category->products->isEmpty())
                        <div class="text-muted">Tidak ada barang pada kategori ini.</div>
                    @else
                        @foreach($category->products->take(5) as $product)
                            <div class="category-product-item d-flex flex-column flex-sm-row justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <div class="category-product-meta">{{ $product->room ? 'Ruangan: ' . $product->room : 'Ruangan belum diisi' }}</div>
                                </div>
                                <div class="text-sm-end">
                                    <div class="fw-semibold">{{ $product->stock }}</div>
                                    <div class="category-product-meta">stok</div>
                                </div>
                            </div>
                        @endforeach

                        @if($category->count > 5)
                            <div class="mt-3 text-center">
                                <a href="{{ route('products.index', ['search' => $category->name]) }}" class="btn btn-sm btn-outline-primary rounded-pill">Lihat semua barang</a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
