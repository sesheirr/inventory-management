@extends('layouts.app')

@section('page-title', 'Edit Ruangan')
@section('page-subtitle', 'Perbarui data ruangan & penanggung jawab')

@section('content')
@php
    $picList = collect($picList ?? []);
    $currentPic = old('person_in_charge', $room->person_in_charge);
    if ($currentPic && !$picList->contains(fn ($p) => mb_strtolower($p) === mb_strtolower($currentPic))) {
        $picList = $picList->push($currentPic)->values();
    }
@endphp
<div class="max-w-xl mx-auto space-y-5" data-framer-page>
    <div class="flex items-center gap-3 pb-3.5 border-b border-slate-200/80 dark:border-slate-800/80">
        <a href="{{ route('rooms.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Edit Ruangan</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $room->name }}</p>
        </div>
    </div>

    <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-5 sm:p-7">
        <form action="{{ route('rooms.update', $room) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="pic_mode" id="editPicModeInput" value="{{ old('pic_mode', 'select') }}">

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Nama Ruangan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $room->name) }}" required 
                       class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                @error('name')<p class="text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Penanggung Jawab
                    </label>
                    <button type="button" id="editTogglePicModeBtn"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 transition-colors cursor-pointer">
                        <i class="bi bi-plus-circle" id="editTogglePicIcon"></i>
                        <span id="editTogglePicText">Tambah Baru</span>
                    </button>
                </div>

                <div id="editPicSelectWrap" class="{{ old('pic_mode', 'select') === 'manual' ? 'hidden' : 'block' }} space-y-1">
                    <select name="person_in_charge" id="editPicSelect"
                            class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="">— Pilih Penanggung Jawab —</option>
                        @foreach($picList as $picOption)
                            <option value="{{ $picOption }}" @selected(mb_strtolower((string) $currentPic) === mb_strtolower($picOption))>{{ $picOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="editPicManualWrap" class="{{ old('pic_mode', 'select') === 'manual' ? 'block' : 'hidden' }} space-y-2 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-blue-200/80 dark:border-blue-900/50">
                    <div class="flex items-center gap-2">
                        <input type="text" name="person_in_charge_manual" id="editPicManualInput"
                               value="{{ old('person_in_charge_manual', old('pic_mode') === 'manual' ? old('person_in_charge') : '') }}"
                               class="flex-1 px-4 py-2 rounded-xl text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                               placeholder="Ketik nama PJ baru..." autocomplete="off">
                        <button type="button" id="editAddPicToDropdownBtn"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors whitespace-nowrap shrink-0 cursor-pointer">
                            <i class="bi bi-check2 me-1"></i>Pakai
                        </button>
                    </div>
                    <p id="editPicDuplicateWarning" class="hidden text-xs font-semibold text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                        <i class="bi bi-exclamation-triangle-fill shrink-0"></i>
                        <span>Nama ini sudah ada di daftar pilihan!</span>
                    </p>
                </div>

                @error('person_in_charge')<p class="text-xs text-rose-500 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <a href="{{ route('rooms.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" id="editRoomSubmitBtn" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editExistingPicsData" class="hidden" data-pics="{{ json_encode($picList->values()) }}"></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const existingPicsEl = document.getElementById('editExistingPicsData');
    const existingPics = existingPicsEl && existingPicsEl.dataset.pics ? JSON.parse(existingPicsEl.dataset.pics) : [];
    const modeInput = document.getElementById('editPicModeInput');
    const toggleBtn = document.getElementById('editTogglePicModeBtn');
    const toggleIcon = document.getElementById('editTogglePicIcon');
    const toggleText = document.getElementById('editTogglePicText');
    const selectWrap = document.getElementById('editPicSelectWrap');
    const manualWrap = document.getElementById('editPicManualWrap');
    const selectEl = document.getElementById('editPicSelect');
    const manualInput = document.getElementById('editPicManualInput');
    const applyBtn = document.getElementById('editAddPicToDropdownBtn');
    const dupWarn = document.getElementById('editPicDuplicateWarning');
    const submitBtn = document.getElementById('editRoomSubmitBtn');

    function normalize(s) {
        return String(s || '').trim().replace(/\s+/g, ' ');
    }
    function isDup(name) {
        const lower = normalize(name).toLowerCase();
        if (!lower) return false;
        return existingPics.some(p => normalize(p).toLowerCase() === lower);
    }
    function validate() {
        const dup = isDup(manualInput?.value);
        dupWarn?.classList.toggle('hidden', !dup);
        if (submitBtn) {
            submitBtn.disabled = dup;
            submitBtn.classList.toggle('opacity-50', dup);
        }
        return !dup;
    }
    function setMode(mode) {
        if (!modeInput) return;
        modeInput.value = mode;
        const isManual = mode === 'manual';
        selectWrap?.classList.toggle('hidden', isManual);
        manualWrap?.classList.toggle('hidden', !isManual);
        if (toggleIcon) toggleIcon.className = isManual ? 'bi bi-list-ul' : 'bi bi-plus-circle';
        if (toggleText) toggleText.textContent = isManual ? 'Pilih Daftar' : 'Tambah Baru';
        if (isManual) {
            manualInput?.focus();
            validate();
        } else {
            dupWarn?.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = false;
        }
    }
    toggleBtn?.addEventListener('click', () => setMode(modeInput?.value === 'manual' ? 'select' : 'manual'));
    manualInput?.addEventListener('input', validate);
    applyBtn?.addEventListener('click', () => {
        const val = normalize(manualInput?.value);
        if (!val || !validate()) return;
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
        setMode('select');
    });
});
</script>
@endsection