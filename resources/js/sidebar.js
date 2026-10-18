const STORAGE_KEY = 'bms-sidebar-expanded';

export function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (!sidebar) return;

    const isMobile = () => window.innerWidth < 992;

    if (!isMobile() && localStorage.getItem(STORAGE_KEY) === '1') {
        sidebar.classList.add('is-expanded');
    }

    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const expanded = sidebar.classList.toggle('is-expanded');
            backdrop?.classList.toggle('show', expanded && isMobile());

            if (!isMobile()) {
                localStorage.setItem(STORAGE_KEY, expanded ? '1' : '0');
            }
        });
    });

    backdrop?.addEventListener('click', () => {
        sidebar.classList.remove('is-expanded');
        backdrop.classList.remove('show');
    });
}
