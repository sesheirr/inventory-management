@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Kategori</h4>
            <p class="text-muted mb-0">Kelola kategori barang inventaris Anda.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('categories.index') }}" method="GET" class="position-relative search-box" style="max-width: 320px; width: 100%;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="search" value="{{ $query ?? '' }}" class="form-control rounded-pill" placeholder="Cari kategori..." style="padding-left: 2.7rem;">
            </form>
            <a href="{{ route('categories.create') }}" class="btn btn-primary rounded-pill">
                <i class="bi bi-plus-lg me-2"></i>Tambah Kategori
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description }}</td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow-sm border-0">
                                    <li><a class="dropdown-item py-2" href="{{ route('categories.show', $category) }}"><i class="bi bi-eye me-2"></i>Lihat</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('categories.edit', $category) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li><hr class="dropdown-divider opacity-50"></li>
                                    <li>
                                        <button type="button" class="dropdown-item py-2 text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $category->id }}">
                                            <i class="bi bi-trash me-2"></i>Hapus
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title">Hapus kategori?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Ini akan menghapus kategori "{{ $category->name }}".
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger rounded-pill">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">Tidak ada kategori ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <p class="text-muted mb-0">Menampilkan {{ $categories->firstItem() ?? 0 }} sampai {{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</p>
        {{ $categories->links() }}
    </div>
</div>
@endsection