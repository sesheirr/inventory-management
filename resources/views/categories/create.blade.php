@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Buat Kategori</h4>
            <p class="text-muted mb-0">Tambah kategori baru untuk barang.</p>
        </div>
    </div>

    <form action="{{ route('categories.store') }}" method="POST" class="row g-4">
        @csrf

        <div class="col-12">
            <label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Kategori</button>
        </div>
    </form>
</div>
@endsection