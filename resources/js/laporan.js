const cfg = window.__laporanPage;

$(function () {
    if (!window.__laporanPage) return;

    $('.report-card [data-format]').on('click', function () {
        const $card = $(this).closest('.report-card');
        const type = $card.data('type');
        const isSync = $card.data('sync') === 1;
        const format = $(this).data('format');
        const from = $card.find('.report-from').val();
        const to = $card.find('.report-to').val();

        if (isSync) {
            const qs = new URLSearchParams({ type, format, from, to }).toString();
            window.location.href = `${cfg.routes.exportSync}?${qs}`;
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true);

        $.post(cfg.routes.exportAsync, { type, format, from, to })
            .done((res) => {
                window.appToast('success', res.message);
            })
            .fail((xhr) => {
                window.appToast('error', xhr.responseJSON?.message || 'Gagal memproses laporan.');
            })
            .always(() => $btn.prop('disabled', false));
    });
});
