@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h4 class="fw-semibold mb-1">Kategori</h4>
        <p class="text-muted mb-0">Filter dan lihat barang berdasarkan kategori utama.</p>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
        <form action="{{ route('categories.index') }}" method="GET" class="flex-fill">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $query ?? '' }}" class="form-control border-start-0" placeholder="Cari kategori atau barang...">
            </div>
        </form>

        @if(auth()->user()->isAdmin())
            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                + Tambah Kategori
            </button>
        @endif
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category-name" class="form-label">Nama Kategori</label>
                        <input id="category-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($categories->isEmpty())
    <div class="card shadow-sm p-4">
        <div class="d-flex flex-column align-items-center text-center">
            <i class="bi bi-tags fs-1 mb-3 text-muted"></i>
            <h5 class="mb-2">Kategori tidak ditemukan</h5>
            <p class="text-muted mb-0">Coba kata kunci lain atau periksa kembali data kategori.</p>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($categories as $category)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0 category-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $category->name }}</h5>
                                <p class="text-muted mb-0">{{ $category->products_count }} barang</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0" type="button" id="categoryMenu{{ $category->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="categoryMenu{{ $category->id }}">
                            @if(auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('categories.edit', $category) }}">Edit</a></li>
                                <li>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">Hapus</button>
                                    </form>
                                </li>
                            @else
                                <li><span class="dropdown-item text-muted">Akses terbatas</span></li>
                            @endif
                        </ul>
                            </div>
                        </div>

                        @if($category->products->isEmpty())
                            <div class="text-muted">Belum ada barang dalam kategori ini.</div>
                        @else
                            <div class="small text-muted mb-2">Barang dalam kategori</div>
                            <ul class="list-unstyled mb-3">
                                @foreach($category->products->take(3) as $product)
                                    <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span>{{ $product->name }}</span>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $product->stock }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <a href="{{ route('products.index', ['search' => $category->name]) }}" class="mt-auto btn btn-sm btn-outline-primary">Lihat semua</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

@section('styles')
<style>
    .category-card {
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .category-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, .08);
    }

    .category-product-item {
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }

    .category-product-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    html.dark .category-card {
        background: #1F2937;
        border-color: #374151;
    }

    html.dark .category-product-item {
        border-color: #374151;
    }
</style>
@endsection