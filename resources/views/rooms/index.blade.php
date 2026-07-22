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

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Ruangan</h4>
            <p class="text-muted mb-0">Lihat dan filter barang berdasarkan ruangan (diambil dari data barang).</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('rooms.index') }}" method="GET" class="position-relative search-box" style="max-width: 420px; width: 100%;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="search" value="{{ $query ?? '' }}" class="form-control rounded-pill" placeholder="Cari ruangan..." style="padding-left: 2.7rem;">
            </form>
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
                                <small class="text-muted">{{ $room->count }} barang</small>
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
@endsection