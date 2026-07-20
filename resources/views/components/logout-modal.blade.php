<!-- LOGOUT MODAL -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <!-- MODAL HEADER -->
            <div class="modal-header border-0 pb-0">
                <div class="modal-title-wrapper w-100">
                    <div class="logout-icon mb-3">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <h5 class="modal-title fw-bold" id="logoutModalLabel">Konfirmasi Logout</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- MODAL BODY -->
            <div class="modal-body text-center pt-2">
                <p class="mb-0 text-muted">Apakah Anda yakin ingin keluar dari sistem Inventory Management?</p>
            </div>

            <!-- MODAL FOOTER -->
            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Batal
                </button>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-box-arrow-right me-2"></i>Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-content {
        background: var(--card);
        color: var(--text);
    }

    .modal-header {
        background: var(--card);
    }

    .modal-body {
        background: var(--card);
    }

    .modal-footer {
        background: var(--card);
    }

    .modal-title-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .logout-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fee2e2;
        color: #dc2626;
        font-size: 28px;
    }

    html.dark .logout-icon {
        background: rgba(220, 38, 38, 0.15);
        color: #fca5a5;
    }

    .modal-title {
        color: var(--text);
    }

    .btn-secondary {
        background-color: #e5e7eb;
        border-color: #e5e7eb;
        color: #111827;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
        border-color: #d1d5db;
    }

    html.dark .btn-secondary {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.1);
        color: #eef2ff;
    }

    html.dark .btn-secondary:hover {
        background-color: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.15);
    }

    .btn-danger {
        background-color: #dc2626;
        border-color: #dc2626;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        background-color: #b91c1c;
        border-color: #b91c1c;
    }

    .btn-danger:focus {
        box-shadow: 0 0 0 0.25rem rgba(220, 38, 38, 0.25);
    }
</style>
