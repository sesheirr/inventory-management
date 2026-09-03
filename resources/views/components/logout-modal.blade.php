{{-- LOGOUT MODAL --}}
<div id="logoutModal" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200">
    <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-center animate-fade-in">
        
        {{-- Close Button --}}
        <button type="button" data-modal-close class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="bi bi-x-lg text-sm"></i>
        </button>

        {{-- Icon --}}
        <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl mx-auto mb-4 shadow-inner">
            <i class="bi bi-box-arrow-right"></i>
        </div>

        {{-- Content --}}
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Konfirmasi Keluar</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
            Apakah Anda yakin ingin keluar dari Sistem Inventaris Diskominfo Garut?
        </p>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="button" data-modal-close class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                Batal
            </button>
            <form action="{{ route('logout') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>
