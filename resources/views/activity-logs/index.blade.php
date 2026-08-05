@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Log Aktivitas</h4>
            <p class="text-muted mb-0">Lihat semua aktivitas sistem oleh seluruh pengguna.</p>
        </div>
    </div>

    <div class="card rounded-4 shadow-sm border-0">
        <div class="card-body">
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Aktivitas</th>
                            <th>Detail</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityLogs as $log)
                            <tr>
                                <td>{{ $log->user?->name ?? 'System' }}</td>
                                <td>{{ ucfirst($log->action) }}</td>
                                <td>{{ $log->description }}</td>
                                <td>{{ $log->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Tidak ada log aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile: activity cards --}}
            <div class="d-md-none">
                <div class="list-group list-group-flush">
                    @forelse($activityLogs as $log)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-semibold">{{ $log->user?->name ?? 'System' }}</div>
                                    <div class="text-muted small">{{ ucfirst($log->action) }}</div>
                                </div>
                                <div class="text-end small text-muted">{{ $log->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                            <div class="mt-2 text-truncate" style="max-width:100%;">{{ $log->description }}</div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted">Tidak ada log aktivitas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
