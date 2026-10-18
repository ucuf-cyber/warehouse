const cfg = window.__stokPage;

// Map "KODE — Nama" (as typed via the shared datalist) back to a product id/stock.
const produkByLabel = new Map(cfg.products.map((p) => [`${p.kode_barang} — ${p.name}`, p]));

function wireProdukSearch($modal) {
    const $search = $modal.find('.produk-search');
    const $hidden = $modal.find('[name=product_id]');
    const $info = $modal.find('.produk-stock-info');

    $search.on('input', function () {
        const product = produkByLabel.get($(this).val());

        if (product) {
            $hidden.val(product.id);
            $info.text(`Stok saat ini: ${window.formatNumber(product.current_stock)} ${product.unit}`);
            $modal.trigger('produk:selected', [product]);
        } else {
            $hidden.val('');
            $info.text('');
        }
    });
}

function resetModal($modal) {
    $modal.find('form')[0].reset();
    $modal.find('[name=product_id]').val('');
    $modal.find('.produk-stock-info').text('');
    $modal.find('.is-invalid').removeClass('is-invalid');
    $modal.find('.invalid-feedback').remove();
}

function showValidationErrors($form, errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const $input = $form.find(`[name="${field}"]`);
        $input.addClass('is-invalid');
        $input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
    });
}

function submitForm($form, url, onSuccess) {
    $form.on('submit', function (e) {
        e.preventDefault();
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        $.post(url, $form.serialize())
            .done((res) => {
                window.appToast('success', res.message);
                bootstrap.Modal.getInstance($form.closest('.modal'))?.hide();
                onSuccess?.(res);
            })
            .fail((xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    showValidationErrors($form, xhr.responseJSON.errors);
                } else {
                    window.handleAjaxError(xhr);
                }
            });
    });
}

function initRiwayat() {
    const dt = $('#table-riwayat').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
            url: cfg.routes.riwayatData,
            data: (d) => {
                d.product_id = $('#filter-produk').val();
                d.type = $('#filter-type').val();
            },
        },
        columns: [
            { data: 'created_at', className: 'dt-nowrap', render: (v) => new Date(v).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) },
            { data: 'product_name', render: (v) => window.truncateCell(v) },
            {
                data: 'type', className: 'dt-nowrap', render: (v, t, row) => {
                    const positive = ['in', 'return'].includes(v);
                    const cls = v === 'adjustment' || v === 'opname' ? 'badge-info' : (positive ? 'badge-success' : 'badge-danger');

                    return `<span class="badge-status ${cls}">${row.type_label}</span>`;
                },
            },
            { data: 'qty', className: 'text-end dt-nowrap', render: (v) => (v > 0 ? '+' : '') + window.formatNumber(v) },
            { data: 'qty_before', className: 'text-end dt-nowrap' },
            { data: 'qty_after', className: 'text-end fw-bold dt-nowrap' },
            { data: 'notes', render: (v) => window.truncateCell(v) },
            { data: 'user_name', className: 'dt-nowrap' },
        ],
    });

    $('#filter-produk, #filter-type').on('change', () => dt.ajax.reload());

    return dt;
}

function initStockForms(riwayatTable) {
    ['#modal-stock-in', '#modal-stock-out', '#modal-adjustment', '#modal-mutasi'].forEach((id) => {
        const $modal = $(id);
        wireProdukSearch($modal);
        $modal.on('hidden.bs.modal', () => resetModal($modal));
    });

    $('#modal-mutasi').on('produk:selected', (e, product) => {
        $('#mutasi-subrack').val(product.sub_rack_id ?? '');
    });

    const reload = () => riwayatTable.ajax.reload(null, false);

    submitForm($('#form-stock-in'), cfg.routes.stockIn, reload);
    submitForm($('#form-stock-out'), cfg.routes.stockOut, reload);
    submitForm($('#form-adjustment'), cfg.routes.adjustment, reload);
    submitForm($('#form-mutasi'), cfg.routes.mutasi, reload);
}

function initSubrackSelect() {
    const $select = $('#mutasi-subrack');
    $select.append('<option value="">- Pilih Lokasi -</option>');
    cfg.subRacks.forEach((sr) => {
        $select.append(`<option value="${sr.id}">${sr.rack?.name ?? ''} — ${sr.code}</option>`);
    });
}

function renderRakList(racks) {
    if (racks.length === 0) {
        $('#rak-list').html(`
            <div class="empty-state">
                <i class="bi bi-grid-3x3-gap empty-state__icon"></i>
                <div class="empty-state__title">Belum ada rak</div>
            </div>`);
        return;
    }

    const html = racks.map((rack) => `
        <div class="border rounded-3 p-3 mb-3" style="border-color: var(--border-color) !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <span class="fw-bold">${rack.code}</span>
                    <span class="text-secondary-app">— ${rack.name}</span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="add-subrak" data-rack-id="${rack.id}" data-rack-name="${rack.name}">
                        <i class="bi bi-plus-lg"></i> Sub Rak
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit-rak" data-id="${rack.id}"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-rak" data-id="${rack.id}"><i class="bi bi-trash"></i></button>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                ${rack.sub_racks.length === 0 ? '<span class="text-secondary-app small">Belum ada sub rak</span>' : rack.sub_racks.map((sr) => `
                    <span class="badge-status badge-muted" style="cursor:default;">
                        ${sr.code} (${sr.products_count} produk)
                        <button type="button" class="btn btn-sm p-0 ms-1 border-0" data-action="edit-subrak" data-id="${sr.id}" title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <button type="button" class="btn btn-sm p-0 ms-1 border-0 text-danger" data-action="delete-subrak" data-id="${sr.id}" title="Hapus"><i class="bi bi-x-circle"></i></button>
                    </span>
                `).join('')}
            </div>
        </div>
    `).join('');

    $('#rak-list').html(html);
}

function loadRakList() {
    $.get(cfg.routes.rakData).done((res) => renderRakList(res.data)).fail(window.handleAjaxError);
}

function initRakTab() {
    loadRakList();

    $('#modal-rak').on('show.bs.modal', function (e) {
        if (e.relatedTarget?.dataset.mode === 'create') {
            $('#form-rak')[0].reset();
            $('#rak-id').val('');
            $('#modal-rak-title').text('Tambah Rak');
        }
    });

    $('#rak-list').on('click', '[data-action="edit-rak"]', function () {
        const id = $(this).data('id');
        $.get(`${cfg.routes.rakBase}/${id}`).done((res) => {
            $('#rak-id').val(res.data.id);
            $('#rak-code').val(res.data.code);
            $('#rak-name').val(res.data.name);
            $('#rak-description').val(res.data.description ?? '');
            $('#modal-rak-title').text('Edit Rak');
            bootstrap.Modal.getOrCreateInstance('#modal-rak').show();
        });
    });

    $('#rak-list').on('click', '[data-action="delete-rak"]', function () {
        const id = $(this).data('id');
        window.appConfirm({ text: 'Hapus rak ini beserta semua sub rak-nya?' }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({ url: `${cfg.routes.rakBase}/${id}`, method: 'DELETE' })
                .done((res) => { window.appToast('success', res.message); loadRakList(); })
                .fail(window.handleAjaxError);
        });
    });

    $('#form-rak').on('submit', function (e) {
        e.preventDefault();
        const id = $('#rak-id').val();
        const url = id ? `${cfg.routes.rakBase}/${id}` : cfg.routes.rakStore;
        const data = $(this).serializeArray();
        if (id) data.push({ name: '_method', value: 'PUT' });

        $('.is-invalid', this).removeClass('is-invalid');
        $('.invalid-feedback', this).remove();

        $.post(url, $.param(data))
            .done((res) => {
                window.appToast('success', res.message);
                bootstrap.Modal.getInstance('#modal-rak')?.hide();
                loadRakList();
            })
            .fail((xhr) => {
                if (xhr.status === 422) showValidationErrors($('#form-rak'), xhr.responseJSON.errors);
                else window.handleAjaxError(xhr);
            });
    });

    $('#rak-list').on('click', '[data-action="add-subrak"]', function () {
        $('#form-subrak')[0].reset();
        $('#subrak-id').val('');
        $('#subrak-rack-id').val($(this).data('rack-id'));
        $('#subrak-rack-name').val($(this).data('rack-name'));
        $('#modal-subrak-title').text('Tambah Sub Rak');
        bootstrap.Modal.getOrCreateInstance('#modal-subrak').show();
    });

    $('#rak-list').on('click', '[data-action="edit-subrak"]', function () {
        const id = $(this).data('id');
        $.get(`${cfg.routes.subRakBase}/${id}`).done((res) => {
            $('#subrak-id').val(res.data.id);
            $('#subrak-rack-id').val(res.data.rack_id);
            $('#subrak-code').val(res.data.code);
            $('#subrak-name').val(res.data.name);
            $('#subrak-rack-name').val(res.data.rack?.name ?? '');
            $('#modal-subrak-title').text('Edit Sub Rak');
            bootstrap.Modal.getOrCreateInstance('#modal-subrak').show();
        });
    });

    $('#rak-list').on('click', '[data-action="delete-subrak"]', function () {
        const id = $(this).data('id');
        window.appConfirm({ text: 'Hapus sub rak ini?' }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({ url: `${cfg.routes.subRakBase}/${id}`, method: 'DELETE' })
                .done((res) => { window.appToast('success', res.message); loadRakList(); })
                .fail(window.handleAjaxError);
        });
    });

    $('#form-subrak').on('submit', function (e) {
        e.preventDefault();
        const id = $('#subrak-id').val();
        const url = id ? `${cfg.routes.subRakBase}/${id}` : cfg.routes.subRakStore;
        const data = $(this).serializeArray();
        if (id) data.push({ name: '_method', value: 'PUT' });

        $('.is-invalid', this).removeClass('is-invalid');
        $('.invalid-feedback', this).remove();

        $.post(url, $.param(data))
            .done((res) => {
                window.appToast('success', res.message);
                bootstrap.Modal.getInstance('#modal-subrak')?.hide();
                loadRakList();
            })
            .fail((xhr) => {
                if (xhr.status === 422) showValidationErrors($('#form-subrak'), xhr.responseJSON.errors);
                else window.handleAjaxError(xhr);
            });
    });
}

function loadStockOutAnalytics() {
    $.get(cfg.routes.analitikStockOut).done((res) => {
        if (res.data.length === 0) {
            $('#analitik-stockout-body').html('<tr><td colspan="4" class="text-center text-secondary-app py-3">Belum ada data penjualan untuk diprediksi</td></tr>');
            return;
        }

        $('#analitik-stockout-body').html(res.data.map((row) => `
            <tr>
                <td>${row.name} <span class="text-secondary-app small">(${row.kode_barang})</span></td>
                <td class="text-end">${window.formatNumber(row.current_stock)}</td>
                <td class="text-end">${row.avg_daily_qty}</td>
                <td class="text-end fw-semibold ${row.days_left !== null && row.days_left <= 7 ? 'text-danger' : ''}">${row.days_left !== null ? `${row.days_left} hari` : '-'}</td>
            </tr>`).join(''));
    }).fail(window.handleAjaxError);
}

function loadRestockAnalytics() {
    $.get(cfg.routes.analitikRestock).done((res) => {
        if (res.data.length === 0) {
            $('#analitik-restock-body').html('<tr><td colspan="4" class="text-center text-secondary-app py-3">Tidak ada produk yang perlu segera direstock</td></tr>');
            return;
        }

        $('#analitik-restock-body').html(res.data.map((row) => `
            <tr>
                <td>${row.name} <span class="text-secondary-app small">(${row.supplier ?? '-'})</span></td>
                <td class="text-end text-danger fw-semibold">${row.days_left} hari</td>
                <td class="text-end">${row.lead_time_days} hari</td>
                <td class="text-end fw-semibold">${window.formatNumber(row.suggested_qty)}</td>
            </tr>`).join(''));
    }).fail(window.handleAjaxError);
}

function loadSlowMovingAnalytics() {
    const days = $('#analitik-slowmoving-days').val();

    $.get(cfg.routes.analitikSlowMoving, { days }).done((res) => {
        if (res.data.length === 0) {
            $('#analitik-slowmoving-body').html('<tr><td colspan="3" class="text-center text-secondary-app py-3">Semua produk terjual dalam periode ini</td></tr>');
            return;
        }

        $('#analitik-slowmoving-body').html(res.data.map((row) => `
            <tr>
                <td>${row.name} <span class="text-secondary-app small">(${row.kode_barang})</span></td>
                <td>${row.category ?? '-'}</td>
                <td class="text-end">${window.formatNumber(row.current_stock)}</td>
            </tr>`).join(''));
    }).fail(window.handleAjaxError);
}

function initAnalitikTab() {
    let loaded = false;

    document.querySelector('[data-bs-target="#tab-analitik"]')?.addEventListener('shown.bs.tab', () => {
        if (loaded) return;
        loaded = true;
        loadStockOutAnalytics();
        loadRestockAnalytics();
        loadSlowMovingAnalytics();
    });

    $('#analitik-slowmoving-days').on('change', loadSlowMovingAnalytics);
}

$(function () {
    if (!document.getElementById('table-riwayat')) return;

    initSubrackSelect();
    const riwayatTable = initRiwayat();
    initStockForms(riwayatTable);
    initRakTab();
    initAnalitikTab();
});
