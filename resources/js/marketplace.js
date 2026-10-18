const cfg = window.__marketplacePage;

function showValidationErrors($form, errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const $input = $form.find(`[name="${field}"]`);
        $input.addClass('is-invalid');
        $input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
    });
}

function initImportForm() {
    $('#form-import').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const $btn = $(this).find('button[type=submit]');
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Mengimpor...');
        $('#import-result').empty();

        $.ajax({
            url: cfg.routes.import, method: 'POST', data: formData, processData: false, contentType: false,
        }).done((res) => {
            window.appToast('success', res.message);
            let html = `<div class="alert alert-success rounded-3 py-2 small mb-0">${res.data.created} pesanan berhasil diimpor.</div>`;
            if (res.data.skipped?.length) {
                html += `<div class="alert alert-warning rounded-3 py-2 small mt-2 mb-0">
                    <strong>${res.data.skipped.length} baris dilewati:</strong>
                    <ul class="mb-0 ps-3">${res.data.skipped.map((s) => `<li>${s}</li>`).join('')}</ul>
                </div>`;
            }
            $('#import-result').html(html);
            $('#form-import')[0].reset();
        }).fail((xhr) => {
            window.appToast('error', xhr.responseJSON?.message || 'Gagal mengimpor file.');
        }).always(() => {
            $btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i>Import Sekarang');
        });
    });
}

function initMappingTable() {
    const dt = $('#table-mapping').DataTable({
        ajax: (_, callback) => {
            $.get(cfg.routes.mappingData).done((res) => callback({ data: res.data })).fail(window.handleAjaxError);
        },
        columns: [
            { data: null, render: (row) => window.truncateCell(`${row.product.kode_barang} — ${row.product.name}`) },
            { data: 'marketplace', className: 'dt-nowrap', render: (v) => `<span class="badge-status badge-info">${v === 'shopee' ? 'Shopee' : 'TikTok'}</span>` },
            { data: 'marketplace_sku', className: 'dt-nowrap' },
            { data: 'marketplace_product_name', render: (v) => window.truncateCell(v) },
            {
                data: null, orderable: false, searchable: false, className: 'dt-nowrap',
                render: (row) => `
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                    </div>`,
            },
        ],
        language: window.DataTable.defaults.language,
    });

    $('#modal-mapping').on('show.bs.modal', function (e) {
        if (e.relatedTarget?.dataset.mode === 'create') {
            $('#form-mapping')[0].reset();
            $('#mapping-id').val('');
            $('#modal-mapping-title').text('Tambah Mapping SKU');
        }
    });

    $('#table-mapping').on('click', '[data-action="edit"]', function () {
        const id = $(this).data('id');
        $.get(`${cfg.routes.mappingBase}/${id}`).done((res) => {
            $('#mapping-id').val(res.data.id);
            $('#mapping-product').val(res.data.product_id);
            $('#mapping-marketplace').val(res.data.marketplace);
            $('#mapping-sku').val(res.data.marketplace_sku);
            $('#mapping-name').val(res.data.marketplace_product_name ?? '');
            $('#modal-mapping-title').text('Edit Mapping SKU');
            bootstrap.Modal.getOrCreateInstance('#modal-mapping').show();
        });
    });

    $('#table-mapping').on('click', '[data-action="delete"]', function () {
        const id = $(this).data('id');
        window.appConfirm({ text: 'Hapus mapping ini?' }).then((r) => {
            if (!r.isConfirmed) return;
            $.ajax({ url: `${cfg.routes.mappingBase}/${id}`, method: 'DELETE' })
                .done((res) => { window.appToast('success', res.message); dt.ajax.reload(null, false); })
                .fail(window.handleAjaxError);
        });
    });

    $('#form-mapping').on('submit', function (e) {
        e.preventDefault();
        const id = $('#mapping-id').val();
        const url = id ? `${cfg.routes.mappingBase}/${id}` : cfg.routes.mappingStore;
        const data = $(this).serializeArray();
        if (id) data.push({ name: '_method', value: 'PUT' });

        $('.is-invalid', this).removeClass('is-invalid');
        $('.invalid-feedback', this).remove();

        $.post(url, $.param(data))
            .done((res) => {
                window.appToast('success', res.message);
                bootstrap.Modal.getInstance('#modal-mapping')?.hide();
                dt.ajax.reload(null, false);
            })
            .fail((xhr) => {
                if (xhr.status === 422) showValidationErrors($('#form-mapping'), xhr.responseJSON.errors);
                else window.handleAjaxError(xhr);
            });
    });
}

$(function () {
    if (!document.getElementById('table-mapping')) return;

    initImportForm();
    initMappingTable();
});
