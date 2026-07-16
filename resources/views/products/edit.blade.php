@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Edit Barang</h4>
            <p class="text-muted mb-0">Refine the details of this inventory item.</p>
        </div>
        <!-- Tombol Back di sini sudah dihapus -->
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="row g-4">
        @csrf
        @method('PUT')
        <div class="col-md-6">
            <label class="form-label">Nama Perangkat</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Model/Tipe</label>
            <input type="text" name="category" class="form-control" value="{{ old('category', $product->category) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Kapasitas</label>
            <input type="text" name="subcategory" class="form-control" value="{{ old('subcategory', $product->subcategory) }}">
        </div>
        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Kuantiti</label>
            <input type="number" min="0" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                <option value="out_of_stock" @selected(old('status', $product->status) === 'out_of_stock')>Out of Stock</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control">
            @if($product->image)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="Current image" class="img-thumbnail" style="max-height:120px;">
                    <label class="form-check-label ms-2">Remove image</label>
                    <input type="checkbox" name="remove_image" value="1" class="form-check-input ms-2">
                </div>
            @endif
        </div>
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Update Product</button>
        </div>
    </form>
</div>
@endsection