@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <style>
        /* Room page: adaptive cards for light/dark themes */
        .room-card {
            border-radius: 12px;
            padding: 16px;
            transition: background .2s ease, border-color .2s ease, color .2s ease;
            border: 1px solid transparent;
        }

        .room-products-card {
            border-radius: 12px;
            padding: 12px;
            margin-top: 10px;
            transition: background .15s ease, border-color .15s ease, color .15s ease;
        }

        .room-product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 10px 8px;
            border-radius: 8px;
        }

        .room-product-sub {
            font-size: 0.875rem;
            color: var(--muted);
        }

        /* Light theme */
        [data-bs-theme="light"] .room-card {
            background: var(--card);
            color: var(--text);
            border-color: #e2e8f0;
        }

        [data-bs-theme="light"] .room-products-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #111827;
        }

        [data-bs-theme="light"] .room-product-sub {
            color: #6b7280;
        }

        /* Dark theme */
        [data-bs-theme="dark"] .room-card {
            background: transparent;
            color: #eef2ff;
            border-color: rgba(255,255,255,0.04);
        }

        [data-bs-theme="dark"] .room-products-card {
            background: #1e293b; /* slightly lighter than page bg */
            border: 1px solid rgba(255,255,255,0.06);
            color: #ffffff;
        }

        [data-bs-theme="dark"] .room-product-sub {
            color: #94a3b8;
        }

        /* small screens adjustments */
        @media (max-width: 576px) {
            .room-product-item { flex-direction: column; align-items: flex-start; }
            .room-product-item .text-end { text-align: left; }
        }
    </style>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Ruangan</h4>
            <p class="text-muted mb-0">Lihat dan filter barang berdasarkan ruangan.</p>
        </div>

        <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 w-100 w-sm-auto">
            <form action="{{ route('rooms.index') }}" method="GET" class="flex-fill">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ $query ?? '' }}" class="form-control" placeholder="Cari ruangan...">
                </div>
            </form>
            @if(auth()->user()->isAdmin())
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahRuangan">
                    <i class="fa fa-plus me-2"></i> Tambah Ruangan
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="modal fade" id="modalTambahRuangan" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <form method="POST" action="{{ route('rooms.store') }}">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Tambah Ruangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                       
                        <div class="mb-3">
                            <label class="form-label">Penanggung Jawab</label>
                            <input type="text" name="person_in_charge" class="form-control @error('person_in_charge') is-invalid @enderror" value="{{ old('person_in_charge') }}">
                            @error('person_in_charge')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                       
                    </div>
                    <div class="modal-footer border-0 form-actions">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @if($rooms->isEmpty())
            <div class="col-12 text-center py-5 text-muted">Tidak ada ruangan ditemukan.</div>
        @else
            @foreach($rooms as $room)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="room-card border-0 rounded-4 shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
    <h5 class="mb-1 fw-semibold">{{ $room->name }}</h5>

    <small class="text-muted d-block">
        Penanggung Jawab:
        {{ $room->person_in_charge ?? '-' }}
    </small>

    <small class="text-muted d-block">
        {{ $room->products_count }} Barang
    </small>
</div>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#roomProducts{{ md5($room->name) }}" aria-expanded="false" aria-controls="roomProducts{{ md5($room->name) }}">
                                    <i class="bi bi-eye"></i> Lihat
                                </button>
                            </div>
                        </div>

                        <div class="collapse" id="roomProducts{{ md5($room->name) }}">
                            <div class="room-products-card">
                                @if($room->products->isEmpty())
                                    <div class="text-muted">Belum ada barang di ruangan ini.</div>
                                @else
                                    @foreach($room->products as $p)
                                        <div class="room-product-item">
                                            <div>
                                                <div class="fw-semibold">{{ $p->name }}</div>
                                                <div class="room-product-sub">Kapasitas: {{ $p->subcategory ?? '-' }}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-semibold">{{ $p->stock }}</div>
                                                <div class="room-product-sub">stok</div>
                                            </div>
                                        </div>
                                        @if(! $loop->last)
                                            <hr class="my-2" style="opacity:.06">
                                        @endif
                                    @endforeach
                                @endif

                                @if($room->count > 6)
                                    <div class="mt-3 text-center">
                                        <a href="{{ route('products.index', ['search' => $room->name]) }}" class="btn btn-sm btn-outline-primary rounded-pill">Lihat semua di ruangan ini</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($errors->any())
            var modal = new bootstrap.Modal(document.getElementById('modalTambahRuangan'));
            modal.show();
        @endif
    });
</script>
@endsection