const cfg = window.__opnamePage;
let currentItemId = null;

function updateRow(item) {
    const $row = $(`#table-opname-items tr[data-item-id="${item.id}"]`);
    $row.find('.cell-physical').text(item.physical_qty);

    const diffClass = item.difference === 0 ? 'text-secondary-app' : (item.difference > 0 ? 'text-success' : 'text-danger');
    const diffSign = item.difference > 0 ? '+' : '';
    $row.find('.cell-difference').html(`<span class="${diffClass}">${diffSign}${item.difference}</span>`);
    $row.find('.cell-status').html('<span class="badge-status badge-success">Selesai</span>');
}

function updateProgress(progress) {
    const pct = progress.total ? Math.round((progress.checked / progress.total) * 100) : 0;
    $('#opname-progress-bar').css('width', `${pct}%`);
    $('#opname-progress-text').text(`${progress.checked}/${progress.total} produk selesai dihitung`);
}

function showItemForm(item) {
    currentItemId = item.id;
    $('#opname-item-name').text(item.product.name);
    $('#opname-item-code').text(item.product.kode_barang);
    $('#opname-item-system').val(item.system_qty);
    $('#opname-item-physical').val(item.physical_qty ?? item.system_qty).trigger('focus').trigger('select');
    $('#opname-item-form-card').removeClass('d-none');
}

function findByCode(code) {
    if (!code) return;

    $.get(`${cfg.routes.find}/${encodeURIComponent(code)}`)
        .done((res) => showItemForm(res.data))
        .fail(window.handleAjaxError);
}

function initIndexPage() {
    $('#btn-buat-sesi, #btn-buat-sesi-empty').on('click', function () {
        window.appConfirm({
            icon: 'question',
            title: 'Buat sesi stock opname baru?',
            text: 'Semua produk aktif akan dimasukkan ke sesi ini untuk dihitung ulang.',
            confirmButtonText: 'Ya, buat sesi',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post(cfg.routes.store)
                .done((res) => { window.location.href = `${cfg.routes.show}/${res.data.id}`; })
                .fail(window.handleAjaxError);
        });
    });
}

$(function () {
    if (!window.__opnamePage) return;

    if (cfg.page === 'index') {
        initIndexPage();
        return;
    }

    $('#table-opname-items').DataTable({
        order: [],
        language: window.DataTable.defaults.language,
    });

    $('#opname-scan-input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            findByCode($(this).val().trim());
            $(this).val('');
        }
    });

    $('#table-opname-items').on('click', 'tbody tr', function () {
        if (cfg.status === 'completed') return;
        findByCode($(this).data('code'));
    });

    $('#btn-simpan-item').on('click', function () {
        if (!currentItemId) return;

        const physicalQty = $('#opname-item-physical').val();

        $.ajax({
            url: `${cfg.routes.updateItem}/${currentItemId}`,
            method: 'PATCH',
            data: { physical_qty: physicalQty },
        }).done((res) => {
            window.appToast('success', 'Hitungan tersimpan.');
            updateRow(res.data);
            updateProgress(res.progress);
            $('#opname-item-form-card').addClass('d-none');
            $('#opname-scan-input').trigger('focus');
        }).fail(window.handleAjaxError);
    });

    $('#btn-finalisasi').on('click', function () {
        window.appConfirm({
            icon: 'warning',
            title: 'Finalisasi sesi ini?',
            text: 'Selisih stok pada produk yang sudah dihitung akan langsung menyesuaikan stok sistem. Produk yang belum dihitung tidak akan berubah.',
            confirmButtonText: 'Ya, finalisasi',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post(cfg.routes.finalize)
                .done((res) => {
                    window.appToast('success', res.message);
                    setTimeout(() => window.location.reload(), 1000);
                })
                .fail(window.handleAjaxError);
        });
    });

    $('#opname-scan-input').trigger('focus');
});
