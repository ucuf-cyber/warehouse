const cfg = window.__produkPage;

function fillSelect($el, items, { placeholder = null, selected = null } = {}) {
    $el.empty();

    if (placeholder) {
        $el.append(`<option value="">${placeholder}</option>`);
    }

    items.forEach((item) => {
        $el.append(`<option value="${item.id}">${item.name}</option>`);
    });

    if (selected !== null) {
        $el.val(selected);
    }
}

function resetModalForm($form) {
    $form[0].reset();
    $form.find('input[type=hidden][name=id]').val('');
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').remove();
}

function showValidationErrors($form, errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const $input = $form.find(`[name="${field}"]`);
        $input.addClass('is-invalid');
        $input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
    });
}

// ---------------- Generic CRUD tab (Kategori / Brand / Supplier) ----------------
function initCrudTab({ table, form, modal, dataUrl, storeUrl, baseUrl, columns, fieldMap, entityLabel }) {
    const $table = $(table);
    const $form = $(form);

    const dt = $table.DataTable({
        ajax: (_, callback) => {
            $.get(dataUrl).done((res) => callback({ data: res.data })).fail(window.handleAjaxError);
        },
        columns,
        language: window.DataTable.defaults.language,
    });

    $(modal).on('hidden.bs.modal', () => resetModalForm($form));

    $(modal).on('show.bs.modal', function (e) {
        const trigger = e.relatedTarget;

        if (trigger?.dataset.mode === 'create') {
            resetModalForm($form);
            $(`${modal}-title`).text(`Tambah ${entityLabel}`);
        }
    });

    $table.on('click', '[data-action="edit"]', function () {
        const id = $(this).data('id');

        $.get(`${baseUrl}/${id}`).done((res) => {
            resetModalForm($form);
            fieldMap.forEach(([field, selector]) => {
                const val = res.data[field];
                const $input = $form.find(selector);

                if ($input.attr('type') === 'checkbox') {
                    $input.prop('checked', !!val);
                } else {
                    $input.val(val ?? '');
                }
            });
            $form.find('[name=id]').val(id);
            $(`${modal}-title`).text(`Edit ${entityLabel}`);
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }).fail(window.handleAjaxError);
    });

    $table.on('click', '[data-action="delete"]', function () {
        const id = $(this).data('id');

        window.appConfirm({ text: `Hapus ${entityLabel} ini?` }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({ url: `${baseUrl}/${id}`, method: 'DELETE' })
                .done((res) => {
                    window.appToast('success', res.message);
                    dt.ajax.reload(null, false);
                })
                .fail(window.handleAjaxError);
        });
    });

    $form.on('submit', function (e) {
        e.preventDefault();

        const id = $form.find('[name=id]').val();
        const url = id ? `${baseUrl}/${id}` : storeUrl;
        const formData = $form.serializeArray();

        if (id) {
            formData.push({ name: '_method', value: 'PUT' });
        }

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        $.ajax({ url, method: 'POST', data: $.param(formData) })
            .done((res) => {
                window.appToast('success', res.message);
                bootstrap.Modal.getInstance(modal)?.hide();
                dt.ajax.reload(null, false);
            })
            .fail((xhr) => {
                if (xhr.status === 422) {
                    showValidationErrors($form, xhr.responseJSON.errors);
                } else {
                    window.handleAjaxError(xhr);
                }
            });
    });

    return dt;
}

function initProdukTab() {
    // #filter-kategori is already server-rendered by Blade; only the modal's selects need JS population.
    fillSelect($('#produk-category'), cfg.categories, { placeholder: null });
    fillSelect($('#produk-brand'), cfg.brands, {});
    fillSelect($('#produk-supplier'), cfg.suppliers, {});
    $('#produk-brand').prepend('<option value="">- Tanpa Brand -</option>');
    $('#produk-supplier').prepend('<option value="">- Pilih Supplier -</option>');

    const dt = $('#table-produk').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: cfg.routes.produkData,
            data: (d) => {
                d.category_id = $('#filter-kategori').val();
                d.status = $('#filter-status').val();
                d.low_stock = $('#filter-low-stock').is(':checked') ? 1 : '';
            },
        },
        columns: [
            {
                data: 'id', orderable: false, searchable: false,
                render: (id) => `<input type="checkbox" class="form-check-input row-check" value="${id}">`,
            },
            { data: 'kode_barang', className: 'dt-nowrap' },
            { data: 'name', render: (v) => window.truncateCell(v) },
            { data: 'category_name', className: 'dt-nowrap' },
            {
                data: 'current_stock', className: 'text-end dt-nowrap',
                render: (val, type, row) => {
                    // Kept in sync with Product::MIN_STOCK_THRESHOLD (app/Models/Product.php).
                    const low = val <= 5 ? ' <span class="badge-status badge-warning">Rendah</span>' : '';

                    return `${window.formatNumber(val)} ${row.unit}${low}`;
                },
            },
            { data: 'harga_jual', className: 'text-end dt-nowrap', render: (val) => window.formatRupiah(val) },
            {
                data: 'status', className: 'dt-nowrap', render: (val) => (val === 'active'
                    ? '<span class="badge-status badge-success">Aktif</span>'
                    : '<span class="badge-status badge-muted">Nonaktif</span>'),
            },
            { data: 'actions', orderable: false, searchable: false, className: 'dt-nowrap' },
        ],
    });

    $('#filter-kategori, #filter-status, #filter-low-stock').on('change', () => dt.ajax.reload());

    $('#check-all-produk').on('change', function () {
        $('.row-check').prop('checked', this.checked);
        toggleCetakButton();
    });

    $('#table-produk').on('change', '.row-check', toggleCetakButton);

    function toggleCetakButton() {
        $('#btn-cetak-terpilih').prop('disabled', $('.row-check:checked').length === 0);
    }

    $('#btn-cetak-terpilih').on('click', () => {
        const ids = $('.row-check:checked').map(function () { return this.value; }).get();
        window.open(`${cfg.routes.barcodePrint}?ids=${ids.join(',')}`, '_blank');
    });

    $('#modal-produk').on('show.bs.modal', function (e) {
        const trigger = e.relatedTarget;

        if (trigger?.dataset.mode === 'create') {
            resetModalForm($('#form-produk'));
            $('#modal-produk-title').text('Tambah Produk');
        }
    });

    $('#modal-produk').on('hidden.bs.modal', () => resetModalForm($('#form-produk')));

    $('#table-produk').on('click', '[data-action="edit"]', function () {
        const id = $(this).data('id');

        $.get(`${cfg.routes.produkShow}/${id}`).done((res) => {
            const p = res.data;
            resetModalForm($('#form-produk'));

            $('#produk-id').val(p.id);
            $('#produk-name').val(p.name);
            $('#produk-category').val(p.category_id);
            $('#produk-brand').val(p.brand_id ?? '');
            $('#produk-supplier').val(p.supplier_id ?? '');
            $('#produk-unit').val(p.unit);
            $('#produk-berat').val(p.berat);
            $('#produk-harga-modal').val(p.harga_modal);
            $('#produk-harga-jual').val(p.harga_jual);
            $('#produk-harga-marketplace').val(p.harga_marketplace);
            $('#produk-status').val(p.status);
            $('#produk-catatan').val(p.catatan ?? '');

            $('#modal-produk-title').text(`Edit Produk — ${p.kode_barang}`);
            bootstrap.Modal.getOrCreateInstance('#modal-produk').show();
        }).fail(window.handleAjaxError);
    });

    $('#table-produk').on('click', '[data-action="detail"]', function () {
        const id = $(this).data('id');
        const modal = bootstrap.Modal.getOrCreateInstance('#modal-produk-detail');
        modal.show();

        $.get(`${cfg.routes.produkShow}/${id}`).done((res) => {
            const p = res.data;
            $('#produk-detail-body').html(`
                <div class="d-flex gap-3 mb-3">
                    <div class="avatar-circle" style="width:64px;height:64px;font-size:1.3rem;">${p.name.substring(0, 1).toUpperCase()}</div>
                    <div>
                        <div class="fw-bold fs-5">${p.name}</div>
                        <div class="text-secondary-app">${p.kode_barang}</div>
                    </div>
                </div>
                <div class="row g-2 small">
                    <div class="col-6"><span class="text-secondary-app">Kategori</span><div class="fw-semibold">${p.category?.name ?? '-'}</div></div>
                    <div class="col-6"><span class="text-secondary-app">Brand</span><div class="fw-semibold">${p.brand?.name ?? '-'}</div></div>
                    <div class="col-6"><span class="text-secondary-app">Supplier</span><div class="fw-semibold">${p.supplier?.name ?? '-'}</div></div>
                    <div class="col-6"><span class="text-secondary-app">Stok Saat Ini</span><div class="fw-semibold">${window.formatNumber(p.current_stock)} ${p.unit}</div></div>
                    <div class="col-6"><span class="text-secondary-app">Harga Modal</span><div class="fw-semibold">${window.formatRupiah(p.harga_modal)}</div></div>
                    <div class="col-6"><span class="text-secondary-app">Harga Jual</span><div class="fw-semibold">${window.formatRupiah(p.harga_jual)}</div></div>
                </div>
            `);
        });
    });

    $('#table-produk').on('click', '[data-action="delete"]', function () {
        const id = $(this).data('id');

        window.appConfirm({ text: 'Produk yang dihapus dapat dipulihkan oleh admin sistem.' }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({ url: `${cfg.routes.produkShow}/${id}`, method: 'DELETE' })
                .done((res) => {
                    window.appToast('success', res.message);
                    dt.ajax.reload(null, false);
                })
                .fail(window.handleAjaxError);
        });
    });

    $('#form-produk').on('submit', function (e) {
        e.preventDefault();

        const id = $('#produk-id').val();
        const url = id ? `${cfg.routes.produkShow}/${id}` : cfg.routes.produkStore;
        const formData = new FormData(this);

        if (id) {
            formData.append('_method', 'PUT');
        }

        $('#form-produk .is-invalid').removeClass('is-invalid');
        $('#form-produk .invalid-feedback').remove();

        $.ajax({
            url, method: 'POST', data: formData, processData: false, contentType: false,
        }).done((res) => {
            window.appToast('success', res.message);
            bootstrap.Modal.getInstance('#modal-produk')?.hide();
            dt.ajax.reload(null, false);
        }).fail((xhr) => {
            if (xhr.status === 422) {
                showValidationErrors($('#form-produk'), xhr.responseJSON.errors);
            } else {
                window.handleAjaxError(xhr);
            }
        });
    });
}

$(function () {
    if (!document.getElementById('table-produk')) return;

    initProdukTab();

    initCrudTab({
        table: '#table-kategori',
        form: '#form-kategori',
        modal: '#modal-kategori',
        dataUrl: cfg.routes.kategoriData,
        storeUrl: cfg.routes.kategoriStore,
        baseUrl: cfg.routes.kategoriBase,
        entityLabel: 'Kategori',
        fieldMap: [
            ['name', '[name=name]'], ['prefix', '#kategori-prefix-display'], ['icon', '[name=icon]'],
            ['description', '[name=description]'], ['is_active', '[name=is_active]'],
        ],
        columns: [
            { data: 'prefix', className: 'dt-nowrap' },
            { data: 'name', render: (v) => window.truncateCell(v) },
            { data: 'products_count', className: 'dt-nowrap' },
            {
                data: 'is_active', className: 'dt-nowrap', render: (v) => (v
                    ? '<span class="badge-status badge-success">Aktif</span>'
                    : '<span class="badge-status badge-muted">Nonaktif</span>'),
            },
            {
                data: null, orderable: false, searchable: false, className: 'dt-nowrap',
                render: (row) => `
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                    </div>`,
            },
        ],
    });

    initCrudTab({
        table: '#table-brand',
        form: '#form-brand',
        modal: '#modal-brand',
        dataUrl: cfg.routes.brandData,
        storeUrl: cfg.routes.brandStore,
        baseUrl: cfg.routes.brandBase,
        entityLabel: 'Brand',
        fieldMap: [
            ['name', '[name=name]'], ['description', '[name=description]'], ['is_active', '[name=is_active]'],
        ],
        columns: [
            { data: 'name', render: (v) => window.truncateCell(v) },
            { data: 'products_count', className: 'dt-nowrap' },
            {
                data: 'is_active', className: 'dt-nowrap', render: (v) => (v
                    ? '<span class="badge-status badge-success">Aktif</span>'
                    : '<span class="badge-status badge-muted">Nonaktif</span>'),
            },
            {
                data: null, orderable: false, searchable: false, className: 'dt-nowrap',
                render: (row) => `
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                    </div>`,
            },
        ],
    });

    initCrudTab({
        table: '#table-supplier',
        form: '#form-supplier',
        modal: '#modal-supplier',
        dataUrl: cfg.routes.supplierData,
        storeUrl: cfg.routes.supplierStore,
        baseUrl: cfg.routes.supplierBase,
        entityLabel: 'Supplier',
        fieldMap: [
            ['name', '[name=name]'], ['contact_person', '[name=contact_person]'], ['phone', '[name=phone]'],
            ['email', '[name=email]'], ['address', '[name=address]'], ['lead_time_days', '[name=lead_time_days]'],
            ['is_active', '[name=is_active]'],
        ],
        columns: [
            { data: 'name', render: (v) => window.truncateCell(v) },
            { data: 'contact_person', render: (v) => window.truncateCell(v) },
            { data: 'phone', className: 'dt-nowrap', render: (v) => v ?? '-' },
            { data: 'lead_time_days', className: 'dt-nowrap', render: (v) => `${v} hari` },
            {
                data: null, orderable: false, searchable: false, className: 'dt-nowrap',
                render: (row) => `
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                    </div>`,
            },
        ],
    });
});
