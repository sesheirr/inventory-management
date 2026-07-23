@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <style>
        .product-thumb { width:48px; height:48px; border-radius:12px; overflow:hidden; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--bs-border-color); background:var(--bs-body-bg); }
        .product-thumb img { width:100%; height:100%; object-fit:cover; }
        .category-badge, .room-badge { min-width:130px; display:inline-flex; align-items:center; justify-content:center; }
        .table-responsive { overflow-x:auto; }
        .delete-mode-cell { min-width:42px; }
        .product-checkbox,
        .select-all-checkbox {
            width: 1.15rem !important;
            height: 1.15rem !important;
            border: 1px solid var(--bs-secondary-border-subtle, rgba(255,255,255,0.6)) !important;
            background-color: var(--bs-body-bg) !important;
            cursor: pointer;
        }
        .product-checkbox:checked,
        .select-all-checkbox:checked {
            background-color: var(--bs-danger) !important;
            border-color: var(--bs-danger) !important;
        }
        [data-bs-theme="dark"] .product-checkbox,
        [data-bs-theme="dark"] .select-all-checkbox {
            border-color: rgba(255,255,255,0.7) !important;
        }
        @media (max-width: 576px) {
            .delete-toolbar { width: 100%; }
            .delete-toolbar .btn,
            .delete-toolbar span {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div class="position-relative search-box" style="max-width:320px; width:100%;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input id="realtimeSearch" name="search" value="{{ $query ?? '' }}" class="form-control rounded-pill" placeholder="Cari barang..." style="padding-left:2.7rem;" autocomplete="off">
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" id="delete-mode-toggle" class="btn btn-outline-danger rounded-pill"><i class="bi bi-trash me-2"></i>Mode Hapus</button>

            <div id="delete-toolbar" class="delete-toolbar d-none d-flex flex-wrap align-items-center gap-2">
                <button type="button" id="select-all-btn" class="btn btn-outline-secondary rounded-pill">Pilih Semua</button>
                <button type="button" id="btn-bulk-delete" class="btn btn-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal" disabled>
                    <i class="bi bi-trash me-2"></i>Hapus Semua
                </button>
                <span id="selected-summary" class="text-muted small">0 barang dipilih</span>
            </div>

            <a href="{{ route('products.export') }}" class="btn btn-success d-flex align-items-center gap-2"><i class="bi bi-file-earmark-excel-fill"></i>Export Excel</a>
            <x-primary-button href="{{ route('products.create') }}"><i class="bi bi-plus-lg me-2"></i>Tambah Barang</x-primary-button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width:42px;" class="delete-mode-cell d-none"><input type="checkbox" id="select-all-checkbox" class="form-check-input select-all-checkbox" @if($products->isEmpty()) disabled @endif></th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Ruangan</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th style="width:50px;"></th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                @forelse($products as $product)
                    <tr>
                        <td class="delete-mode-cell d-none"><input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}"></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="product-thumb">
                                    @if($product->image)
                                        @php
                                            $isRemote = \Illuminate\Support\Str::startsWith($product->image, ['http://','https://']);
                                            $thumbCandidate = 'products/thumbs/' . basename($product->image);
                                            $thumbExists = !$isRemote && file_exists(storage_path('app/public/' . $thumbCandidate));
                                            $imageSrc = $thumbExists ? asset('storage/' . $thumbCandidate) : ($isRemote ? $product->image : asset('storage/' . ltrim($product->image, '/')));
                                        @endphp
                                        <img src="{{ $imageSrc }}" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                                    @else
                                        <i class="bi bi-box2 text-muted fs-5"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    @if($product->subcategory)<div class="text-muted small">{{ $product->subcategory }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td><span class="badge category-badge bg-primary-subtle text-primary-emphasis px-3 py-2 rounded-pill text-wrap">{{ $product->category }}</span></td>
                        <td><span class="badge room-badge bg-secondary-subtle text-secondary-emphasis px-3 py-2 rounded-pill text-wrap">{{ $product->room ?: 'Belum diisi' }}</span></td>
                        <td><span class="fw-semibold">{{ $product->stock ?? 0 }}</span></td>
                        <td>
                            @if(($product->stock ?? 0) > 0 && $product->status !== 'inactive')
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Aktif</span>
                            @elseif($product->status === 'inactive')
                                <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">Tidak Aktif</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">Stok Habis</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow-sm border-0">
                                    <li><a class="dropdown-item py-2" href="{{ route('products.show', $product) }}"><i class="bi bi-eye me-2 text-muted"></i> Lihat Detail</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('products.edit', $product) }}"><i class="bi bi-pencil me-2 text-muted"></i> Edit Barang</a></li>
                                    <li><hr class="dropdown-divider opacity-50"></li>
                                    <li><button type="button" class="dropdown-item py-2 text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}"><i class="bi bi-trash me-2"></i> Hapus</button></li>
                                </ul>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title">Hapus barang?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">Ini akan menghapus {{ $product->name }} dari inventaris.</div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-danger rounded-pill">Hapus</button></form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada barang ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Hapus semua barang?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin menghapus <span id="bulk-delete-count">0</span> barang?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <form id="bulk-delete-form" action="{{ route('products.destroySelected') }}" method="POST">
                        @csrf
                        <div id="selected-ids-container"></div>
                        <button type="submit" class="btn btn-danger rounded-pill">Ya</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-4">
        <p class="text-muted mb-0">Menampilkan {{ $products->firstItem() ?? 0 }} sampai {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} barang</p>
        {{ $products->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModeToggle = document.getElementById('delete-mode-toggle');
    const deleteToolbar = document.getElementById('delete-toolbar');
    const deleteModeCells = document.querySelectorAll('.delete-mode-cell');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const selectAllBtn = document.getElementById('select-all-btn');
    const btnBulkDelete = document.getElementById('btn-bulk-delete');
    const selectedSummary = document.getElementById('selected-summary');
    const bulkDeleteCount = document.getElementById('bulk-delete-count');
    const selectedIdsContainer = document.getElementById('selected-ids-container');
    const realtimeSearch = document.getElementById('realtimeSearch');

    let deleteModeEnabled = false;
    let debounceTimer = null;

    function syncSelectionState() {
        const selected = Array.from(checkboxes).filter(ch => ch.checked).map(ch => ch.value);
        const count = selected.length;
        const isAllSelected = checkboxes.length > 0 && count === checkboxes.length;

        selectedSummary.textContent = `${count} barang dipilih`;
        bulkDeleteCount.textContent = count;
        btnBulkDelete.disabled = count === 0;
        selectAllCheckbox.checked = isAllSelected;
        selectAllBtn.textContent = isAllSelected ? 'Batal Pilih Semua' : 'Pilih Semua';

        selectedIdsContainer.innerHTML = '';
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_ids[]';
            input.value = id;
            selectedIdsContainer.appendChild(input);
        });
    }

    function toggleDeleteMode() {
        deleteModeEnabled = !deleteModeEnabled;
        deleteModeToggle.innerHTML = deleteModeEnabled
            ? '<i class="bi bi-x-circle me-2"></i>Selesai Mode Hapus'
            : '<i class="bi bi-trash me-2"></i>Mode Hapus';

        deleteToolbar.classList.toggle('d-none', !deleteModeEnabled);
        deleteModeCells.forEach(cell => cell.classList.toggle('d-none', !deleteModeEnabled));

        if (!deleteModeEnabled) {
            checkboxes.forEach(ch => ch.checked = false);
            selectAllCheckbox.checked = false;
            syncSelectionState();
        }
    }

    deleteModeToggle?.addEventListener('click', toggleDeleteMode);

    selectAllBtn?.addEventListener('click', function () {
        const allSelected = Array.from(checkboxes).every(ch => ch.checked);
        checkboxes.forEach(ch => ch.checked = !allSelected);
        syncSelectionState();
    });

    selectAllCheckbox?.addEventListener('change', function () {
        checkboxes.forEach(ch => ch.checked = this.checked);
        syncSelectionState();
    });

    checkboxes.forEach(ch => ch.addEventListener('change', syncSelectionState));
    syncSelectionState();

    realtimeSearch?.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const q = encodeURIComponent(this.value || '');
            window.location.href = `?search=${q}`;
        }, 350);
    });
});
</script>

@endsection
