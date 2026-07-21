@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        {{-- Search Box --}}
        <div class="position-relative search-box" style="max-width: 320px; width: 100%;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text"
                   id="realtimeSearch"
                   name="search"
                   value="{{ $query ?? '' }}"
                   class="form-control rounded-pill"
                   placeholder="Cari barang..."
                   style="padding-left: 2.7rem;"
                   autocomplete="off">
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex align-items-center gap-2">
            <form id="bulk-delete-form" action="{{ route('products.destroySelected') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang yang dipilih?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="selected_ids" id="selected-ids-input">
                <button type="submit" id="btn-bulk-delete" class="btn btn-danger d-none animate__animated animate__fadeIn">
                    <i class="bi bi-trash me-2"></i>Hapus Terpilih (<span id="selected-count">0</span>)
                </button>
            </form>

            <a href="{{ route('products.export') }}" class="btn btn-success d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-excel-fill"></i>Export Excel
            </a>

            <x-primary-button href="{{ route('products.create') }}">
                <i class="bi bi-plus-lg me-2"></i>Tambah Barang
            </x-primary-button>
        </div>
    </div>

    {{-- Table Responsive --}}
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="select-all-checkbox" class="form-check-input" @if($products->isEmpty()) disabled @endif>
                    </th>
                    <th>Nama Barang</th>
                    <th>Model/Tipe</th>
                    <th>Kapasitas</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                @forelse($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="product-thumb">
                                    @if($product->image)
                                        @php
                                            $imageSrc = \Illuminate\Support\Str::startsWith($product->image, ['http://', 'https://']) 
                                                ? $product->image 
                                                : asset('storage/' . ltrim($product->image, '/'));
                                        @endphp
                                        <img src="{{ $imageSrc }}" alt="{{ $product->name }}" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\"bi bi-box2\"></i>';">
                                    @else
                                        <i class="bi bi-box2"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->category }}</td>
                        <td>{{ $product->subcategory }}</td>

                        <td>
                            <span class="fw-medium">{{ $product->stock ?? 0 }}</span> pcs
                        </td>

                        <td>
                            @if(($product->stock ?? 0) > 0)
                                <span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill fw-medium text-capitalize">
                                    {{ $product->status === 'inactive' ? 'Tidak Aktif' : 'Aktif' }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-2.5 py-1.5 rounded-pill fw-medium text-capitalize">
                                    Stok Habis
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="dropdown">
                                <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; font-size: 1.25rem; line-height: 1; border: none;">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow-sm border-0">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('products.show', $product) }}">
                                            <i class="bi bi-eye me-2 text-muted"></i> Lihat Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('products.edit', $product) }}">
                                            <i class="bi bi-pencil me-2 text-muted"></i> Edit Barang
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider opacity-50"></li>
                                    <li>
                                        <button type="button" class="dropdown-item py-2 text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}">
                                            <i class="bi bi-trash me-2"></i> Hapus
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    {{-- Delete Modal --}}
                    <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">Hapus barang?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Ini akan menghapus {{ $product->name }} dari inventaris.
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger rounded-pill">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Tidak ada barang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Container --}}
    <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-4">
        <p class="text-muted mb-0">Menampilkan {{ $products->firstItem() ?? 0 }} sampai {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} barang</p>
        {{ $products->links() }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const btnBulkDelete = document.getElementById('btn-bulk-delete');
        const selectedCountSpan = document.getElementById('selected-count');
        const selectedIdsInput = document.getElementById('selected-ids-input');
        const searchInput = document.getElementById('realtimeSearch');

        function getCheckboxes() {
            return document.querySelectorAll('.product-checkbox');
        }

        function updateBulkDeleteStatus() {
            const checkboxes = getCheckboxes();
            const checkedIds = Array.from(checkboxes)
                .filter(i => i.checked)
                .map(i => i.value);

            selectedCountSpan.textContent = checkedIds.length;
            selectedIdsInput.value = checkedIds.join(',');

            if (checkedIds.length > 0) {
                btnBulkDelete.classList.remove('d-none');
            } else {
                btnBulkDelete.classList.add('d-none');
            }

            if (checkboxes.length > 0 && selectAllCheckbox) {
                selectAllCheckbox.checked = checkedIds.length === checkboxes.length;
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                getCheckboxes().forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateBulkDeleteStatus();
            });
        }

        document.getElementById('productTableBody').addEventListener('change', function (e) {
            if (e.target.classList.contains('product-checkbox')) {
                updateBulkDeleteStatus();
            }
        });

        if (searchInput) {
            let timeout = null;

            searchInput.addEventListener('input', function () {
                clearTimeout(timeout);

                timeout = setTimeout(function () {
                    const keyword = searchInput.value;

                    fetch(`{{ route('products.index') }}?search=${encodeURIComponent(keyword)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newTableBody = doc.getElementById('productTableBody');
                        const currentTableBody = document.getElementById('productTableBody');
                        if (newTableBody && currentTableBody) {
                            currentTableBody.innerHTML = newTableBody.innerHTML;
                        }

                        const newPagination = doc.getElementById('paginationContainer');
                        const currentPagination = document.getElementById('paginationContainer');
                        if (newPagination && currentPagination) {
                            currentPagination.innerHTML = newPagination.innerHTML;
                        }

                        updateBulkDeleteStatus();
                    })
                    .catch(error => console.error('Error fetching search results:', error));
                }, 300);
            });
        }
    });
</script>
@endsection