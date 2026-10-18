const cfg = window.__penjualanPage;
const produkByLabel = new Map(cfg.products.map((p) => [`${p.kode_barang} — ${p.name}`, p]));
let soItemIndex = 0;

function soItemRowHtml(index) {
    return `
        <div class="row g-2 align-items-end mb-2 so-item-row" data-index="${index}">
            <div class="col-5">
                <input type="text" class="form-control form-control-sm produk-search" list="datalist-produk-so" placeholder="Cari produk..." autocomplete="off" required>
                <input type="hidden" name="items[${index}][product_id]">
                <div class="form-text stock-hint"></div>
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

function addSoItemRow() {
    const $row = $(soItemRowHtml(soItemIndex++));
    $('#so-items').append($row);

    $row.find('.produk-search').on('input', function () {
        const product = produkByLabel.get($(this).val());
        if (product) {
            $row.find('[name$="[product_id]"]').val(product.id);
            $row.find('.item-price').val(product.harga_jual);
            $row.find('.stock-hint').text(`Stok tersedia: ${window.formatNumber(product.current_stock)} ${product.unit}`);
            recalcSoTotal();
        }
    });

    $row.find('.item-qty, .item-price').on('input', recalcSoTotal);
    $row.find('.btn-remove-item').on('click', function () { $row.remove(); recalcSoTotal(); });
}

function recalcSoTotal() {
    let total = 0;
    $('.so-item-row').each(function () {
        total += (Number($(this).find('.item-qty').val()) || 0) * (Number($(this).find('.item-price').val()) || 0);
    });
    $('#so-total').text(`Total: ${window.formatRupiah(total)}`);
}

function marketplaceBadge(mp) {
    const icons = { manual: 'bi-shop', shopee: 'bi-bag-check', tiktok: 'bi-music-note' };
    const labels = { manual: 'Manual', shopee: 'Shopee', tiktok: 'TikTok' };
    return `<span class="badge-status badge-info"><i class="bi ${icons[mp] ?? 'bi-shop'} me-1"></i>${labels[mp] ?? mp}</span>`;
}

function statusBadge(status) {
    const map = {
        pending: ['badge-warning', 'Pending'],
        processing: ['badge-info', 'Diproses'],
        completed: ['badge-success', 'Selesai'],
        cancelled: ['badge-danger', 'Batal'],
    };
    const [cls, label] = map[status] ?? ['badge-muted', status];
    return `<span class="badge-status ${cls}">${label}</span>`;
}

function initTable() {
    const dt = $('#table-penjualan').DataTable({
        processing: true,
        serverSide: true,
        order: [[3, 'desc']],
        ajax: {
            url: cfg.routes.data,
            data: (d) => {
                d.marketplace = $('#filter-marketplace').val();
                d.status = $('#filter-status-so').val();
            },
        },
        columns: [
            { data: 'code', className: 'dt-nowrap' },
            { data: 'marketplace', className: 'dt-nowrap', render: (v) => marketplaceBadge(v) },
            { data: 'customer_name', render: (v) => window.truncateCell(v) },
            { data: 'sale_date', className: 'dt-nowrap', render: (v) => window.formatDateId(v) },
            { data: 'total_amount', className: 'text-end dt-nowrap', render: (v) => window.formatRupiah(v) },
            { data: 'status', className: 'dt-nowrap', render: (v) => statusBadge(v) },
            { data: 'actions', orderable: false, searchable: false, className: 'dt-nowrap' },
        ],
    });

    $('#filter-marketplace, #filter-status-so').on('change', () => dt.ajax.reload());

    return dt;
}

function loadDetail(id) {
    $('#so-detail-body').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    bootstrap.Modal.getOrCreateInstance('#modal-penjualan-detail').show();

    $.get(`${cfg.routes.base}/${id}`).done((res) => {
        const so = res.data;
        $('#so-detail-title').text(so.code);

        const itemsHtml = so.items.map((i) => `
            <tr><td>${i.product.name}</td><td class="text-end">${i.qty}</td><td class="text-end">${window.formatRupiah(i.price)}</td><td class="text-end">${window.formatRupiah(i.subtotal)}</td></tr>
        `).join('');

        $('#so-detail-body').html(`
            <div class="d-flex justify-content-between mb-3">
                <div>${marketplaceBadge(so.marketplace)} ${statusBadge(so.status)}</div>
                <div class="fw-bold">${window.formatRupiah(so.total_amount)}</div>
            </div>
            <div class="small text-secondary-app mb-2">
                Pelanggan: ${so.customer_name ?? '-'}<br>
                ${so.marketplace_order_id ? `No. Pesanan Marketplace: ${so.marketplace_order_id}<br>` : ''}
                Dicatat oleh: ${so.user?.name ?? '-'}
            </div>
            <table class="table table-sm"><thead><tr><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead><tbody>${itemsHtml}</tbody></table>
        `);
    }).fail(window.handleAjaxError);
}

$(function () {
    if (!document.getElementById('table-penjualan')) return;

    addSoItemRow();
    $('#btn-add-so-item').on('click', addSoItemRow);

    $('#modal-penjualan').on('hidden.bs.modal', function () {
        $('#form-penjualan')[0].reset();
        $('#so-items').empty();
        soItemIndex = 0;
        addSoItemRow();
        recalcSoTotal();
    });

    const dt = initTable();

    $('#table-penjualan').on('click', '[data-action="detail"]', function () {
        loadDetail($(this).data('id'));
    });

    $('#table-penjualan').on('click', '[data-action="cancel"]', function () {
        const id = $(this).data('id');
        window.appConfirm({ text: 'Batalkan penjualan ini? Stok yang sudah keluar akan dikembalikan.' }).then((r) => {
            if (!r.isConfirmed) return;
            $.ajax({ url: `${cfg.routes.base}/${id}/cancel`, method: 'PUT' })
                .done((res) => { window.appToast('success', res.message); dt.ajax.reload(null, false); })
                .fail(window.handleAjaxError);
        });
    });

    $('#form-penjualan').on('submit', function (e) {
        e.preventDefault();

        $.post(cfg.routes.store, $(this).serialize())
            .done((res) => {
                window.appToast('success', res.message);
                bootstrap.Modal.getInstance('#modal-penjualan')?.hide();
                dt.ajax.reload();
            })
            .fail((xhr) => {
                window.appToast('error', xhr.responseJSON?.message || 'Gagal menyimpan penjualan.');
            });
    });
});
