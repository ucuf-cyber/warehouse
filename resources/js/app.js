import './bootstrap';
import './toast';

import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

import $ from 'jquery';

window.$ = window.jQuery = $;

import 'jquery-validation';

import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);
window.Chart = Chart;

import DataTable from 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';
import 'datatables.net-responsive-bs5';

window.DataTable = DataTable;

DataTable.defaults.language = {
    search: '',
    searchPlaceholder: 'Cari...',
    lengthMenu: 'Tampilkan _MENU_ data',
    info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
    infoEmpty: 'Tidak ada data',
    infoFiltered: '(disaring dari _MAX_ total data)',
    zeroRecords: 'Data tidak ditemukan',
    emptyTable: 'Belum ada data',
    paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' },
};

// jQuery AJAX: attach CSRF token to every request (session-based auth).
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// ---- Formatting helpers used across Blade views ----
window.formatRupiah = (value) => 'Rp ' + Number(value || 0).toLocaleString('id-ID');
window.formatNumber = (value) => Number(value || 0).toLocaleString('id-ID');
window.formatDateId = (value) => (value
    ? new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    : '-');
window.escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));
// Truncated table cell: full text stays available via the title attribute/tooltip.
window.truncateCell = (value) => {
    const safe = window.escapeHtml(value ?? '-');
    return `<span class="dt-truncate" title="${safe}">${safe}</span>`;
};

import { initTheme } from './theme';
import { initSidebar } from './sidebar';

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSidebar();

    // Bootstrap tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));

    // Auto-shown server-side flash toast (see layouts/app.blade.php #server-flash).
    const flash = document.getElementById('server-flash');
    if (flash?.dataset.icon) {
        window.appToast(flash.dataset.icon, flash.dataset.message);
    }

    // Page-specific bundles, lazy-loaded only where their markup exists so
    // heavy libraries (e.g. html5-qrcode) never weigh down other pages.
    if (document.getElementById('scan-wedge-input')) {
        import('./scan.js');
    }

    if (document.getElementById('table-produk')) {
        import('./produk.js');
    }

    if (document.getElementById('table-riwayat')) {
        import('./stok.js');
    }

    if (window.__opnamePage) {
        import('./opname.js');
    }

    if (document.getElementById('table-po')) {
        import('./pembelian.js');
    }

    if (document.getElementById('table-penjualan')) {
        import('./penjualan.js');
    }

    if (document.getElementById('table-mapping')) {
        import('./marketplace.js');
    }

    if (document.getElementById('table-transaksi')) {
        import('./keuangan.js');
    }

    if (document.querySelector('.report-card')) {
        import('./laporan.js');
    }

    if (window.__dashboardPage) {
        import('./dashboard.js');
    }

    if (window.__pengaturanPage) {
        import('./pengaturan.js');
    }
});

// PWA: register the service worker once the page has finished loading so it
// never competes with the initial render for bandwidth/CPU.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Installability is a nice-to-have — a failed registration shouldn't be user-facing.
        });
    });
}
