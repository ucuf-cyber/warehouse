const cfg = window.__dashboardPage;
const sparklines = {};
let distribusiChart = null;
let performaChart = null;

const PALETTE = ['#2F6FED', '#60A5FA', '#22C55E', '#F59E0B', '#94A3B8', '#38BDF8'];

function createSparkline(canvasId, data, color) {
    sparklines[canvasId]?.destroy();

    sparklines[canvasId] = new Chart(document.getElementById(canvasId), {
        type: 'line',
        data: {
            labels: data.map((_, i) => i),
            datasets: [{
                data, borderColor: color, backgroundColor: `${color}22`,
                fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
        },
    });
}

function trendText(pct) {
    const arrow = pct >= 0 ? '▲' : '▼';
    const cls = pct >= 0 ? 'is-positive' : 'is-negative';
    return { text: `${arrow} ${Math.abs(pct)}% dari periode sebelumnya`, cls };
}

function loadSummary() {
    $.get(cfg.routes.summary).done((res) => {
        const d = res.data;

        $('#kpi-kas').removeClass('skeleton skeleton-title').text(window.formatRupiah(d.cash_balance));
        const profitTrend = trendText(d.profit_today_change_pct);
        $('#kpi-kas-sub')
            .text(`Profit hari ini: ${d.profit_today >= 0 ? '+' : ''}${window.formatRupiah(d.profit_today)} (${profitTrend.text})`)
            .removeClass('is-positive is-negative').addClass(profitTrend.cls);

        $('#kpi-income').removeClass('skeleton skeleton-title').text(window.formatRupiah(d.income_this_month));
        const incomeTrend = trendText(d.income_change_pct);
        $('#kpi-income-sub').text(incomeTrend.text).removeClass('is-positive is-negative').addClass(incomeTrend.cls);

        $('#kpi-expense').removeClass('skeleton skeleton-title').text(window.formatRupiah(d.expense_this_month));
        // For expenses, a decrease vs. the prior period is the "good" (green) direction.
        const expenseTrend = trendText(d.expense_change_pct);
        $('#kpi-expense-sub').text(expenseTrend.text).removeClass('is-positive is-negative').addClass(d.expense_change_pct <= 0 ? 'is-positive' : 'is-negative');

        $('#kpi-restock').removeClass('skeleton skeleton-title').text(window.formatNumber(d.products_below_min_stock));

        createSparkline('spark-kas', d.sparkline_cash, '#22C55E');
        createSparkline('spark-income', d.sparkline_income, '#2F6FED');
        createSparkline('spark-expense', d.sparkline_expense, '#EF4444');
    }).fail(window.handleAjaxError);
}

function loadStockDistribution() {
    $.get(cfg.routes.stockDistribution).done((res) => {
        distribusiChart?.destroy();

        distribusiChart = new Chart(document.getElementById('chart-distribusi'), {
            type: 'doughnut',
            data: {
                labels: res.data.map((r) => r.label),
                datasets: [{
                    data: res.data.map((r) => r.value),
                    backgroundColor: PALETTE,
                    borderWidth: 2,
                    borderColor: getComputedStyle(document.body).getPropertyValue('--bg-surface') || '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12, usePointStyle: true } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total ? Math.round((ctx.raw / total) * 100) : 0;
                                return `${ctx.label}: ${window.formatNumber(ctx.raw)} unit (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });
    }).fail(window.handleAjaxError);
}

function loadFinanceSummary(year) {
    $.get(cfg.routes.financeSummary, { year }).done((res) => {
        performaChart?.destroy();

        performaChart = new Chart(document.getElementById('chart-performa'), {
            type: 'bar',
            data: {
                labels: res.data.map((r) => r.month),
                datasets: [
                    { label: 'Modal', data: res.data.map((r) => r.modal), backgroundColor: '#2F6FED' },
                    { label: 'Omzet', data: res.data.map((r) => r.omzet), backgroundColor: '#22C55E' },
                    { label: 'Profit', data: res.data.map((r) => r.profit), backgroundColor: '#F59E0B' },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true, ticks: { callback: (v) => window.formatNumber(v) } } },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } },
            },
        });
    }).fail(window.handleAjaxError);
}

function marketplaceIcon(mp) {
    const icons = { manual: 'bi-shop', shopee: 'bi-bag-check', tiktok: 'bi-music-note' };
    const labels = { manual: 'Manual', shopee: 'Shopee', tiktok: 'TikTok' };
    return `<i class="bi ${icons[mp] ?? 'bi-shop'} me-1"></i>${labels[mp] ?? mp}`;
}

function statusBadge(status) {
    const map = {
        pending: ['badge-warning', 'Diproses'],
        processing: ['badge-warning', 'Diproses'],
        completed: ['badge-success', 'Selesai'],
        cancelled: ['badge-danger', 'Batal'],
    };
    const [cls, label] = map[status] ?? ['badge-muted', status];
    return `<span class="badge-status ${cls}">${label}</span>`;
}

function loadRecentSales() {
    $.get(cfg.routes.recentSales).done((res) => {
        if (res.data.length === 0) {
            $('#recent-sales-body').html('<tr><td colspan="7" class="text-center text-secondary-app py-4">Belum ada penjualan</td></tr>');
            return;
        }

        const rows = res.data.map((sale) => {
            const date = new Date(sale.sale_date);
            const time = new Date(sale.created_at);

            return `
                <tr>
                    <td class="dt-nowrap"><i class="bi bi-check-circle-fill text-primary" style="cursor:pointer;" data-action="check" data-id="${sale.id}"></i></td>
                    <td class="fw-semibold dt-nowrap">${sale.code}</td>
                    <td class="dt-nowrap">${statusBadge(sale.status)}</td>
                    <td class="dt-nowrap">${marketplaceIcon(sale.marketplace)}</td>
                    <td class="dt-nowrap">${window.formatRupiah(sale.total_amount)}</td>
                    <td class="dt-nowrap">${window.formatDateId(sale.sale_date)}</td>
                    <td class="dt-nowrap">${time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</td>
                </tr>`;
        }).join('');

        $('#recent-sales-body').html(rows);
    }).fail(window.handleAjaxError);
}

function loadAll() {
    loadSummary();
    loadStockDistribution();
    loadFinanceSummary($('#filter-tahun').val());
    loadRecentSales();
}

$(function () {
    if (!window.__dashboardPage) return;

    loadAll();

    $('#filter-tahun').on('change', function () {
        loadFinanceSummary($(this).val());
    });

    $('#btn-refresh-dashboard').on('click', function () {
        const $icon = $(this).find('i');
        $icon.addClass('spin');
        loadAll();
        setTimeout(() => $icon.removeClass('spin'), 600);
        window.appToast('success', 'Dashboard diperbarui.');
    });

    $('#recent-sales-body').on('click', '[data-action="check"]', function () {
        $(this).removeClass('bi-check-circle-fill text-primary').addClass('bi-check-circle-fill text-success');
        window.appToast('success', 'Ditandai sudah dicek.');
    });
});
