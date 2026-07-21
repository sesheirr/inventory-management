document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.setAttribute('autocomplete', 'off');
    }
});

// Dark/Light theme toggle on layout and settings page
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
        docEl.classList.remove('light', 'dark');
        docEl.classList.add(theme === 'dark' ? 'dark' : 'light');
        if (icon) {
            icon.classList.toggle('bi-sun', theme === 'dark');
            icon.classList.toggle('bi-moon', theme !== 'dark');
        }
        updateSegmentButtons(theme);
    }

    function getPreferredTheme() {
        const stored = localStorage.getItem('theme');
        if (stored === 'dark' || stored === 'light') return stored;
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
        return 'light';
    }

    const current = getPreferredTheme();
    applyTheme(current);

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
            if (!selectedTheme) return;
            localStorage.setItem('theme', selectedTheme);
            applyTheme(selectedTheme);
        });
    });
});
