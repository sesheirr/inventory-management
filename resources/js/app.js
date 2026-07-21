document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.setAttribute('autocomplete', 'off');
    }
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
