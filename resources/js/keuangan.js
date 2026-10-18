const cfg = window.__keuanganPage;
let cashflowChart = null;

function statusTypeBadge(type) {
    return type === 'income'
        ? '<span class="badge-status badge-success">Pemasukan</span>'
        : '<span class="badge-status badge-danger">Pengeluaran</span>';
}

function loadSummary() {
    $.get(cfg.routes.summary).done((res) => {
        const d = res.data;
        const net = d.income_this_month - d.expense_this_month;

        $('#kpi-kas').removeClass('skeleton skeleton-title').text(window.formatRupiah(d.cash_balance));
        $('#kpi-income').removeClass('skeleton skeleton-title').text(window.formatRupiah(d.income_this_month));
        $('#kpi-expense').removeClass('skeleton skeleton-title').text(window.formatRupiah(d.expense_this_month));
        $('#kpi-net').removeClass('skeleton skeleton-title').text(window.formatRupiah(net)).toggleClass('text-danger', net < 0);
    });
}

function initTransaksiTable() {
    const dt = $('#table-transaksi').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
            url: cfg.routes.data,
            data: (d) => {
                d.type = $('#filter-type-tx').val();
                d.date_from = $('#filter-from-tx').val();
                d.date_to = $('#filter-to-tx').val();
            },
        },
        columns: [
            { data: 'transaction_date', className: 'dt-nowrap', render: (v) => window.formatDateId(v) },
            { data: 'type', className: 'dt-nowrap', render: (v) => statusTypeBadge(v) },
            { data: 'category', className: 'dt-nowrap' },
            { data: 'description', render: (v) => window.truncateCell(v) },
            { data: 'amount', className: 'text-end dt-nowrap', render: (v, t, row) => (row.type === 'income' ? '+' : '-') + window.formatRupiah(v) },
            { data: 'user_name', className: 'dt-nowrap' },
            {
                data: 'is_editable', orderable: false, searchable: false, className: 'dt-nowrap',
                render: (editable, t, row) => (editable
                    ? `<button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>`
                    : '<span class="text-secondary-app small">Otomatis</span>'),
            },
        ],
    });

    $('#filter-type-tx, #filter-from-tx, #filter-to-tx').on('change', () => dt.ajax.reload());

    $('#table-transaksi').on('click', '[data-action="delete"]', function () {
        const id = $(this).data('id');
        window.appConfirm({ text: 'Hapus transaksi ini?' }).then((r) => {
            if (!r.isConfirmed) return;
            $.ajax({ url: `${cfg.routes.base}/${id}`, method: 'DELETE' })
                .done((res) => { window.appToast('success', res.message); dt.ajax.reload(null, false); loadSummary(); })
                .fail(window.handleAjaxError);
        });
    });

    return dt;
}

function initTransaksiForm(dt) {
    const categoriesByType = {
        expense: cfg.expenseCategories,
        income: ['Penjualan', 'Modal Tambahan', 'Lain-lain'],
    };

    function fillCategories() {
        const type = $('#transaksi-type').val();
        const $cat = $('#transaksi-category');
        $cat.empty();
        categoriesByType[type].forEach((c) => $cat.append(`<option value="${c}">${c}</option>`));
    }

    $('#transaksi-type').on('change', fillCategories);
    fillCategories();

    $('#modal-transaksi').on('hidden.bs.modal', function () {
        $('#form-transaksi')[0].reset();
        fillCategories();
    });

    $('#form-transaksi').on('submit', function (e) {
        e.preventDefault();

        $.post(cfg.routes.store, $(this).serialize())
            .done((res) => {
                window.appToast('success', res.message);
                bootstrap.Modal.getInstance('#modal-transaksi')?.hide();
                dt.ajax.reload();
                loadSummary();
            })
            .fail((xhr) => window.appToast('error', xhr.responseJSON?.message || 'Gagal menyimpan transaksi.'));
    });
}

function loadLabaRugi() {
    const params = { from: $('#lr-from').val(), to: $('#lr-to').val() };

    $.get(cfg.routes.labaRugi, params).done((res) => {
        const d = res.data;
        $('#lr-omzet').text(window.formatRupiah(d.omzet));
        $('#lr-hpp').text(`(${window.formatRupiah(d.hpp)})`);
        $('#lr-laba-kotor').text(window.formatRupiah(d.laba_kotor));
        $('#lr-beban').text(`(${window.formatRupiah(d.beban_operasional)})`);
        $('#lr-laba-bersih').text(window.formatRupiah(d.laba_bersih)).toggleClass('text-danger', d.laba_bersih < 0);
    });

    const qs = new URLSearchParams({ type: 'laba-rugi', format: 'pdf', ...params }).toString();
    $('#link-lr-pdf').attr('href', `${cfg.routes.exportPdf}?${qs}`);
    $('#link-lr-excel').attr('href', `${cfg.routes.exportExcel}?${qs.replace('format=pdf', 'format=excel')}`);
}

function loadCashflow() {
    const params = { from: $('#cf-from').val(), to: $('#cf-to').val() };

    $.get(cfg.routes.cashflow, params).done((res) => {
        const labels = res.data.map((r) => r.period);
        const income = res.data.map((r) => r.income);
        const expense = res.data.map((r) => r.expense);
        const balance = res.data.map((r) => r.balance);

        cashflowChart?.destroy();
        cashflowChart = new Chart(document.getElementById('chart-cashflow'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { type: 'bar', label: 'Pemasukan', data: income, backgroundColor: '#22C55E' },
                    { type: 'bar', label: 'Pengeluaran', data: expense, backgroundColor: '#EF4444' },
                    { type: 'line', label: 'Saldo Kumulatif', data: balance, borderColor: '#2F6FED', backgroundColor: '#2F6FED', tension: 0.3 },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });
    });

    const qs = new URLSearchParams({ type: 'cashflow', format: 'excel', ...params }).toString();
    $('#link-cf-excel').attr('href', `${cfg.routes.exportExcel}?${qs}`);
}

function loadNeraca() {
    $.get(cfg.routes.neraca).done((res) => {
        const d = res.data;
        $('#nr-kas').text(window.formatRupiah(d.aset.kas));
        $('#nr-persediaan').text(window.formatRupiah(d.aset.persediaan));
        $('#nr-total-aset').text(window.formatRupiah(d.aset.total));
        $('#nr-hutang').text(window.formatRupiah(d.kewajiban.hutang_supplier));
        $('#nr-modal').text(window.formatRupiah(d.modal));
        $('#nr-total-pasiva').text(window.formatRupiah(d.kewajiban.total + d.modal));
    });
}

$(function () {
    if (!window.__keuanganPage) return;

    loadSummary();
    const dt = initTransaksiTable();
    initTransaksiForm(dt);

    $('#btn-lr-load').on('click', loadLabaRugi);
    $('#btn-cf-load').on('click', loadCashflow);

    document.querySelector('[data-bs-target="#tab-labarugi"]')?.addEventListener('shown.bs.tab', loadLabaRugi);
    document.querySelector('[data-bs-target="#tab-cashflow"]')?.addEventListener('shown.bs.tab', loadCashflow);
    document.querySelector('[data-bs-target="#tab-neraca"]')?.addEventListener('shown.bs.tab', loadNeraca);
});
