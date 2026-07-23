@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <style>
        /* ==========================================================
           LIGHT MODE (default) — tidak diubah
           ========================================================== */
        .category-card {
<<<<<<< HEAD
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
=======
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            transition: background .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .category-card:hover {
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1);
        }

        .category-title {
            color: var(--text);
        }

        .category-subtitle,
        .category-product-meta {
            color: var(--muted);
>>>>>>> 2216918e6076e9594849353f7859f1ec38eb41cc
        }

        .category-product-item {
            padding: 16px 0;
<<<<<<< HEAD
            border-bottom: 1px solid rgba(15, 23, 42, 0.12);
        }

        html.dark .category-product-item {
            border-color: rgba(255,255,255,0.12);
=======
            border-bottom: 1px solid var(--border);
>>>>>>> 2216918e6076e9594849353f7859f1ec38eb41cc
        }

        .category-product-item:last-child {
            border-bottom: none;
        }

<<<<<<< HEAD
        html.dark .badge {
            background-color: rgba(59, 130, 246, 0.2) !important;
            color: #dbeafe !important;
=======
        .category-product-name,
        .category-product-stock {
            color: var(--text);
        }

        .category-badge {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .category-search-icon {
            color: var(--muted);
        }

        .category-search-input {
            background: var(--card);
            border-color: var(--border);
            color: var(--text);
        }

        .category-search-input::placeholder {
            color: var(--muted);
        }

        /* ==========================================================
           DARK MODE — pakai selector html.dark, sesuai project ini
           (bukan [data-bs-theme="dark"] — itu tidak dipakai project ini)
           ========================================================== */
        html.dark .category-card {
            background: #1F2937;
            border-color: #374151;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .4);
        }

        html.dark .category-card:hover {
            background: #273449;
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .45);
        }

        html.dark .category-title {
            color: #F9FAFB;
        }

        html.dark .category-subtitle,
        html.dark .category-product-meta {
            color: #9CA3AF;
        }

        html.dark .category-product-item {
            border-color: #374151;
        }

        html.dark .category-product-name,
        html.dark .category-product-stock {
            color: #E5E7EB;
        }

        html.dark .category-badge {
            background-color: rgba(59, 130, 246, 0.15);
            color: #93C5FD;
        }

        html.dark .category-search-icon {
            color: #9CA3AF;
        }

        html.dark .category-search-input {
            background: #1F2937;
            border-color: #374151;
            color: #E5E7EB;
        }

        html.dark .category-search-input::placeholder {
            color: #9CA3AF;
        }

        html.dark .category-search-input:focus {
            background: #1F2937;
            border-color: #4B5563;
            color: #E5E7EB;
            box-shadow: 0 0 0 .2rem rgba(59, 130, 246, .15);
>>>>>>> 2216918e6076e9594849353f7859f1ec38eb41cc
        }
    </style>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1 category-title">Kategori</h4>
            <p class="mb-0 category-subtitle">Filter dan lihat barang berdasarkan kategori utama.</p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-lg-auto">
            <form action="{{ route('categories.index') }}" method="GET" class="position-relative search-box" style="max-width: 420px; width: 100%;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 category-search-icon"></i>
                <input type="text" name="search" value="{{ $query ?? '' }}" class="form-control rounded-pill category-search-input" placeholder="Cari kategori atau barang..." style="padding-left: 2.7rem;">
            </form>
        </div>
    </div>

    <div class="row g-4">
        @foreach($categories as $category)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="category-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-semibold mb-1 category-title">{{ $category->name }}</h5>
                            <p class="mb-0 category-subtitle">{{ $category->count }} barang</p>
                        </div>
                        <span class="badge rounded-pill px-3 py-2 category-badge">Kategori Tetap</span>
                    </div>

                    @if($category->products->isEmpty())
                        <div class="category-subtitle">Tidak ada barang pada kategori ini.</div>
                    @else
                        @foreach($category->products->take(5) as $product)
                            <div class="category-product-item d-flex flex-column flex-sm-row justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold category-product-name">{{ $product->name }}</div>
                                    <div class="category-product-meta">{{ $product->room ? 'Ruangan: ' . $product->room : 'Ruangan belum diisi' }}</div>
                                </div>
                                <div class="text-sm-end">
                                    <div class="fw-semibold category-product-stock">{{ $product->stock }}</div>
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