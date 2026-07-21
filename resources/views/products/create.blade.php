@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Buat Barang</h4>
            <p class="text-muted mb-0">Tambah barang baru dengan detail lengkap.</p>
        </div>
    </div>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="row g-4">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Nama Barang</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Model/Tipe</label>
            <input type="text" name="category" class="form-control" value="{{ old('category') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Kapasitas</label>
            <input type="text" name="subcategory" class="form-control" value="{{ old('subcategory') }}">
        </div>

        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jumlah</label>
            <input type="number" min="0" name="stock" class="form-control" value="{{ old('stock', 0) }}" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Gambar</label>
            <input type="file" name="image" class="form-control">
        </div>
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Barang</button>
        </div>
    </form>
</div>
@endsection