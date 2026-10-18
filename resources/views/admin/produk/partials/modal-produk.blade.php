<div class="modal fade" id="modal-produk" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <form id="form-produk" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-produk-title">Tambah Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="produk-id">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Nama Produk</label>
                            <input type="text" name="name" id="produk-name" class="form-control" required maxlength="150">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold small">Kategori</label>
                            <select name="category_id" id="produk-category" class="form-select" required></select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Brand</label>
                            <select name="brand_id" id="produk-brand" class="form-select">
                                <option value="">- Tanpa Brand -</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold small">Supplier</label>
                            <select name="supplier_id" id="produk-supplier" class="form-select">
                                <option value="">- Pilih Supplier -</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Satuan</label>
                            <input type="text" name="unit" id="produk-unit" class="form-control" value="pcs" required maxlength="30">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold small">Harga Modal</label>
                            <input type="number" name="harga_modal" id="produk-harga-modal" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Harga Jual</label>
                            <input type="number" name="harga_jual" id="produk-harga-jual" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Harga Marketplace</label>
                            <input type="number" name="harga_marketplace" id="produk-harga-marketplace" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Berat (gram)</label>
                            <input type="number" name="berat" id="produk-berat" class="form-control" min="0" value="0">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold small">Status</label>
                            <select name="status" id="produk-status" class="form-select" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Foto Produk</label>
                            <input type="file" name="foto" id="produk-foto" class="form-control" accept="image/*">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Catatan</label>
                            <textarea name="catatan" id="produk-catatan" class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
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

<div class="modal fade" id="modal-produk-detail" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="produk-detail-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>
