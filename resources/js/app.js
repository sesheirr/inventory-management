document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.setAttribute('autocomplete', 'off');
    }

    const transitionOverlay = document.createElement('div');
    transitionOverlay.className = 'page-transition-overlay';
    transitionOverlay.setAttribute('aria-hidden', 'true');
    document.body.appendChild(transitionOverlay);

    document.querySelectorAll('.nav-link[data-nav-transition="true"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#')) {
                return;
            }

            const targetUrl = new URL(href, window.location.origin).href;
            if (window.location.href === targetUrl) {
                return;
            }

            event.preventDefault();

            const rect = link.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            transitionOverlay.style.setProperty('--fx', `${centerX}px`);
            transitionOverlay.style.setProperty('--fy', `${centerY}px`);
            transitionOverlay.style.setProperty('--nav-accent', link.dataset.navAccent || 'rgba(56, 189, 248, 0.48)');

            const mainPanel = document.querySelector('.main-panel');
            mainPanel?.classList.add('route-transitioning');
            transitionOverlay.classList.add('active');

            setTimeout(() => {
                window.location.assign(href);
            }, 260);
        });
    });

   // Auto-close Notifikasi Toast
document.querySelectorAll('.toast').forEach((toastElement) => {
    // 1. Set waktu tunggu 3 detik (3000 ms)
    setTimeout(() => {
        // Efek fade-out halus
        toastElement.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        toastElement.style.opacity = '0';
        toastElement.style.transform = 'translateY(-10px)';

        // Hapus elemen dari HTML setelah efek fade-out selesai
        setTimeout(() => {
            toastElement.remove();
        }, 400);
    }, 3000);
});

// Tombol 'X' (Close) Manual
document.querySelectorAll('.btn-close').forEach((button) => {
    button.addEventListener('click', () => {
        const toast = button.closest('.toast');
        if (toast) {
            toast.style.transition = 'opacity 0.2s ease';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 200);
        }
    });
});
});

// Dark/Light theme toggle
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('darkModeToggle');
    const icon = document.getElementById('themeIcon');
    const themeButtons = document.querySelectorAll('.segment-button');

    function updateSegmentButtons(theme) {
        themeButtons.forEach(button => {
            if (button.dataset.theme === theme) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }

    function applyTheme(theme) {
        const docEl = document.documentElement;
        if (theme === 'dark') {
            docEl.classList.add('dark');
            if (icon) {
                icon.classList.remove('bi-moon');
                icon.classList.add('bi-sun');
            }
        } else {
            docEl.classList.remove('dark');
            if (icon) {
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon');
            }
        }
        updateSegmentButtons(theme);
    }

    function getPreferredTheme() {
        const stored = localStorage.getItem('theme');
        if (stored === 'dark' || stored === 'light') return stored;
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
        return 'light';
    }

    // Initialize theme on load
    const current = getPreferredTheme();
    applyTheme(current);

    // Toggle on button click
    if (toggle) {
        toggle.addEventListener('click', () => {
            const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        });
    }

    themeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const selectedTheme = button.dataset.theme;
            if (selectedTheme) {
                localStorage.setItem('theme', selectedTheme);
                applyTheme(selectedTheme);
            }
        });
    });
});
