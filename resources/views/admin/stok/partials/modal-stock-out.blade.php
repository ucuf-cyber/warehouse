<div class="modal fade" id="modal-stock-out" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-stock-out" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-up-right text-danger me-2"></i>Stock Out</h5>
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
                        <label class="form-label fw-semibold small">Jumlah Keluar</label>
                        <input type="number" name="qty" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alasan</label>
                        <select name="type" class="form-select" required>
                            <option value="out">Keluar (penjualan manual/lainnya)</option>
                            <option value="damage">Rusak</option>
                            <option value="lost">Hilang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
