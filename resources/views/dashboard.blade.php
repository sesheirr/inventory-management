@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-semibold mb-1">Sistem Informasi Inventarisasi Barang dan Aset</h4>
            <p class="text-muted mb-0">Ringkasan inventaris dan aktivitas terbaru.</p>
        </div>
    </div>

    {{-- Alert Notifikasi Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
        <div class="col">
            <div class="card rounded-4 shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;background:rgba(13,110,253,0.08);">
                        <i class="fa fa-box fa-lg text-primary"></i>
                    </div>
                    <div>
                        <small class="text-muted">Total Barang</small>
                        <div class="fs-4 fw-semibold">{{ $totalProducts }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card rounded-4 shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;background:rgba(25,135,84,0.08);">
                        <i class="fa fa-tags fa-lg text-success"></i>
                    </div>
                    <div>
                        <small class="text-muted">Total Kategori</small>
                        <div class="fs-4 fw-semibold">{{ $totalCategories }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card rounded-4 shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;background:rgba(255,193,7,0.08);">
                        <i class="fa fa-building fa-lg text-warning"></i>
                    </div>
                    <div>
                        <small class="text-muted">Total Ruangan</small>
                        <div class="fs-4 fw-semibold">{{ $totalRooms }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card rounded-4 shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;background:rgba(111,66,193,0.08);">
                        <i class="fa fa-exchange-alt fa-lg text-secondary"></i>
                    </div>
                    <div>
                        <small class="text-muted">Total Mutasi</small>
                        <div class="fs-4 fw-semibold">{{ $totalMutations }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card rounded-4 shadow-sm border-0">
        <div class="card-body">
            {{-- Header Tabel + Tombol Clear History --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Aktivitas Terbaru</h5>
                
                @if(auth()->user()->isSuperAdmin() && $activityLogs->count() > 0)
                    <form action="{{ route('dashboard.clear-history') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh riwayat aktivitas?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                            <i class="fa fa-trash me-1"></i> Clear History
                        </button>
                    </form>
                @endif
            </div>

            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Aktivitas</th>
                            <th>Detail Data</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityLogs as $log)
                            <tr>
                                <td class="d-flex align-items-center gap-2">
                                    <!-- Cek Foto Profil / Avatar -->
                                    @if($log->user && $log->user->avatar)
                                        {{-- Cek apakah avatar berupa URL Cloudinary/Hosting atau file lokal --}}
                                        @php
                                            $avatarUrl = \Illuminate\Support\Str::startsWith($log->user->avatar, ['http://', 'https://'])
                                                ? $log->user->avatar
                                                : asset('storage/' . $log->user->avatar);
                                        @endphp

                                        <img src="{{ $avatarUrl }}" 
                                             alt="{{ $log->user->name }}" 
                                             class="rounded-circle" 
                                             width="36" height="36" 
                                             style="object-fit: cover;">
                                    @else
                                        <!-- Inisial Nama / Default System -->
                                        <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center fw-semibold" style="width:36px;height:36px; font-size: 14px;">
                                            {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="fw-semibold">{{ $log->user?->name ?? 'System' }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if($log->action === 'created')
                                        <span class="badge rounded-pill bg-success-subtle text-success">Created</span>
                                    @elseif($log->action === 'updated')
                                        <span class="badge rounded-pill bg-primary-subtle text-primary">Updated</span>
                                    @elseif($log->action === 'deleted')
                                        <span class="badge rounded-pill bg-danger-subtle text-danger">Deleted</span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary">{{ ucfirst($log->action) }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->description }}</td>
                                <td>{{ $log->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada aktivitas terbaru.</td></tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile recent activity cards --}}
            <div class="d-md-none">
                <div class="list-group list-group-flush">
                    @forelse($activityLogs as $log)
                        <div class="list-group-item">
                            <div class="d-flex align-items-start gap-3 activity-item">
                                @if($log->user && $log->user->avatar)
                                    @php
                                        $avatarUrl = 
                                            
                                            \Illuminate\Support\Str::startsWith($log->user->avatar, ['http://', 'https://'])
                                                ? $log->user->avatar
                                                : asset('storage/' . $log->user->avatar);
                                    @endphp
                                    <img src="{{ $avatarUrl }}" alt="{{ $log->user->name }}" class="rounded-circle" width="44" height="44" style="object-fit:cover;">
                                @else
                                    <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center fw-semibold" style="width:44px;height:44px; font-size: 14px;">
                                        {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                    </div>
                                @endif

                                <div class="flex-grow-1 activity-item-body">
                                    <div class="activity-item-row">
                                        <div class="activity-item-meta">
                                            <div class="fw-semibold">{{ $log->user?->name ?? 'System' }}</div>
                                            <div class="small text-muted">{{ ucfirst($log->action) }}</div>
                                        </div>
                                        <div class="small text-muted activity-item-timestamp">{{ $log->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                    </div>
                                    <div class="mt-2 activity-item-description">{{ $log->description }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted">Tidak ada aktivitas terbaru.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection