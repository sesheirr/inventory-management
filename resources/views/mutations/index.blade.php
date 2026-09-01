@extends('layouts.app')

@section('content')
<div class="card border-0 shadow-sm p-3 p-md-4 rounded-4">
    {{-- Header Section --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Mutasi Barang</h5>
            <small class="text-muted">Kelola riwayat mutasi masuk, keluar, dan pindah ruangan</small>
        </div>
        <div>
            <a href="{{ route('mutations.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i>Tambah Mutasi
            </a>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="p-3 mb-4 rounded-4 filter-panel">
        <form method="GET" action="{{ route('mutations.index') }}" id="filterForm">

            {{-- Baris Utama: Pencarian di Kiri & Tombol Filter di Kanan --}}
            <div class="d-flex gap-3 align-items-center flex-wrap flex-md-nowrap">

                {{-- SEARCH BAR --}}
                <div class="position-relative search-box flex-grow-1" style="width:100%;">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="realtimeSearch" name="search" value="{{ $search ?? '' }}"
                           class="form-control rounded-pill ps-5 bg-transparent"
                           placeholder="Cari barang, kode, atau catatan..." autocomplete="off">
                </div>

                {{-- Tombol Toggle Filter Dropdown & Reset (di kanan) --}}
                <div class="d-flex align-items-center gap-2">
                    @php
                        $isFiltered = ($dateFrom ?? '') || ($dateTo ?? '') || ($categoryId ?? '') || ($roomId ?? '') || ($type ?? '') || ($status ?? '');
                    @endphp

                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-1 text-nowrap py-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#advancedFilter"
                            aria-expanded="{{ $isFiltered ? 'true' : 'false' }}"
                            aria-controls="advancedFilter"
                            id="filterToggleBtn">
                        <i class="bi bi-funnel"></i>
                        <span id="filterToggleText">{{ $isFiltered ? 'Sembunyikan Filter' : 'Filter' }}</span>
                        <i class="bi {{ $isFiltered ? 'bi-chevron-up' : 'bi-chevron-down' }} ms-1" id="filterChevron"></i>
                    </button>

                    <a href="{{ route('mutations.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 d-flex align-items-center justify-content-center" title="Reset Data">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>

            {{-- Bagian Pilihan Filter (Muncul di Bawah saat Tombol Filter Diklik) --}}
            <div class="collapse {{ $isFiltered ? 'show' : '' }} mt-3" id="advancedFilter">
                <div class="row g-2 pt-3 border-top border-secondary-subtle">
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted small fw-medium mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm trigger-auto-submit" title="Dari Tanggal">
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted small fw-medium mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm trigger-auto-submit" title="Sampai Tanggal">
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted small fw-medium mb-1">Kategori</label>
                        <select name="category_id" class="form-select form-select-sm trigger-auto-submit">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(($categoryId ?? '') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted small fw-medium mb-1">Ruangan</label>
                        <select name="room_id" class="form-select form-select-sm trigger-auto-submit">
                            <option value="">Semua Ruangan</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected(($roomId ?? '') == $room->id)>{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted small fw-medium mb-1">Tipe Mutasi</label>
                        <select name="type" class="form-select form-select-sm trigger-auto-submit">
                            <option value="">Semua Tipe</option>
                            <option value="masuk" @selected(($type ?? '') === 'masuk')>Masuk</option>
                            <option value="keluar" @selected(($type ?? '') === 'keluar')>Keluar</option>
                            <option value="pindah_ruang" @selected(($type ?? '') === 'pindah_ruang')>Pindah Ruang</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted small fw-medium mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm trigger-auto-submit">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(($status ?? '') === 'pending')>Pending</option>
                            <option value="approved" @selected(($status ?? '') === 'approved')>Approved</option>
                            <option value="rejected" @selected(($status ?? '') === 'rejected')>Rejected</option>
                        </select>
                    </div>
                </div>
            </div>

        </form>
    </div>

    {{-- Data Table (desktop) --}}
    <div class="d-none d-md-block">
        {{-- overflow-visible agar dropdown tidak terpotong atau tersembunyi --}}
        <div class="table-responsive overflow-visible pb-4">
            <table class="table table-sm align-middle table-hover small mb-0">
            <thead>
                <tr class="border-bottom border-secondary-subtle">
                    <th scope="col" class="py-2 text-muted">Tanggal</th>
                    <th scope="col" class="py-2 text-muted">Barang</th>
                    <th scope="col" class="py-2 text-muted">Tipe</th>
                    <th scope="col" class="py-2 text-center text-muted">Qty</th>
                    <th scope="col" class="py-2 text-muted">Dari</th>
                    <th scope="col" class="py-2 text-muted">Ke</th>
                    <th scope="col" class="py-2 text-muted">Catatan</th>
                    <th scope="col" class="py-2 text-muted">Status</th>
                    <th scope="col" class="py-2 text-end text-muted">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutations as $mutation)
                    <tr class="border-bottom border-secondary-subtle">
                        <td class="text-nowrap text-muted">{{ optional($mutation->mutation_date)->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $mutation->product->name ?? '-' }}</div>
                        </td>
                        <td>
                            @switch($mutation->type)
                                @case('masuk')
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 rounded-pill px-3 py-1 fw-semibold">
                                        <i class="bi bi-box-arrow-in-down me-1"></i>Masuk
                                    </span>
                                    @break
                                @case('keluar')
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill px-3 py-1 fw-semibold">
                                        <i class="bi bi-box-arrow-up me-1"></i>Keluar
                                    </span>
                                    @break
                                @case('pindah_ruang')
                                    <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 rounded-pill px-3 py-1 fw-semibold">
                                        <i class="bi bi-arrow-left-right me-1"></i>Pindah
                                    </span>
                                    @break
                                @default
                                    <span class="badge bg-secondary bg-opacity-25 text-body border border-secondary rounded-pill px-3 py-1 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $mutation->type)) }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="fw-semibold text-center">{{ $mutation->quantity }}</td>
                        <td class="text-muted">{{ $mutation->fromRoom?->name ?? '-' }}</td>
                        <td class="text-muted">{{ $mutation->toRoom?->name ?? '-' }}</td>
                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 140px;" title="{{ $mutation->note }}">
                                {{ $mutation->note ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @switch($mutation->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark rounded-pill px-2"><i class="bi bi-clock me-1"></i>Pending</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success rounded-pill px-2"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger rounded-pill px-2"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary rounded-pill px-2">{{ ucfirst($mutation->status) }}</span>
                            @endswitch

                            {{-- Tombol Alasan Penolakan --}}
                            @if($mutation->status === 'rejected')
                                <div class="mt-1">
                                    <button type="button" 
                                            class="btn btn-link p-0 text-danger border-0 text-decoration-none d-inline-flex align-items-center" 
                                            style="font-size: 0.725rem;" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#reasonModal{{ $mutation->id }}">
                                        <i class="bi bi-info-circle me-1"></i>Alasan
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                    <form action="{{ route('mutations.approve', $mutation) }}" method="POST" class="m-0"
                                          onsubmit="return confirm('Setujui mutasi ini? Stok/lokasi barang akan diperbarui.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-xs btn-success rounded-pill px-2 py-1 d-inline-flex align-items-center" style="font-size: 0.75rem;" title="Setujui">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 d-inline-flex align-items-center" style="font-size: 0.75rem;"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal{{ $mutation->id }}" title="Tolak">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @endif

                                <div class="dropdown">
                                    <button class="btn btn-sm border-0 rounded-circle p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="line-height: 1;">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow border border-secondary-subtle small">
                                        <li>
                                            <a class="dropdown-item py-1" href="{{ route('mutations.show', $mutation) }}">
                                                <i class="bi bi-eye me-2 text-muted"></i> Lihat Detail
                                            </a>
                                        </li>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                            <li><hr class="dropdown-divider opacity-50 my-1"></li>
                                            <li>
                                                <form action="{{ route('mutations.destroy', $mutation) }}" method="POST" class="m-0"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data mutasi ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item py-1 text-danger">
                                                        <i class="bi bi-trash me-2"></i> Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted small">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Tidak ada data mutasi yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile: Card list --}}
    <div class="d-md-none">
        <div class="row g-3">
            @forelse($mutations as $mutation)
                <div class="col-12">
                    <div class="card border rounded-3 p-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $mutation->product->name ?? '-' }}</div>
                                <div class="text-muted small">{{ optional($mutation->mutation_date)->format('d/m/Y') }} • {{ $mutation->product->kode_barang ?? '' }}</div>
                            </div>
                            
                            {{-- Dropdown Aksi untuk Mobile --}}
                            <div class="dropdown">
                                <button class="btn btn-sm border-0 rounded-circle p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="line-height: 1;">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow border border-secondary-subtle small">
                                    <li>
                                        <a class="dropdown-item py-1" href="{{ route('mutations.show', $mutation) }}">
                                            <i class="bi bi-eye me-2 text-muted"></i> Lihat Detail
                                        </a>
                                    </li>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                        <li><hr class="dropdown-divider opacity-50 my-1"></li>
                                        <li>
                                            <form action="{{ route('mutations.destroy', $mutation) }}" method="POST" class="m-0"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data mutasi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-1 text-danger">
                                                    <i class="bi bi-trash me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-secondary bg-opacity-25 text-body border border-secondary rounded-pill">Qty: {{ $mutation->quantity }}</span>
                            @switch($mutation->type)
                                @case('masuk')
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 rounded-pill">Masuk</span>
                                    @break
                                @case('keluar')
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill">Keluar</span>
                                    @break
                                @case('pindah_ruang')
                                    <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 rounded-pill">Pindah</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary bg-opacity-25 text-body border border-secondary rounded-pill">{{ ucfirst(str_replace('_',' ',$mutation->type)) }}</span>
                            @endswitch
                            @switch($mutation->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success rounded-pill">Approved</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger rounded-pill">Rejected</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary rounded-pill">{{ ucfirst($mutation->status) }}</span>
                            @endswitch
                        </div>

                        <div class="mt-2 d-flex flex-column gap-1 small text-muted">
                            <div>Dari: <strong>{{ $mutation->fromRoom?->name ?? '-' }}</strong></div>
                            <div>Ke: <strong>{{ $mutation->toRoom?->name ?? '-' }}</strong></div>
                        </div>

                        @if(!empty($mutation->note))
                            <div class="mt-2 text-muted small">Catatan: {{ $mutation->note }}</div>
                        @endif

                        @if(!empty($mutation->rejection_note))
                            <div class="mt-2 text-danger small">
                                <strong>Alasan Penolakan:</strong> {{ $mutation->rejection_note }}
                            </div>
                        @endif

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                <form action="{{ route('mutations.approve', $mutation) }}" method="POST" class="m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">Setujui</button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $mutation->id }}">Tolak</button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4 text-muted small">Tidak ada data mutasi yang ditemukan.</div>
            @endforelse
        </div>
    </div>

    {{-- Footer Pagination --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 pt-2 gap-2 border-top border-secondary-subtle">
        <p class="text-muted small mb-0" style="font-size: 0.8rem;">
            Menampilkan {{ $mutations->firstItem() ?? 0 }} - {{ $mutations->lastItem() ?? 0 }} dari {{ $mutations->total() }} data
        </p>
        <div>
            {{ $mutations->links() }}
        </div>
    </div>
</div>

{{-- Modals Container --}}
@foreach($mutations as $mutation)
    @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
        <div class="modal fade" id="rejectModal{{ $mutation->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm modal-dialog-scrollable">
                <div class="modal-content rounded-4 border border-secondary-subtle shadow">
                    <form action="{{ route('mutations.reject', $mutation) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-semibold">Tolak Pengajuan</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-2">
                            <p class="small text-muted mb-2">Barang: <strong>{{ $mutation->product->name ?? '-' }}</strong></p>
                            <div class="mb-2">
                                <label class="form-label small fw-medium mb-1">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="rejection_note" class="form-control form-control-sm" rows="3" required placeholder="Tulis alasan singkat..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 form-actions">
                            <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Tolak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($mutation->status === 'rejected')
        <div class="modal fade" id="reasonModal{{ $mutation->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content rounded-4 border border-danger-subtle shadow">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-semibold text-danger d-flex align-items-center gap-1">
                            <i class="bi bi-exclamation-triangle-fill"></i> Alasan Penolakan
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="small text-muted mb-2">Barang: <strong>{{ $mutation->product->name ?? '-' }}</strong></p>
                        <div class="p-3 bg-danger-subtle text-danger-emphasis rounded-3 border border-danger-subtle small">
                            {{ $mutation->rejection_note ?? 'Tidak ada catatan alasan penolakan yang dicantumkan.' }}
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('styles')
<style>
.filter-panel {
    background-color: transparent;
}
.filter-panel .form-control,
.filter-panel .form-select {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
}
.filter-panel .form-control:focus,
.filter-panel .form-select:focus {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterCollapse = document.getElementById('advancedFilter');
    const filterToggleText = document.getElementById('filterToggleText');
    const filterChevron = document.getElementById('filterChevron');

    if (filterCollapse) {
        filterCollapse.addEventListener('show.bs.collapse', function () {
            filterToggleText.textContent = 'Sembunyikan Filter';
            filterChevron.classList.remove('bi-chevron-down');
            filterChevron.classList.add('bi-chevron-up');
        });
        filterCollapse.addEventListener('hide.bs.collapse', function () {
            filterToggleText.textContent = 'Filter';
            filterChevron.classList.remove('bi-chevron-up');
            filterChevron.classList.add('bi-chevron-down');
        });
    }

    const filterForm = document.getElementById('filterForm');
    const realtimeSearch = document.getElementById('realtimeSearch');
    const baseUrl = "{{ route('mutations.index') }}";

    function updateResults() {
        if (!filterForm) return;

        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            if (value !== null && value !== '') {
                params.append(key, value);
            }
        }

        const queryString = params.toString();
        window.location.href = queryString ? `${baseUrl}?${queryString}` : baseUrl;
    }

    let debounceTimer = null;
    let isUserTyping = false;

    realtimeSearch?.addEventListener('focus', function () {
        isUserTyping = true;
        const val = realtimeSearch.value;
        realtimeSearch.value = '';
        realtimeSearch.value = val;
    });

    realtimeSearch?.addEventListener('input', function () {
        if (!isUserTyping) return;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateResults, 350);
    });

    const autoSubmitElements = document.querySelectorAll('.trigger-auto-submit');
    autoSubmitElements.forEach(function (element) {
        element.addEventListener('change', updateResults);
    });
});
</script>
@endpush
@endsection