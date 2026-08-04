@extends('layouts.app')

@section('content')
<div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 bg-dark text-white">
    {{-- Header Section --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1 text-white">Notifikasi</h5>
            <small class="text-secondary">Pemberitahuan aktivitas dan pengajuan mutasi barang</small>
        </div>
        
        @if(auth()->user()->unreadNotifications->count() > 0)
            <div>
                <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="m-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Filter Nav Tabs --}}
    <div class="border-bottom border-secondary border-opacity-25 mb-3">
        <ul class="nav nav-tabs border-0 gap-2">
            <li class="nav-item">
                <a class="nav-link border-0 text-white {{ request('filter') != 'unread' ? 'active bg-transparent fw-bold border-bottom border-primary border-2 text-primary' : 'text-secondary' }}" 
                   href="{{ route('notifications.index') }}">
                    Semua <span class="badge bg-secondary bg-opacity-25 ms-1 text-white">{{ auth()->user()->notifications->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link border-0 text-white {{ request('filter') == 'unread' ? 'active bg-transparent fw-bold border-bottom border-primary border-2 text-primary' : 'text-secondary' }}" 
                   href="{{ route('notifications.index', ['filter' => 'unread']) }}">
                    Belum Dibaca <span class="badge bg-danger ms-1">{{ auth()->user()->unreadNotifications->count() }}</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- Notification List Container --}}
    <div class="d-flex flex-column gap-2">
        @forelse($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $data = $notification->data;
            @endphp
            
            <div class="p-3 rounded-4 border border-secondary border-opacity-25 d-flex align-items-start justify-content-between gap-3 transition-all {{ $isUnread ? 'bg-primary bg-opacity-10' : 'bg-dark bg-opacity-50' }}">
                <div class="d-flex align-items-start gap-3">
                    {{-- Icon Berdasarkan Tipe Notifikasi --}}
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 mt-1" 
                         style="width: 38px; height: 38px; background-color: {{ isset($data['type']) && $data['type'] == 'rejected' ? 'rgba(220, 53, 69, 0.2)' : 'rgba(13, 110, 253, 0.2)' }};">
                        @if(isset($data['type']) && $data['type'] == 'rejected')
                            <i class="bi bi-x-circle text-danger fs-5"></i>
                        @elseif(isset($data['type']) && $data['type'] == 'approved')
                            <i class="bi bi-check-circle text-success fs-5"></i>
                        @else
                            <i class="bi bi-bell text-primary fs-5"></i>
                        @endif
                    </div>

                    {{-- Isi Pesan --}}
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-semibold text-white small">{{ $data['title'] ?? 'Notifikasi Sistem' }}</span>
                            @if($isUnread)
                                <span class="badge bg-primary rounded-circle p-1" title="Belum dibaca"><span class="visually-hidden">Unread</span></span>
                            @endif
                        </div>
                        <p class="mb-1 text-secondary small" style="line-height: 1.4;">
                            {{ $data['message'] ?? '-' }}
                        </p>
                        <small class="text-secondary font-monospace" style="font-size: 0.75rem;">
                            <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-2">
                    @if(isset($data['url']))
                        <a href="{{ $data['url'] }}" class="btn btn-xs btn-outline-light rounded-pill px-2 py-1" style="font-size: 0.75rem;" title="Lihat Detail">
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif

                    @if($isUnread)
                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1" style="font-size: 0.75rem;" title="Tandai dibaca">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-outline-danger border-0 rounded-circle p-1" style="font-size: 0.85rem;" title="Hapus">
                            <i class="bi bi-trash text-secondary hover-danger"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-secondary">
                <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <p class="mb-0 small">Belum ada notifikasi saat ini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if(method_exists($notifications, 'links'))
        <div class="d-flex justify-content-end mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection