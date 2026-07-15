@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div class="position-relative search-box">
            <i class="bi bi-search"></i>
            <form action="{{ route('products.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <input type="text" name="search" value="{{ old('search', $query) }}" class="form-control" placeholder="Search products...">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
            </form>
        </div>
        <x-primary-button href="{{ route('products.create') }}">
            <i class="bi bi-plus-lg me-2"></i>Add Item
        </x-primary-button>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th><input type="checkbox" class="form-check-input"></th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th>Edition</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="product-thumb">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <i class="bi bi-box2"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->category }}</td>
                        <td>{{ $product->subcategory }}</td>
                        <td>{{ $product->edition }}</td>
                        <td>
                            <div class="action-icons">
                                <a href="{{ route('products.edit', $product) }}" class="action-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="{{ route('products.show', $product) }}" class="action-icon" title="View"><i class="bi bi-eye"></i></a>
                                <button type="button" class="action-icon" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}" title="More"><i class="bi bi-three-dots"></i></button>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">Delete product?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    This will remove {{ $product->name }} from the inventory.
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger rounded-pill">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <p class="text-muted mb-0">Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</p>
        {{ $products->links() }}
    </div>
</div>
@endsection
