@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Buat Ruangan</h4>
            <p class="text-muted mb-0">Tambah ruangan penyimpanan baru untuk barang.</p>
        </div>
    </div>

    <form action="{{ route('rooms.store') }}" method="POST" class="row g-4">
        @csrf

        <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Lokasi</label>
            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}">
            @error('location')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Penanggung Jawab</label>
            <input type="text" name="person_in_charge" class="form-control @error('person_in_charge') is-invalid @enderror" value="{{ old('person_in_charge') }}">
            @error('person_in_charge')
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
            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Ruangan</button>
        </div>
    </form>
</div>
@endsection