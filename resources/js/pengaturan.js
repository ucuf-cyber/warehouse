const cfg = window.__pengaturanPage;

function initProfilForm() {
    $('#form-profil-toko').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        $.ajax({
            url: cfg.routes.updateProfile, method: 'POST', data: formData, processData: false, contentType: false,
        }).done((res) => window.appToast('success', res.message))
            .fail((xhr) => window.appToast('error', xhr.responseJSON?.message || 'Gagal menyimpan.'));
    });
}

function initPrinterForm() {
    $('#form-printer').on('submit', function (e) {
        e.preventDefault();

        $.ajax({ url: cfg.routes.updatePrinter, method: 'PUT', data: $(this).serialize() })
            .done((res) => window.appToast('success', res.message))
            .fail((xhr) => window.appToast('error', xhr.responseJSON?.message || 'Gagal menyimpan.'));
    });
}

function initBackup() {
    $('#btn-run-backup').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Membuat backup...');

        $.post(cfg.routes.backupRun)
            .done((res) => {
                window.appToast('success', res.message);
                setTimeout(() => window.location.reload(), 1200);
            })
            .fail((xhr) => window.appToast('error', xhr.responseJSON?.message || 'Backup gagal.'))
            .always(() => $btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i>Buat Backup Sekarang'));
    });

    $('#table-backup').on('click', '[data-action="delete-backup"]', function () {
        const path = $(this).data('path');

        window.appConfirm({ text: 'Hapus file backup ini?' }).then((r) => {
            if (!r.isConfirmed) return;

            $.ajax({ url: cfg.routes.backupDelete, method: 'DELETE', data: { path } })
                .done((res) => { window.appToast('success', res.message); window.location.reload(); })
                .fail(window.handleAjaxError);
        });
    });
}

function initActivityLog() {
    const dt = $('#table-activity-log').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
            url: cfg.routes.activityLogData,
            data: (d) => {
                d.log_name = $('#filter-log-name').val();
                d.date_from = $('#filter-log-from').val();
                d.date_to = $('#filter-log-to').val();
            },
        },
        columns: [
            { data: 'created_at', className: 'dt-nowrap', render: (v) => new Date(v).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) },
            { data: 'log_name', className: 'dt-nowrap', render: (v) => `<span class="badge-status badge-info text-capitalize">${v ?? 'default'}</span>` },
            { data: 'description_full', render: (v) => window.truncateCell(v) },
            { data: 'causer_name', className: 'dt-nowrap' },
        ],
    });

    $('#filter-log-name, #filter-log-from, #filter-log-to').on('change', () => dt.ajax.reload());
}

function activateTabFromQuery() {
    const tab = new URLSearchParams(window.location.search).get('tab');
    if (!tab) return;

    const trigger = document.querySelector(`[data-bs-target="#tab-${tab}"]`);
    if (trigger) {
        bootstrap.Tab.getOrCreateInstance(trigger).show();
    }
}

$(function () {
    if (!window.__pengaturanPage) return;

    initProfilForm();
    initPrinterForm();
    initBackup();
    initActivityLog();
    activateTabFromQuery();
});
