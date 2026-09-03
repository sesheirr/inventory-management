// Theme Management (Dark / Light Mode)
function applyTheme(theme) {
    const docEl = document.documentElement;
    const desktopIcon = document.getElementById('themeIcon');
    const mobileIcon = document.getElementById('darkModeIconMobile');
    const mobileText = document.getElementById('darkModeTextMobile');
    const themeButtons = document.querySelectorAll('.segment-button');

    if (theme === 'dark') {
        docEl.classList.add('dark');
        if (desktopIcon) {
            desktopIcon.className = 'bi bi-sun text-amber-400 text-lg';
        }
        if (mobileIcon) {
            mobileIcon.className = 'bi bi-sun text-amber-400 text-lg';
        }
        if (mobileText) {
            mobileText.textContent = 'Mode Terang';
        }
    } else {
        docEl.classList.remove('dark');
        if (desktopIcon) {
            desktopIcon.className = 'bi bi-moon-stars text-slate-600 text-lg';
        }
        if (mobileIcon) {
            mobileIcon.className = 'bi bi-moon-stars text-slate-400 text-lg';
        }
        if (mobileText) {
            mobileText.textContent = 'Mode Gelap';
        }
    }

    themeButtons.forEach(button => {
        if (button.dataset.theme === theme) {
            button.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
            button.classList.remove('text-slate-600', 'dark:text-slate-400');
        } else {
            button.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
            button.classList.add('text-slate-600', 'dark:text-slate-400');
        }
    });
}

function getPreferredTheme() {
    const stored = localStorage.getItem('theme');
    if (stored === 'dark' || stored === 'light') return stored;
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
    return 'light';
}

// Initialize theme immediately
const currentTheme = getPreferredTheme();
applyTheme(currentTheme);

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(getPreferredTheme());

    // Search inputs autocomplete off
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => input.setAttribute('autocomplete', 'off'));

    // Dark Mode Toggle buttons
    const desktopToggle = document.getElementById('darkModeToggle');
    const mobileToggle = document.getElementById('darkModeToggleMobile');
    const themeButtons = document.querySelectorAll('.segment-button');

    function toggleTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        const nextTheme = isDark ? 'light' : 'dark';
        localStorage.setItem('theme', nextTheme);
        applyTheme(nextTheme);
    }

    if (desktopToggle) desktopToggle.addEventListener('click', toggleTheme);
    if (mobileToggle) mobileToggle.addEventListener('click', toggleTheme);

    themeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const selectedTheme = button.dataset.theme;
            if (selectedTheme) {
                localStorage.setItem('theme', selectedTheme);
                applyTheme(selectedTheme);
            }
        });
    });

    // Mobile Sidebar Drawer Management
    const sidebar = document.getElementById('sidebarOffcanvas');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const openSidebarBtn = document.getElementById('openSidebarBtn');
    const closeSidebarBtn = document.getElementById('closeSidebarBtn');

    function openMobileSidebar() {
        if (sidebar && sidebarBackdrop) {
            sidebar.classList.remove('-translate-x-full');
            sidebarBackdrop.classList.remove('hidden');
            setTimeout(() => sidebarBackdrop.classList.remove('opacity-0'), 10);
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeMobileSidebar() {
        if (sidebar && sidebarBackdrop) {
            sidebar.classList.add('-translate-x-full');
            sidebarBackdrop.classList.add('opacity-0');
            setTimeout(() => sidebarBackdrop.classList.add('hidden'), 250);
            document.body.classList.remove('overflow-hidden');
        }
    }

    if (openSidebarBtn) openSidebarBtn.addEventListener('click', openMobileSidebar);
    if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeMobileSidebar);
    if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);

    // Toast auto-close and manual dismiss
    const toastEl = document.getElementById('liveToast');
    if (toastEl) {
        setTimeout(() => {
            toastEl.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toastEl.style.opacity = '0';
            toastEl.style.transform = 'translate(-50%, -20px) scale(0.95)';
            setTimeout(() => toastEl.remove(), 300);
        }, 3500);

        const toastClose = toastEl.querySelector('.toast-close-btn');
        if (toastClose) {
            toastClose.addEventListener('click', () => {
                toastEl.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                toastEl.style.opacity = '0';
                toastEl.style.transform = 'translate(-50%, -20px) scale(0.95)';
                setTimeout(() => toastEl.remove(), 200);
            });
        }
    }

    // Generic Dropdowns Handler
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-dropdown-toggle]');
        if (toggleBtn) {
            e.stopPropagation();
            const targetId = toggleBtn.getAttribute('data-dropdown-toggle');
            const targetMenu = document.getElementById(targetId);
            
            // Close all other dropdowns
            document.querySelectorAll('[data-dropdown-menu]').forEach(menu => {
                if (menu !== targetMenu) menu.classList.add('hidden');
            });

            if (targetMenu) {
                targetMenu.classList.toggle('hidden');
            }
            return;
        }

        // Click outside dropdowns -> close all
        if (!e.target.closest('[data-dropdown-menu]')) {
            document.querySelectorAll('[data-dropdown-menu]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // Generic Modal Handler
    document.querySelectorAll('[data-modal-target]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetModalId = btn.getAttribute('data-modal-target');
            const targetModal = document.getElementById(targetModalId);
            if (targetModal) {
                targetModal.classList.remove('hidden');
                targetModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-container');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
        });
    });

    // Close modal on backdrop click
    document.querySelectorAll('.modal-container').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
        });
    });
});
