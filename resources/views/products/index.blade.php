@extends('layouts.app')

@section('content')
<div class="card dashboard-card mobile-borderless-card">
    <style>
        .product-thumb { width:48px; height:48px; border-radius:12px; overflow:hidden; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--bs-border-color); background:var(--bs-body-bg); }
        .product-thumb img { width:100%; height:100%; object-fit:cover; }
        .category-badge, .room-badge { min-width:130px; display:inline-flex; align-items:center; justify-content:center; }
        .table-responsive { overflow-x:auto; }
        
        /* Layout Delete Mode Default */
        .delete-mode-cell { min-width:32px; display: flex; align-items: center; }
        td.delete-mode-cell { display: table-cell; }

        /* ===== Checkbox (Mode Hapus) ===== */
        .product-checkbox,
        .select-all-checkbox {
            width: 23px !important;
            height: 23px !important;
            border: 2px solid #4B5563 !important;
            background-color: transparent !important;
            border-radius: 6px !important;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            position: relative;
            margin: 0;
            flex-shrink: 0;
            transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .product-checkbox:hover,
        .select-all-checkbox:hover {
            border-color: #EF4444 !important;
            cursor: pointer;
        }
        .product-checkbox:checked,
        .select-all-checkbox:checked {
            background-color: #EF4444 !important;
            border-color: #EF4444 !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.25), 0 0 8px rgba(239,68,68,0.6);
        }
        .product-checkbox:checked::after,
        .select-all-checkbox:checked::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 45%;
            width: 6px;
            height: 11px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: translate(-50%, -50%) rotate(45deg);
        }
        .product-checkbox:focus-visible,
        .select-all-checkbox:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.3);
        }
        [data-bs-theme="dark"] .product-checkbox,
        [data-bs-theme="dark"] .select-all-checkbox {
            border-color: #4B5563 !important;
        }

        /* ===== Row highlight when selected for delete ===== */
        tr.row-selected-for-delete,
        .mobile-item.row-selected-for-delete {
            background-color: rgba(239,68,68,0.10) !important;
            box-shadow: inset 4px 0 0 0 #EF4444;
            transition: background-color 0.25s ease, box-shadow 0.25s ease;
        }
        tr.row-selected-for-delete td,
        tr.row-selected-for-delete .fw-semibold,
        tr.row-selected-for-delete .text-muted,
        .mobile-item.row-selected-for-delete * {
            color: inherit;
        }

        @media (max-width: 576px) {
            .mobile-borderless-card {
                border: none !important;
                border-radius: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                box-shadow: none !important;
                background-color: transparent !important;
            }
            .mobile-header-pad {
                padding: 0 15px;
            }
            
            .action-toolbar { 
                width: 100%; 
                display: flex;
                flex-wrap: nowrap !important;
                gap: 6px !important;
                overflow-x: auto;
                padding-bottom: 4px;
            }
            .action-toolbar .btn { 
                font-size: 0.78rem !important; 
                padding: 0.35rem 0.6rem !important; 
                flex: 1 1 0;
                justify-content: center;
                white-space: nowrap;
            }
            
            .category-badge, .room-badge { min-width: auto; padding-left: 0.6rem !important; padding-right: 0.6rem !important; font-size: 0.75rem; }
        }

        .back-btn-circle { width: 40px; height: 40px; padding: 0; font-size: 1.1rem; flex-shrink: 0; }
    </style>

    @if(request('from') === 'categories')
        <div class="mb-3 mobile-header-pad">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center back-btn-circle" title="Kembali ke Kategori">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    @endif

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3 mobile-header-pad">
        <div class="position-relative search-box" style="max-width:320px; width:100%;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input id="realtimeSearch" name="search" value="{{ $query ?? '' }}" class="form-control rounded-pill" placeholder="Cari barang..." style="padding-left:2.7rem;" autocomplete="off">
        </div>

        {{-- Ditambahkan justify-content-md-end agar mepet ke kanan di desktop --}}
        <div class="d-flex align-items-center justify-content-md-end gap-2 action-toolbar w-100 w-lg-auto">
            <button type="button" id="delete-mode-toggle" class="btn btn-outline-danger rounded-pill d-flex align-items-center">
                <i class="bi bi-trash me-1"></i>Hapus
            </button>

            <div id="delete-toolbar" class="delete-toolbar d-none d-flex align-items-center gap-1 flex-grow-1 flex-lg-grow-0">
                <button type="button" id="select-all-btn" class="btn btn-outline-secondary rounded-pill btn-sm text-nowrap">Semua</button>
                <button type="button" id="btn-bulk-delete" class="btn btn-danger rounded-pill btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal" disabled>
                    <i class="bi bi-trash me-1"></i>Hapus
                </button>
                <span id="selected-summary" class="text-muted small ms-1 text-nowrap">0 dipilih</span>
            </div>

            <a id="export-btn" href="{{ route('products.export') }}" class="btn btn-success rounded-pill d-flex align-items-center">
                <i class="bi bi-file-earmark-excel-fill me-1"></i>Export
            </a>
            
            <x-primary-button id="add-product-btn" href="{{ route('products.create') }}" class="rounded-pill d-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i>Tambah
            </x-primary-button>
        </div>
    </div>

    {{-- Desktop: Table list --}}
    <div class="d-none d-md-block">
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
                                    <img src="{{ $product->image ?? asset('images/no-image.png') }}" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    @if($product->subcategory)<div class="text-muted small">{{ $product->subcategory }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td><span class="badge category-badge bg-primary-subtle text-primary-emphasis px-3 py-2 rounded-pill text-wrap">{{ $product->category }}</span></td>
                        <td><span class="badge room-badge bg-secondary-subtle text-secondary-emphasis px-3 py-2 rounded-pill text-wrap">{{ $product->room_name ?: 'Belum diisi' }}</span></td>
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
                                @if(auth()->user()->isAdmin())
                                    <li><hr class="dropdown-divider opacity-50"></li>
                                    <li><button type="button" class="dropdown-item py-2 text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}"><i class="bi bi-trash me-2"></i> Hapus</button></li>
                                @endif
                            </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada barang ditemukan.</td></tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: List Item --}}
    <div class="d-md-none border-top mt-2 bg-white">
        @forelse($products as $product)
            <div class="mobile-item py-3 px-3 border-bottom">
                <div class="d-flex gap-2 align-items-start">
                    
                    <div class="delete-mode-cell d-none pt-1">
                        <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}">
                    </div>

                    <div class="product-thumb flex-shrink-0">
                        <img src="{{ $product->image ?? asset('images/no-image.png') }}" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                    </div>
                    
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="text-truncate pe-2">
                                <div class="fw-semibold text-truncate" style="font-size: 0.95rem;">{{ $product->name }}</div>
                                @if($product->subcategory)<div class="text-muted small text-truncate" style="font-size: 0.8rem;">{{ $product->subcategory }}</div>@endif
                            </div>
                            <div class="text-muted small fw-medium flex-shrink-0 mt-1" style="font-size: 0.8rem;">Stok: {{ $product->stock ?? 0 }}</div>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge category-badge bg-primary-subtle text-primary-emphasis px-2 py-1 rounded-pill" style="font-size: 0.72rem;">{{ $product->category }}</span>
                            <span class="badge room-badge bg-secondary-subtle text-secondary-emphasis px-2 py-1 rounded-pill" style="font-size: 0.72rem;">{{ $product->room_name ?: 'Belum diisi' }}</span>
                            @if(($product->stock ?? 0) > 0 && $product->status !== 'inactive')
                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 0.72rem;">Aktif</span>
                            @elseif($product->status === 'inactive')
                                <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill" style="font-size: 0.72rem;">Tidak Aktif</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill" style="font-size: 0.72rem;">Stok Habis</span>
                            @endif
                        </div>
                        
                        <div class="d-flex gap-1">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-secondary flex-fill py-1" style="font-size: 0.78rem;">Lihat</a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary flex-fill py-1" style="font-size: 0.78rem;">Edit</a>
                                <button type="button" class="btn btn-sm btn-outline-danger flex-fill py-1" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}">Hapus</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">Tidak ada barang ditemukan.</div>
        @endforelse
    </div>

    @if(auth()->user()->isAdmin())
        @foreach($products as $product)
            <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content rounded-4">
                        <div class="modal-header border-0">
                            <h5 class="modal-title">Hapus barang?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">Ini akan menghapus {{ $product->name }} dari inventaris.</div>
                        <div class="modal-footer border-0 form-actions">
                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                            <form action="{{ route('products.destroy', $product) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-danger rounded-pill">Hapus</button></form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Hapus semua barang?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin menghapus <span id="bulk-delete-count">0</span> barang?</p>
                </div>
                <div class="modal-footer border-0 form-actions">
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

    <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-4 mobile-header-pad">
        <p class="text-muted mb-0 small">Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }}</p>
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
    const exportButton = document.getElementById('export-btn');
    const addProductButton = document.getElementById('add-product-btn');

    let deleteModeEnabled = false;
    let debounceTimer = null;

    function updateRowHighlight(checkbox) {
        const row = checkbox.closest('tr, .mobile-item');
        if (!row) return;
        row.classList.toggle('row-selected-for-delete', checkbox.checked);
    }

    function syncSelectionState() {
        const selected = Array.from(checkboxes).filter(ch => ch.checked).map(ch => ch.value);
        const count = selected.length;
        const isAllSelected = checkboxes.length > 0 && count === checkboxes.length;

        selectedSummary.textContent = `${count} dipilih`;
        bulkDeleteCount.textContent = count;
        btnBulkDelete.disabled = count === 0;
        if(selectAllCheckbox) selectAllCheckbox.checked = isAllSelected;
        
        if(selectAllBtn) {
            selectAllBtn.textContent = isAllSelected ? 'Batal' : 'Semua';
        }

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
        
        if(deleteModeEnabled) {
            deleteModeToggle.innerHTML = '<i class="bi bi-x-circle me-1"></i>Selesai';
            deleteModeToggle.classList.remove('btn-outline-danger');
            deleteModeToggle.classList.add('btn-danger');
        } else {
            deleteModeToggle.innerHTML = '<i class="bi bi-trash me-1"></i>Hapus';
            deleteModeToggle.classList.remove('btn-danger');
            deleteModeToggle.classList.add('btn-outline-danger');
        }

        deleteToolbar.classList.toggle('d-none', !deleteModeEnabled);
        deleteModeCells.forEach(cell => cell.classList.toggle('d-none', !deleteModeEnabled));

        if (exportButton) {
            exportButton.classList.toggle('d-none', deleteModeEnabled);
        }
        if (addProductButton) {
            addProductButton.classList.toggle('d-none', deleteModeEnabled);
        }

        if (!deleteModeEnabled) {
            checkboxes.forEach(ch => {
                ch.checked = false;
                updateRowHighlight(ch);
            });
            if(selectAllCheckbox) selectAllCheckbox.checked = false;
            syncSelectionState();
        }
    }

    deleteModeToggle?.addEventListener('click', toggleDeleteMode);

    selectAllBtn?.addEventListener('click', function () {
        const allSelected = Array.from(checkboxes).every(ch => ch.checked);
        checkboxes.forEach(ch => {
            ch.checked = !allSelected;
            updateRowHighlight(ch);
        });
        syncSelectionState();
    });

    selectAllCheckbox?.addEventListener('change', function () {
        checkboxes.forEach(ch => {
            ch.checked = this.checked;
            updateRowHighlight(ch);
        });
        syncSelectionState();
    });

    checkboxes.forEach(ch => ch.addEventListener('change', function () {
        updateRowHighlight(this);
        syncSelectionState();
    }));
    syncSelectionState();

    let isUserTyping = false;

    realtimeSearch?.addEventListener('focus', () => {
        isUserTyping = true;
    });

    realtimeSearch?.addEventListener('input', function () {
        if (!isUserTyping) return;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            params.set('search', this.value || '');
            window.location.href = `?${params.toString()}`;
        }, 350);
    });
});
</script>
@endsection