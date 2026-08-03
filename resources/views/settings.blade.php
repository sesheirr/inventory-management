@extends('layouts.app')

@section('content')
<div class="settings-page">
    <div class="settings-hero">
        <div class="settings-hero-inner">
            <div>
                <p class="settings-breadcrumb">Settings</p>
                <h1 class="settings-hero-title">Pengaturan</h1>
                <p class="settings-hero-subtitle">Kelola preferensi aplikasi dan akun Anda.</p>
            </div>
        </div>
    </div>

    <div class="settings-stack">
        <!-- Edit Profile card (moved actions from Profile page) -->
        <section class="settings-card">
            <div class="settings-card-inner">
                <div class="settings-card-meta">
                    <div class="settings-card-icon settings-card-icon-violet">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="settings-card-title">Edit Profile</h2>
                        <p class="settings-card-text">Perbarui informasi profil akun Anda.</p>
                    </div>
                </div>

                <div class="settings-card-action">
                    <button type="button" class="btn-ghost" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        Edit Profile
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <path d="M5 12h14"></path>
                            <path d="m13 6 6 6-6 6"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <!-- Ubah Password card (keamanan) -->
        <section class="settings-card">
            <div class="settings-card-inner">
                <div class="settings-card-meta">
                    <div class="settings-card-icon settings-card-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="11" width="12" height="9" rx="2"></rect>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="settings-card-title">Ubah Password</h2>
                        <p class="settings-card-text">Ubah password akun Anda secara berkala untuk menjaga keamanan.</p>
                    </div>
                </div>

                <div class="settings-card-action">
                    <button type="button" class="btn-ghost">
                        Ubah Password
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <path d="M5 12h14"></path>
                            <path d="m13 6 6 6-6 6"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <!-- Tentang Aplikasi (unchanged) -->
        <section class="settings-card">
            <div class="settings-card-inner">
                <div class="settings-card-meta">
                    <div class="settings-card-icon settings-card-icon-yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 8h.01"></path>
                            <path d="M11 12h1v4h1"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="settings-card-title">Tentang Aplikasi</h2>
                        <p class="settings-card-text">Informasi versi dan detail aplikasi inventaris.</p>
                    </div>
                </div>

                <div class="settings-card-action settings-card-info">
                    <div class="info-row">
                        <span class="info-label">Nama Aplikasi</span>
                        <span class="info-value">Sistem Informasi Inventarisasi Barang dan Aset</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Versi</span>
                        <span class="info-value">v1.0.0</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Developer</span>
                        <span class="info-value">RPL SMKN 1 Garut</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Moved Edit Profile modal from Profile page so edit action is accessible from Settings -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-semibold" id="editProfileModalLabel">Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 text-center">
                            <label class="form-label">Foto Profil</label>
                            <div class="d-flex flex-column align-items-center gap-2">
                                <div class="avatar-circle shadow overflow-hidden" style="width: 90px; height: 90px; font-size: 2rem;">
                                    @if(!empty($user->avatar))
                                        <img id="profilePreviewImage" src="{{ $user->avatar }}" alt="Foto profil" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                        <span id="profilePreviewFallback" class="fw-bold d-none">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                                    @else
                                        <img id="profilePreviewImage" src="" alt="Foto profil" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                        <span id="profilePreviewFallback" class="fw-bold">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                                    @endif
                                </div>
                                <input type="file" id="avatarInput" name="avatar" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="gender" class="form-select">
                                <option value="" @selected(empty($user->gender))>-- Pilih --</option>
                                <option value="Laki-laki" @selected($user->gender === 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected($user->gender === 'Perempuan')>Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            @php
                                $birthDateValue = old('birth_date');
                                if (empty($birthDateValue) && !empty($user->birth_date)) {
                                    try {
                                        $parsedBirthDate = \Carbon\Carbon::parse($user->birth_date);
                                        $birthDateValue = $parsedBirthDate->format('Y-m-d');
                                    } catch (\Throwable $e) {
                                        $birthDateValue = (string) $user->birth_date;
                                    }
                                }
                                if (!empty($birthDateValue)) {
                                    $birthDateValue = substr((string) $birthDateValue, 0, 10);
                                }
                            @endphp
                            <input type="date" name="birth_date" class="form-control" value="{{ $birthDateValue ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="3">{{ old('address', $user->address ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const avatarInput = document.getElementById('avatarInput');
        const previewImage = document.getElementById('profilePreviewImage');
        const previewFallback = document.getElementById('profilePreviewFallback');

        if (!avatarInput || !previewImage || !previewFallback) {
            return;
        }

        avatarInput.addEventListener('change', function (event) {
            const [file] = event.target.files || [];

            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
                previewFallback.classList.add('d-none');
            };

            reader.readAsDataURL(file);
        });
    });
    </script>
</div>
@endsection
