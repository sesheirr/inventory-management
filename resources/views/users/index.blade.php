@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="mb-4">
        <h3 class="fw-bold mb-0">Manajemen User</h3>
        <p class="text-muted mb-0">Kelola role akses pengguna sistem</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama</th>
                        <th>Email</th>
                        <th>Role Saat Ini</th>
                        <th class="pe-4">Ubah Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="ps-4">{{ $user->name }}</td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 {{ $user->role === 'superadmin' ? 'bg-danger-subtle text-danger' : ($user->role === 'admin' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="pe-4">
                                @if($user->id === auth()->id())
                                    <span class="text-muted small fst-italic">Ini akun Anda</span>
                                @elseif($user->isSuperAdmin())
                                    <span class="text-muted small fst-italic">Akun Super Admin tidak dapat diubah di sini</span>
                                @else
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <form method="POST" action="{{ route('users.update-role', $user) }}" class="d-flex gap-2 align-items-center">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" class="form-select form-select-sm" style="width: auto;">
                                                <option value="user" @selected($user->role === 'user')>User</option>
                                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                                <option value="superadmin" @selected($user->role === 'superadmin')>Superadmin</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary"
                                                    onclick="return confirm('Ubah role {{ $user->name }} menjadi peran yang dipilih?')">
                                                Simpan
                                            </button>
                                        </form>

                                        @if($user->id !== auth()->id() && !$user->isSuperAdmin())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus akun {{ $user->name }}? Tindakan ini tidak bisa dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
