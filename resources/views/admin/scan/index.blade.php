<x-app-layout pageTitle="Scan Produk" pageSubtitle="Scan barcode/QR via kamera atau scanner USB">
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="app-card h-100">
                <div class="app-card__header">
                    <h6><i class="bi bi-camera me-2"></i>Scan via Kamera</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-toggle-camera">
                        <i class="bi bi-camera-video me-1"></i>Mulai Kamera
                    </button>
                </div>
                <div id="scan-reader-camera" class="rounded-3 overflow-hidden bg-body-tertiary" style="min-height: 280px;"></div>
                <p class="text-secondary-app small mt-2 mb-0">Izinkan akses kamera saat diminta oleh browser.</p>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="app-card h-100">
                <div class="app-card__header">
                    <h6><i class="bi bi-keyboard me-2"></i>Scan via USB Scanner</h6>
                </div>
                <p class="text-secondary-app small">
                    Klik kolom di bawah lalu scan — scanner USB akan mengetik kode secara otomatis dan menekan Enter.
                </p>
                <input type="text" id="scan-wedge-input" class="form-control form-control-lg text-center"
                       placeholder="Klik di sini, lalu scan barcode..." autocomplete="off">

                <hr>

                <h6 class="fw-bold small text-secondary-app mb-2">HASIL SCAN TERAKHIR</h6>
                <div id="scan-result-empty" class="empty-state py-4">
                    <i class="bi bi-upc-scan empty-state__icon"></i>
                    <div class="small text-secondary-app">Belum ada produk yang di-scan</div>
                </div>

                <div id="scan-result-card" class="d-none">
                    <div class="d-flex gap-3">
                        <div class="avatar-circle" style="width:56px;height:56px;font-size:1.1rem;" id="scan-result-initial">?</div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-truncate">
                                <span id="scan-result-name">-</span>
                                <span class="badge-status badge-warning d-none ms-1" id="scan-result-badge">Belum terdaftar</span>
                            </div>
                            <div class="text-secondary-app small" id="scan-result-code">-</div>
                            <div class="mt-1">
                                <span class="badge-status badge-info" id="scan-result-category">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-4">
                            <div class="text-secondary-app small">Stok</div>
                            <div class="fw-bold" id="scan-result-stock">-</div>
                        </div>
                        <div class="col-4">
                            <div class="text-secondary-app small">Harga Jual</div>
                            <div class="fw-bold" id="scan-result-price">-</div>
                        </div>
                        <div class="col-4">
                            <div class="text-secondary-app small">Lokasi</div>
                            <div class="fw-bold" id="scan-result-location">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
