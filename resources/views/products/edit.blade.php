@extends('layouts.app')

@section('content')
<div class="card dashboard-card mobile-borderless-card">
    <style>
        .back-btn-circle { width: 40px; height: 40px; padding: 0; font-size: 1.1rem; flex-shrink: 0; position: relative; z-index: 1050 !important; pointer-events: auto; }

        @media (max-width: 576px) {
            .mobile-borderless-card {
                border: none !important;
                border-radius: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                box-shadow: none !important;
                background-color: transparent !important;
            }
        }
    </style>

    <div class="d-flex align-items-center gap-3 mb-4 px-3 px-md-0">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-circle d-inline-flex d-md-none align-items-center justify-content-center back-btn-circle" title="Kembali">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-semibold mb-1">Edit Barang</h4>
            <p class="text-muted mb-0">Perbarui detail barang ini.</p>
        </div>
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="row g-4 px-2 px-md-0">
        @csrf
        @method('PUT')

        <div class="col-md-6">
            <label class="form-label">Nama Barang</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select" required>
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Kapasitas</label>
            <input type="text" name="subcategory" class="form-control" value="{{ old('subcategory', $product->subcategory) }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Ruangan</label>
            <select name="room_id" class="form-select" required>
                <option value="" selected disabled>Pilih ruangan</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" @selected(old('room_id', $product->room_id) == $room->id)>{{ $room->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Jumlah</label>
            <input type="number" min="0" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="active" @selected(old('status', $product->status) === 'active')>Aktif</option>
                <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Tidak Aktif</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Gambar</label>
            <input type="file" name="image" class="form-control">
            @if($product->image)
                <div class="mt-2">
                    <img src="{{ $product->image }}" alt="Gambar saat ini" class="img-thumbnail" style="max-height:120px;" onerror="this.onerror=null; this.src='https://placehold.co/120x120?text=No+Image';">
                    <label class="form-check-label ms-2">Hapus gambar</label>
                    <input type="checkbox" name="remove_image" value="1" class="form-check-input ms-2">
                </div>
            @endif
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 form-actions mt-4">
            <button type="submit" class="btn btn-primary rounded-pill px-4 w-100 w-md-auto">Perbarui Barang</button>
        </div>
    </form>
</div>
@endsection