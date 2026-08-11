document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.setAttribute('autocomplete', 'off');
    }
});
// Dark/Light theme toggle on layout and settings page
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('darkModeToggle');
    const mobileToggle = document.getElementById('darkModeToggleMobile');
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
        const mobileIcon = document.getElementById('darkModeIconMobile');
        const mobileText = document.getElementById('darkModeTextMobile');
        if (theme === 'dark') {
            docEl.classList.add('dark');
            if (icon) { icon.classList.remove('bi-moon'); icon.classList.add('bi-sun'); }
            if (mobileIcon) { mobileIcon.classList.remove('fa-moon'); mobileIcon.classList.add('fa-sun'); }
            if (mobileText) mobileText.textContent = 'Mode Terang';
        } else {
            docEl.classList.remove('dark');
            if (icon) { icon.classList.remove('bi-sun'); icon.classList.add('bi-moon'); }
            if (mobileIcon) { mobileIcon.classList.remove('fa-sun'); mobileIcon.classList.add('fa-moon'); }
            if (mobileText) mobileText.textContent = 'Mode Gelap';
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
    function handleToggleClick() {
        const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme);
    }
    if (toggle) {
        toggle.addEventListener('click', handleToggleClick);
    }
    if (mobileToggle) {
        mobileToggle.addEventListener('click', handleToggleClick);
    }
    themeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const selectedTheme = button.dataset.theme;
            if (!selectedTheme) return;
            localStorage.setItem('theme', selectedTheme);
            applyTheme(selectedTheme);
        });
    });
    window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        applyTheme(getPreferredTheme());
    }
  });
});