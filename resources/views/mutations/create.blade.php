@extends('layouts.app')

@section('content')
<div class="card dashboard-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Tambah Mutasi Barang</h4>
            <p class="text-muted mb-0">Isi data mutasi untuk barang masuk, keluar, atau pindah ruangan.</p>
        </div>
    </div>

    <form action="{{ route('mutations.store') }}" method="POST" class="row g-4" id="mutationForm">
        @csrf

        <div class="col-md-6">
            <label class="form-label">Barang</label>
            <select name="product_id" class="form-select" required>
                <option value="">Pilih barang</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" @selected(old('product_id') == $item->id)>{{ $item->name }} @if($item->kode_barang) ({{ $item->kode_barang }})@endif</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Jenis Mutasi</label>
            <select name="type" id="mutationType" class="form-select" required>
                <option value="masuk" @selected(old('type') === 'masuk')>Masuk</option>
                <option value="keluar" @selected(old('type') === 'keluar')>Keluar</option>
                <option value="pindah_ruang" @selected(old('type') === 'pindah_ruang')>Pindah Ruang</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Jumlah</label>
            <input type="number" min="1" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" required>
        </div>

        <div class="col-md-4" id="toRoomField">
            <label class="form-label">Ruangan Tujuan</label>
            <select name="to_room_id" id="to_room_id" class="form-select">
                <option value="" selected disabled>Pilih ruangan tujuan</option>
                @if($rooms->isEmpty())
                    <option value="" disabled>Belum ada data ruangan (Tambahkan di menu Ruangan)</option>
                @else
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('to_room_id') == $room->id)>{{ $room->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>

     <div class="col-md-4">
        <label class="form-label">Tanggal Mutasi</label>
        <input type="date" name="mutation_date" class="form-control" value="{{ old('mutation_date', date('Y-m-d')) }}" required>
    </div>

        <div class="col-12">
            <label class="form-label">Catatan</label>
            <textarea name="note" rows="4" class="form-control">{{ old('note') }}</textarea>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 form-actions">
            <a href="{{ route('mutations.index') }}" class="btn btn-secondary rounded-pill">Batal</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Mutasi</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mutationType = document.getElementById('mutationType');
        const toRoomField = document.getElementById('toRoomField');

        function updateToRoomVisibility() {
            if (mutationType.value === 'keluar') {
                toRoomField.style.display = 'none';
                toRoomField.querySelector('select').value = '';
            } else {
                toRoomField.style.display = 'block';
            }
        }

        mutationType.addEventListener('change', updateToRoomVisibility);
        updateToRoomVisibility();
    });
</script>
@endsection
