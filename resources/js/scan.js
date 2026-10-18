import { Html5Qrcode } from 'html5-qrcode';

function renderResult(product, source) {
    $('#scan-result-empty').addClass('d-none');
    $('#scan-result-card').removeClass('d-none');
    $('#scan-result-initial').text(product.name.substring(0, 1).toUpperCase());
    $('#scan-result-name').text(product.name);

    if (source === 'external') {
        // Not in our catalog — shows what the external lookup returned
        // (brand instead of our internal fields, which don't apply).
        $('#scan-result-badge').removeClass('d-none').text('Belum terdaftar');
        $('#scan-result-code').text(product.brand ?? '-');
        $('#scan-result-category').text('Sumber: database eksternal');
        $('#scan-result-stock').text('-');
        $('#scan-result-price').text('-');
        $('#scan-result-location').text('-');
    } else {
        $('#scan-result-badge').addClass('d-none');
        $('#scan-result-code').text(product.kode_barang);
        $('#scan-result-category').text(product.category ? product.category.name : '-');
        $('#scan-result-stock').text(window.formatNumber(product.current_stock) + ' ' + (product.unit ?? ''));
        $('#scan-result-price').text(window.formatRupiah(product.harga_jual));
        $('#scan-result-location').text(product.sub_rack ? product.sub_rack.code : '-');
    }
}

function lookup(code) {
    if (!code) return;

    $.get('/api/products/scan', { code })
        .done((res) => {
            renderResult(res.data, res.source);

            const label = res.source === 'external' ? 'Ditemukan via database eksternal' : 'Produk ditemukan';
            window.appToast('success', `${label}: ${res.data.name}`);
        })
        .fail(window.handleAjaxError);
}

$(function () {
    // USB scanner (keyboard wedge): types the code then presses Enter.
    const $wedge = $('#scan-wedge-input');
    $wedge.on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            lookup($wedge.val().trim());
            $wedge.val('');
        }
    });
    $wedge.trigger('focus');

    // Camera scanner
    let html5QrCode = null;
    let cameraRunning = false;

    $('#btn-toggle-camera').on('click', async function () {
        const $btn = $(this);

        if (cameraRunning) {
            await html5QrCode?.stop();
            cameraRunning = false;
            $btn.html('<i class="bi bi-camera-video me-1"></i>Mulai Kamera');
            return;
        }

        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Memuat...');
        html5QrCode ??= new Html5Qrcode('scan-reader-camera');

        try {
            await html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => lookup(decodedText),
            );
            cameraRunning = true;
            $btn.html('<i class="bi bi-camera-video-off me-1"></i>Hentikan Kamera');
        } catch (err) {
            window.appToast('error', 'Tidak dapat mengakses kamera.');
        } finally {
            $btn.prop('disabled', false);
        }
    });
});
