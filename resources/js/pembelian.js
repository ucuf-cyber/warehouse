const cfg = window.__pembelianPage;
const produkByLabel = new Map(cfg.products.map((p) => [`${p.kode_barang} — ${p.name}`, p]));
let poItemIndex = 0;

function poItemRowHtml(index) {
    return `
        <div class="row g-2 align-items-end mb-2 po-item-row" data-index="${index}">
            <div class="col-5">
                <input type="text" class="form-control form-control-sm produk-search" list="datalist-produk-po" placeholder="Cari produk..." autocomplete="off" required>
                <input type="hidden" name="items[${index}][product_id]">
            </div>
            <div class="col-3">
                <input type="number" name="items[${index}][qty]" class="form-control form-control-sm item-qty" min="1" value="1" placeholder="Qty" required>
            </div>
            <div class="col-3">
                <input type="number" name="items[${index}][price]" class="form-control form-control-sm item-price" min="0" placeholder="Harga" required>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button>
            </div>
        </div>`;
}

function addPoItemRow() {
    const $row = $(poItemRowHtml(poItemIndex++));
    $('#po-items').append($row);

    $row.find('.produk-search').on('input', function () {
        const product = produkByLabel.get($(this).val());
        if (product) {
            $row.find('[name$="[product_id]"]').val(product.id);
            $row.find('.item-price').val(product.harga_modal);
            recalcPoTotal();
        }
    });

    $row.find('.item-qty, .item-price').on('input', recalcPoTotal);

    $row.find('.btn-remove-item').on('click', function () {
        $row.remove();
        recalcPoTotal();
    });
}

function recalcPoTotal() {
    let total = 0;
    $('.po-item-row').each(function () {
        const qty = Number($(this).find('.item-qty').val()) || 0;
        const price = Number($(this).find('.item-price').val()) || 0;
        total += qty * price;
    });
    $('#po-total').text(`Total: ${window.formatRupiah(total)}`);
}

function statusBadge(status) {
    const map = {
        draft: ['badge-muted', 'Draft'],
        ordered: ['badge-warning', 'Dipesan'],
        received: ['badge-success', 'Diterima'],
        cancelled: ['badge-danger', 'Dibatalkan'],
    };
    const [cls, label] = map[status] ?? ['badge-muted', status];
    return `<span class="badge-status ${cls}">${label}</span>`;
}

function initTable() {
    const dt = $('#table-po').DataTable({
        processing: true,
        serverSide: true,
        order: [[2, 'desc']],
        ajax: {
            url: cfg.routes.data,
            data: (d) => { d.status = $('#filter-status-po').val(); },
        },
        columns: [
            { data: 'code', className: 'dt-nowrap' },
            { data: 'supplier_name', render: (v) => window.truncateCell(v) },
            { data: 'purchase_date', className: 'dt-nowrap', render: (v) => window.formatDateId(v) },
            { data: 'status', className: 'dt-nowrap', render: (v) => statusBadge(v) },
            { data: 'total_amount', className: 'text-end dt-nowrap', render: (v) => window.formatRupiah(v) },
            { data: 'paid_amount', className: 'text-end dt-nowrap', render: (v) => window.formatRupiah(v) },
            { data: 'balance', className: 'text-end dt-nowrap', render: (v) => window.formatRupiah(v) },
            { data: 'actions', orderable: false, searchable: false, className: 'dt-nowrap' },
        ],
    });

    $('#filter-status-po').on('change', () => dt.ajax.reload());

    return dt;
}

function loadDetail(id) {
    $('#po-detail-body').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    $('#po-detail-actions').empty();
    bootstrap.Modal.getOrCreateInstance('#modal-po-detail').show();

    $.get(`${cfg.routes.base}/${id}`).done((res) => renderDetail(res.data)).fail(window.handleAjaxError);
}

function renderDetail(po) {
    $('#po-detail-title').text(`${po.code} — ${po.supplier.name}`);

    const itemsHtml = po.items.map((i) => `
        <tr><td>${i.product.kode_barang}</td><td>${i.product.name}</td><td class="text-end">${i.qty}</td><td class="text-end">${window.formatRupiah(i.price)}</td><td class="text-end">${window.formatRupiah(i.subtotal)}</td></tr>
    `).join('');

    const paymentsHtml = po.payments.length
        ? po.payments.map((p) => `<li class="d-flex justify-content-between border-bottom py-1"><span>${window.formatDateId(p.payment_date)} (${p.method})</span><span class="fw-semibold">${window.formatRupiah(p.amount)}</span></li>`).join('')
        : '<li class="text-secondary-app small">Belum ada pembayaran</li>';

    const returnsHtml = po.returns.length
        ? po.returns.map((r) => `<li class="border-bottom py-1"><div class="d-flex justify-content-between"><span class="fw-semibold">${r.code}</span><span>${window.formatRupiah(r.total_amount)}</span></div><div class="text-secondary-app small">${r.reason ?? '-'}</div></li>`).join('')
        : '<li class="text-secondary-app small">Belum ada retur</li>';

    const notaHtml = po.nota
        ? `<a href="/storage/${po.nota}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-text me-1"></i>Lihat Nota</a>`
        : '<span class="text-secondary-app small">Belum ada nota diunggah</span>';

    $('#po-detail-body').html(`
        <div class="d-flex justify-content-between mb-3">
            <div>${statusBadge(po.status)}</div>
            <div class="text-end">
                <div class="text-secondary-app small">Total: ${window.formatRupiah(po.total_amount)}</div>
                <div class="text-secondary-app small">Dibayar: ${window.formatRupiah(po.paid_amount)}</div>
                <div class="fw-bold">Sisa: ${window.formatRupiah(po.total_amount - po.paid_amount)}</div>
            </div>
        </div>
        <h6 class="fw-bold small">ITEM</h6>
        <table class="table table-sm mb-3"><tbody>${itemsHtml}</tbody></table>
        <h6 class="fw-bold small">PEMBAYARAN</h6>
        <ul class="list-unstyled mb-3">${paymentsHtml}</ul>
        <h6 class="fw-bold small">RETUR</h6>
        <ul class="list-unstyled mb-3">${returnsHtml}</ul>
        <h6 class="fw-bold small">NOTA</h6>
        <div class="mb-2">${notaHtml}</div>
    `);

    const actions = [];

    if (po.status === 'ordered') {
        actions.push(`<button type="button" class="btn btn-success btn-sm" data-action="terima" data-id="${po.id}"><i class="bi bi-box-seam me-1"></i>Terima Barang</button>`);
        actions.push(`<button type="button" class="btn btn-outline-danger btn-sm" data-action="cancel" data-id="${po.id}"><i class="bi bi-x-lg me-1"></i>Batalkan</button>`);
    }

    if (po.status !== 'cancelled') {
        actions.push(`<button type="button" class="btn btn-primary btn-sm" data-action="bayar" data-id="${po.id}"><i class="bi bi-cash me-1"></i>Tambah Pembayaran</button>`);
        actions.push(`<button type="button" class="btn btn-outline-secondary btn-sm" data-action="nota" data-id="${po.id}"><i class="bi bi-upload me-1"></i>Upload Nota</button>`);
    }

    if (po.status === 'received') {
        actions.push(`<button type="button" class="btn btn-outline-danger btn-sm" data-action="retur" data-id="${po.id}"><i class="bi bi-arrow-return-left me-1"></i>Buat Retur</button>`);
    }

    $('#po-detail-actions').html(actions.join(''));
    $('#po-detail-actions').data('purchase', po);
}

function refreshTableAndDetail(dt, id) {
    dt.ajax.reload(null, false);
    if ($('#modal-po-detail').hasClass('show')) loadDetail(id);
}

function initDetailActions(dt) {
    $('#table-po').on('click', '[data-action="detail"]', function () {
        loadDetail($(this).data('id'));
    });

    $('#po-detail-actions').on('click', '[data-action="terima"]', function () {
        const id = $(this).data('id');
        window.appConfirm({ text: 'Stok produk akan otomatis bertambah sesuai item PO.' }).then((r) => {
            if (!r.isConfirmed) return;
            $.post(`${cfg.routes.base}/${id}/terima`)
                .done((res) => { window.appToast('success', res.message); refreshTableAndDetail(dt, id); })
                .fail(window.handleAjaxError);
        });
    });

    $('#po-detail-actions').on('click', '[data-action="cancel"]', function () {
        const id = $(this).data('id');
        window.appConfirm({ text: 'Batalkan PO ini?' }).then((r) => {
            if (!r.isConfirmed) return;
            $.ajax({ url: `${cfg.routes.base}/${id}/cancel`, method: 'PUT' })
                .done((res) => { window.appToast('success', res.message); refreshTableAndDetail(dt, id); })
                .fail(window.handleAjaxError);
        });
    });

    $('#po-detail-actions').on('click', '[data-action="bayar"]', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Tambah Pembayaran',
            html: `
                <input type="number" id="swal-amount" class="swal2-input" placeholder="Jumlah bayar">
                <input type="date" id="swal-date" class="swal2-input" value="${new Date().toISOString().slice(0, 10)}">
                <select id="swal-method" class="swal2-input">
                    <option value="transfer">Transfer</option>
                    <option value="cash">Cash</option>
                    <option value="other">Lainnya</option>
                </select>`,
            confirmButtonText: 'Simpan',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2F6FED',
            preConfirm: () => ({
                amount: document.getElementById('swal-amount').value,
                payment_date: document.getElementById('swal-date').value,
                method: document.getElementById('swal-method').value,
            }),
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post(`${cfg.routes.base}/${id}/pembayaran`, result.value)
                .done((res) => { window.appToast('success', res.message); refreshTableAndDetail(dt, id); })
                .fail(window.handleAjaxError);
        });
    });

    $('#po-detail-actions').on('click', '[data-action="nota"]', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Upload Nota',
            html: '<input type="file" id="swal-nota" class="swal2-file" accept="image/*,.pdf">',
            confirmButtonText: 'Upload',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2F6FED',
            preConfirm: () => document.getElementById('swal-nota').files[0],
        }).then((result) => {
            if (!result.isConfirmed || !result.value) return;

            const formData = new FormData();
            formData.append('nota', result.value);

            $.ajax({
                url: `${cfg.routes.base}/${id}/nota`, method: 'POST', data: formData, processData: false, contentType: false,
            }).done((res) => { window.appToast('success', res.message); refreshTableAndDetail(dt, id); })
                .fail(window.handleAjaxError);
        });
    });

    $('#po-detail-actions').on('click', '[data-action="retur"]', function () {
        const po = $('#po-detail-actions').data('purchase');
        $('#retur-purchase-id').val(po.id);
        $('#form-po-retur')[0].reset();
        $('#retur-purchase-id').val(po.id);

        const html = po.items.map((item, idx) => `
            <div class="row g-2 align-items-center mb-2">
                <div class="col-1"><input type="checkbox" class="form-check-input retur-check" data-index="${idx}"></div>
                <div class="col-6">${item.product.name} <span class="text-secondary-app small">(dibeli: ${item.qty})</span></div>
                <div class="col-3">
                    <input type="number" class="form-control form-control-sm retur-qty" data-index="${idx}" min="1" max="${item.qty}" value="1" disabled>
                </div>
                <input type="hidden" class="retur-product-id" data-index="${idx}" value="${item.product_id ?? item.product.id}">
                <input type="hidden" class="retur-price" data-index="${idx}" value="${item.price}">
            </div>`).join('');

        $('#retur-items').html(html);
        $('.retur-check').on('change', function () {
            $(this).closest('.row').find('.retur-qty').prop('disabled', !this.checked);
        });

        bootstrap.Modal.getInstance('#modal-po-detail')?.hide();
        bootstrap.Modal.getOrCreateInstance('#modal-po-retur').show();
    });

    $('#form-po-retur').on('submit', function (e) {
        e.preventDefault();

        const purchaseId = $('#retur-purchase-id').val();
        const items = [];

        $('.retur-check:checked').each(function () {
            const idx = $(this).data('index');
            items.push({
                product_id: $(`.retur-product-id[data-index="${idx}"]`).val(),
                qty: $(`.retur-qty[data-index="${idx}"]`).val(),
                price: $(`.retur-price[data-index="${idx}"]`).val(),
            });
        });

        if (items.length === 0) {
            window.appToast('error', 'Pilih minimal satu item untuk diretur.');
            return;
        }

        const payload = { ...Object.fromEntries(new FormData(this)), items };

        $.ajax({
            url: `${cfg.routes.base}/${purchaseId}/retur`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
        }).done((res) => {
            window.appToast('success', res.message);
            bootstrap.Modal.getInstance('#modal-po-retur')?.hide();
            dt.ajax.reload(null, false);
        }).fail(window.handleAjaxError);
    });
}

$(function () {
    if (!document.getElementById('table-po')) return;

    addPoItemRow();
    $('#btn-add-po-item').on('click', addPoItemRow);

    $('#modal-po').on('hidden.bs.modal', function () {
        $('#form-po')[0].reset();
        $('#po-items').empty();
        poItemIndex = 0;
        addPoItemRow();
        recalcPoTotal();
    });

    const dt = initTable();
    initDetailActions(dt);

    $('#form-po').on('submit', function (e) {
        e.preventDefault();

        $.post(cfg.routes.store, $(this).serialize())
            .done((res) => {
                window.appToast('success', res.message);
                bootstrap.Modal.getInstance('#modal-po')?.hide();
                dt.ajax.reload();
            })
            .fail((xhr) => {
                if (xhr.status === 422) {
                    window.appToast('error', xhr.responseJSON?.message || 'Data tidak valid.');
                } else {
                    window.handleAjaxError(xhr);
                }
            });
    });
});
