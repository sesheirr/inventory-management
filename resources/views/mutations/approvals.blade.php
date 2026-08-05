@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold">Approval Mutasi</h4>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <div class="card rounded-4 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Barang</th>
                            <th>Ruangan Asal</th>
                            <th>Ruangan Tujuan</th>
                            <th>Pengaju</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutations as $mutation)
                            <tr>
                                <td>{{ $mutation->product?->name }}</td>
                                <td>{{ $mutation->fromRoom?->name ?? '-' }}</td>
                                <td>{{ $mutation->toRoom?->name ?? '-' }}</td>
                                <td>{{ $mutation->user?->name }}</td>
                                <td>{{ $mutation->mutation_date->format('d/m/Y') }}</td>
                                <td><span class="badge {{ $mutation->statusBadgeClass() }}">{{ $mutation->statusLabel() }}</span></td>
                                <td>
                                    @if($mutation->status === 'pending')
                                        <form class="d-inline" method="POST" action="{{ route('mutations.approve', $mutation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                        </form>

                                        <button class="btn btn-sm btn-danger ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $mutation->id }}">Reject</button>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $mutation->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('mutations.reject', $mutation) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Tolak Mutasi</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Alasan Penolakan</label>
                                                                <textarea name="rejection_note" class="form-control" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Tidak ada permintaan mutasi yang menunggu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
