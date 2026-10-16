@extends('layouts.app')

@section('page-title', 'Ruangan')
@section('page-subtitle', 'Lokasi penempatan & penanggung jawab aset')

@section('content')
@php
    $picList = collect($picList ?? []);
    $activePic = $pic ?? '';
@endphp
<div class="space-y-4 sm:space-y-6" data-framer-page>
    {{-- Header & Controls --}}
    <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-xs space-y-3.5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">Ruangan</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">Lokasi penempatan & penanggung jawab</p>
            </div>

            @if(auth()->user()->isAdmin())
                <button type="button" data-modal-target="modalTambahRuangan" 
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/20 transition-all shrink-0 cursor-pointer">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah</span>
                </button>
            @endif
        </div>

        <form action="{{ route('rooms.index') }}" method="GET" id="roomFilterForm" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                <input type="search" name="search" value="{{ $query ?? '' }}" placeholder="Cari ruangan, barang, atau PJ..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors" autocomplete="off">
            </div>

            {{-- Filter by Penanggung Jawab Dropdown --}}
            <div class="w-full sm:w-56">
                <select name="pic" id="roomPicFilterSelect" onchange="this.form.submit()"
                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    <option value="">Semua Penanggung Jawab</option>
                    @foreach($picList as $picOption)
                        <option value="{{ $picOption }}" @selected(mb_strtolower($activePic) === mb_strtolower($picOption))>
                            PJ: {{ $picOption }}
                        </option>
                    @endforeach
                    <option value="__unassigned__" @selected($activePic === '__unassigned__')>— Belum Ada PJ —</option>
                </select>
            </div>

            @if(($query ?? '') !== '' || $activePic !== '')
                <a href="{{ route('rooms.index') }}" class="px-3 py-2 rounded-xl inline-flex items-center justify-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors shrink-0" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span class="sm:hidden">Reset</span>
                </a>
            @endif
        </form>

        {{-- Quick Filter Pills by Penanggung Jawab --}}
        @if($picList->isNotEmpty())
            <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-1.5 overflow-x-auto pb-0.5">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 shrink-0 mr-1">
                    PJ:
                </span>
                <a href="{{ route('rooms.index', array_filter(['search' => $query ?? null])) }}"
                   class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap shrink-0 transition-all {{ $activePic === '' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    Semua ({{ $picList->count() }})
                </a>
                @foreach($picList as $picOption)
                    @php
                        $isPicSelected = mb_strtolower($activePic) === mb_strtolower($picOption);
                    @endphp
                    <a href="{{ route('rooms.index', array_filter(['search' => $query ?? null, 'pic' => $isPicSelected ? null : $picOption])) }}"
                       class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap shrink-0 transition-all {{ $isPicSelected ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-slate-700' }}">
                        {{ $picOption }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Room Cards Grid --}}
    @if($rooms->isEmpty())
        <div id="roomsEmptyMessage" class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-10 text-center text-slate-400 dark:text-slate-500 shadow-xs">
            <i class="bi bi-building text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Ruangan Tidak Ditemukan</h3>
            <p class="text-xs mt-1">Gunakan filter lain atau tambahkan ruangan baru.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-5" id="roomsGrid" data-framer-stagger>
            @foreach($rooms as $room)
                <div class="room-card-col group rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-blue-500/40 transition-all duration-200 p-4 sm:p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base font-bold shrink-0 group-hover:scale-105 transition-transform">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('rooms.show', $room) }}" class="block text-sm sm:text-base font-extrabold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 leading-tight transition-colors truncate">
                                        {{ $room->name }}
                                    </a>
                                    <div class="text-[11px] font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ $room->products_count }} barang</div>
                                </div>
                            </div>

                            @if(auth()->user()->isAdmin())
                                <div class="relative shrink-0">
                                    <button type="button" data-dropdown-toggle="room-dropdown-{{ $room->id }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                                        <i class="bi bi-three-dots-vertical text-sm"></i>
                                    </button>
                                    <div id="room-dropdown-{{ $room->id }}" data-dropdown-menu class="hidden absolute right-0 mt-1 w-36 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-1 z-20 text-left">
                                        <a href="{{ route('rooms.show', $room) }}" class="flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                            <i class="bi bi-eye text-slate-400"></i> Detail
                                        </a>
                                        <a href="{{ route('rooms.edit', $room) }}" class="flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                            <i class="bi bi-pencil text-slate-400"></i> Edit
                                        </a>
                                        <button type="button" class="delete-room-btn w-full flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer"
                                                data-room-name="{{ $room->name }}" 
                                                data-delete-url="{{ route('rooms.destroy', $room) }}">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Penanggung Jawab Info --}}
                        <div class="flex items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400 mb-2.5 bg-slate-50 dark:bg-slate-900/40 px-3 py-2 rounded-xl border border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i class="bi bi-person-badge text-blue-500 shrink-0"></i>
                                <span class="truncate">PJ: <strong class="text-slate-700 dark:text-slate-200">{{ $room->person_in_charge ?: 'Belum ditentukan' }}</strong></span>
                            </div>
                            @if($room->person_in_charge)
                                <a href="{{ route('rooms.index', ['pic' => $room->person_in_charge]) }}"
                                   class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline shrink-0">
                                    Filter
                                </a>
                            @endif
                        </div>

                        {{-- Product List inside Room --}}
                        @if($room->products->isEmpty())
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 italic py-1.5">Belum ada barang.</p>
                        @else
                            <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                @foreach($room->products->take(3) as $p)
                                    <a href="{{ route('products.show', $p) }}" class="flex items-center justify-between text-xs py-1 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        <span class="text-slate-700 dark:text-slate-300 truncate pr-2 hover:underline">{{ $p->name }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0 tabular-nums">
                                            {{ $p->stock }} unit
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 mt-3 flex items-center gap-2">
                        <a href="{{ route('products.index', ['room_id' => $room->id]) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50/80 dark:bg-amber-950/30 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                            <span>Lihat Barang</span>
                            <i class="bi bi-arrow-right text-[10px]"></i>
                        </a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('rooms.edit', $room) }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors shrink-0" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Room Modal (Desktop Centered / Mobile Bottom Sheet) --}}
<div id="modalTambahRuangan" class="modal-container responsive-bottom-sheet fixed inset-0 z-50 {{ ($errors->has('name') || $errors->has('person_in_charge')) ? 'flex' : 'hidden' }} items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="sheet-dialog relative w-full sm:max-w-md rounded-t-3xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-6 max-h-[90vh] overflow-y-auto animate-fade-in">
        <div class="sheet-grab-handle w-11 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto mb-3 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <div>
                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Tambah Ruangan</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Nama ruangan & penanggung jawab unik</p>
            </div>
            <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>

        <form action="{{ route('rooms.store') }}" method="POST" id="modalRoomCreateForm" class="space-y-4">
            @csrf
            <input type="hidden" name="pic_mode" id="modalPicModeInput" value="{{ old('pic_mode', 'select') }}">

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nama Ruangan <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                       class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                       placeholder="Contoh: Ruang Server & NOC">
                @error('name')<p class="text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Penanggung Jawab: Existing Dropdown OR Manual Unique Input --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Penanggung Jawab
                    </label>
                    <button type="button" id="modalTogglePicModeBtn"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 transition-colors cursor-pointer">
                        <i class="bi bi-plus-circle" id="modalTogglePicIcon"></i>
                        <span id="modalTogglePicText">Tambah Baru</span>
                    </button>
                </div>

                {{-- Mode 1: Select from Existing Unique PICs --}}
                <div id="modalPicSelectWrap" class="{{ old('pic_mode', 'select') === 'manual' ? 'hidden' : 'block' }} space-y-1">
                    <select name="person_in_charge" id="modalPicSelect"
                            class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="">— Pilih Penanggung Jawab —</option>
                        @foreach($picList as $picOption)
                            <option value="{{ $picOption }}" @selected(old('person_in_charge') === $picOption)>{{ $picOption }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Mode 2: Manual Input with Live Uniqueness Verification --}}
                <div id="modalPicManualWrap" class="{{ old('pic_mode', 'select') === 'manual' ? 'block' : 'hidden' }} space-y-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-blue-200/80 dark:border-blue-900/50">
                    <div class="flex items-center gap-2">
                        <input type="text" name="person_in_charge_manual" id="modalPicManualInput"
                               value="{{ old('person_in_charge_manual', old('pic_mode') === 'manual' ? old('person_in_charge') : '') }}"
                               class="flex-1 px-3.5 py-2 rounded-xl text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                               placeholder="Ketik nama PJ baru..." autocomplete="off">
                        <button type="button" id="modalAddPicToDropdownBtn"
                                class="px-3 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors whitespace-nowrap shrink-0 cursor-pointer">
                            <i class="bi bi-check2 me-1"></i>Pakai
                        </button>
                    </div>
                    <p id="modalPicDuplicateWarning" class="hidden text-xs font-semibold text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                        <i class="bi bi-exclamation-triangle-fill shrink-0"></i>
                        <span>Nama ini sudah ada di daftar pilihan!</span>
                    </p>
                </div>

                @error('person_in_charge')<p class="text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="modalRoomSubmitBtn" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="existingPicsData" class="hidden" data-pics="{{ json_encode($picList->values()) }}"></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const existingPicsEl = document.getElementById('existingPicsData');
    const existingPics = existingPicsEl && existingPicsEl.dataset.pics ? JSON.parse(existingPicsEl.dataset.pics) : [];

    const modeInput = document.getElementById('modalPicModeInput');
    const toggleBtn = document.getElementById('modalTogglePicModeBtn');
    const toggleIcon = document.getElementById('modalTogglePicIcon');
    const toggleText = document.getElementById('modalTogglePicText');
    const selectWrap = document.getElementById('modalPicSelectWrap');
    const manualWrap = document.getElementById('modalPicManualWrap');
    const selectEl = document.getElementById('modalPicSelect');
    const manualInput = document.getElementById('modalPicManualInput');
    const applyManualBtn = document.getElementById('modalAddPicToDropdownBtn');
    const duplicateWarn = document.getElementById('modalPicDuplicateWarning');
    const submitBtn = document.getElementById('modalRoomSubmitBtn');

    function normalizeName(str) {
        return String(str || '').trim().replace(/\s+/g, ' ');
    }

    function isDuplicatePic(name) {
        const lower = normalizeName(name).toLowerCase();
        if (!lower) return false;
        return existingPics.some(p => normalizeName(p).toLowerCase() === lower);
    }

    function setPicMode(mode) {
        if (!modeInput) return;
        modeInput.value = mode;
        const isManual = mode === 'manual';
        selectWrap?.classList.toggle('hidden', isManual);
        manualWrap?.classList.toggle('hidden', !isManual);
        if (toggleIcon) {
            toggleIcon.className = isManual ? 'bi bi-list-ul' : 'bi bi-plus-circle';
        }
        if (toggleText) {
            toggleText.textContent = isManual ? 'Pilih Daftar' : 'Tambah Baru';
        }
        if (isManual) {
            manualInput?.focus();
            validateManualPic();
        } else {
            duplicateWarn?.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    function validateManualPic() {
        if (!manualInput) return true;
        const dup = isDuplicatePic(manualInput.value);
        duplicateWarn?.classList.toggle('hidden', !dup);
        if (submitBtn) {
            submitBtn.disabled = dup;
            submitBtn.classList.toggle('opacity-50', dup);
        }
        return !dup;
    }

    toggleBtn?.addEventListener('click', function () {
        setPicMode(modeInput?.value === 'manual' ? 'select' : 'manual');
    });

    manualInput?.addEventListener('input', validateManualPic);

    applyManualBtn?.addEventListener('click', function () {
        const val = normalizeName(manualInput?.value);
        if (!val) return;
        if (isDuplicatePic(val)) {
            validateManualPic();
            return;
        }
        existingPics.push(val);
        if (selectEl) {
            const opt = document.createElement('option');
            opt.value = val;
            opt.textContent = val;
            opt.selected = true;
            selectEl.appendChild(opt);
            selectEl.value = val;
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (manualInput) manualInput.value = '';
        setPicMode('select');
    });

    document.querySelectorAll('.delete-room-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const roomName = this.dataset.roomName || 'ruangan ini';
            if (!confirm('Hapus ' + roomName + '?')) {
                return;
            }

            const deleteUrl = this.dataset.deleteUrl;
            const cardCol = this.closest('.room-card-col');
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Ruangan tidak dapat dihapus.');
                }
                return data;
            })
            .then(() => {
                if (cardCol) cardCol.remove();
                const remaining = document.querySelectorAll('.room-card-col');
                if (remaining.length === 0) {
                    window.location.reload();
                }
            })
            .catch(err => {
                if (window.showToast) {
                    window.showToast(err.message || 'Gagal menghapus ruangan', 'error');
                } else {
                    alert(err.message || 'Gagal menghapus ruangan');
                }
            });
        });
    });
});
</script>
@endsection