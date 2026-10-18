<div class="modal fade" id="modal-adjustment" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-adjustment" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-sliders text-primary me-2"></i>Penyesuaian Stok</h5>
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
                        <label class="form-label fw-semibold small">Stok Fisik Sebenarnya</label>
                        <input type="number" name="physical_qty" class="form-control" min="0" required>
                        <div class="form-text">Sistem akan menghitung selisih secara otomatis.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="255" placeholder="cth: Koreksi hasil cek fisik"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
