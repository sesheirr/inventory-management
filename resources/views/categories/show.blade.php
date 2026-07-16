@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Category Details</h4>
            <p class="text-muted mb-0">Review category information.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="stat-box p-4">
                <span class="text-muted">Name</span>
                <div class="fw-semibold">{{ $category->name }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-box p-4">
                <span class="text-muted">Products Count</span>
                <div class="fw-semibold">{{ $category->products->count() }}</div>
            </div>
        </div>
        <div class="col-12">
            <div class="stat-box p-4">
                <span class="text-muted">Description</span>
                <div class="fw-semibold">{{ $category->description }}</div>
            </div>
        </div>
    </div>
</div>
@endsection