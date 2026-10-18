<div class="modal fade" id="modal-mutasi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-mutasi" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right text-primary me-2"></i>Mutasi Lokasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Produk</label>
                        <input type="text" class="form-control produk-search" list="datalist-produk" placeholder="Cari kode/nama produk..." autocomplete="off" required>
                        <input type="hidden" name="product_id">
                        <div class="form-text produk-stock-info"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Lokasi Rak Baru</label>
                        <select name="sub_rack_id" id="mutasi-subrack" class="form-select" required></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3">Pindahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
