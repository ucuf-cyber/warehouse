const STORAGE_KEY = 'bms-theme';

function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    document.querySelectorAll('[data-theme-icon]').forEach((el) => {
        el.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
    });
}

function currentTheme() {
    return localStorage.getItem(STORAGE_KEY)
        || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
}

export function initTheme() {
    applyTheme(currentTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem(STORAGE_KEY, next);
            applyTheme(next);
        });
    });
}

// Apply immediately (before DOMContentLoaded) to avoid a flash of the wrong theme.
applyTheme(currentTheme());
