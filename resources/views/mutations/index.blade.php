@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Mutasi Barang</h4>
            <p class="text-muted mb-0">Catat mutasi masuk, keluar, dan pindah ruangan.</p>
        </div>
        <a href="{{ route('mutations.create') }}" class="btn btn-primary rounded-pill"><i class="bi bi-plus-lg me-2"></i>Tambah Mutasi</a>
    </div>

    <div class="card p-4 mb-4 rounded-4 shadow-sm">
        <form method="GET" action="{{ route('mutations.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Dari</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ruangan</label>
                <select name="room_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected($roomId == $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipe</label>
                <select name="type" class="form-select">
                    <option value="">Semua</option>
                    <option value="masuk" @selected($type === 'masuk')>Masuk</option>
                    <option value="keluar" @selected($type === 'keluar')>Keluar</option>
                    <option value="pindah_ruang" @selected($type === 'pindah_ruang')>Pindah Ruang</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cari</label>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama, kode, catatan">
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary rounded-pill">Saring</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Barang</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Dari Ruang</th>
                    <th>Ke Ruang</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutations as $mutation)
                    <tr>
                        <td>{{ optional($mutation->mutation_date)->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $mutation->product->name ?? '-' }}</div>
                            <small class="text-muted">{{ $mutation->product->kode_barang ?? '' }}</small>
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $mutation->type)) }}</td>
                        <td>{{ $mutation->quantity }}</td>
                        <td>{{ $mutation->fromRoom?->name ?? '-' }}</td>
                        <td>{{ $mutation->toRoom?->name ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($mutation->note, 60) }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow-sm border-0">
                                    <li><a class="dropdown-item py-2" href="{{ route('mutations.show', $mutation) }}"><i class="bi bi-eye me-2 text-muted"></i> Lihat</a></li>
                                    <li><hr class="dropdown-divider opacity-50"></li>
                                    <li>
                                        <form action="{{ route('mutations.destroy', $mutation) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">Hapus</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Tidak ada mutasi ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <p class="text-muted mb-0">Menampilkan {{ $mutations->firstItem() ?? 0 }} sampai {{ $mutations->lastItem() ?? 0 }} dari {{ $mutations->total() }} mutasi</p>
        {{ $mutations->links() }}
    </div>
</div>
@endsection
