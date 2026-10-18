<div class="modal fade" id="modal-kategori" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-kategori" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-kategori-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="kategori-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Kategori</label>
                        <input type="text" name="name" id="kategori-name" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3" id="kategori-prefix-info">
                        <label class="form-label fw-semibold small">Prefix Kode Barang</label>
                        <input type="text" id="kategori-prefix-display" class="form-control text-uppercase" disabled placeholder="Dibuat otomatis dari nama kategori">
                        <div class="form-text">Prefix dibuat otomatis dari nama kategori, dipakai untuk kode barang, contoh: SKN-000001.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Icon (Bootstrap Icons)</label>
                        <input type="text" name="icon" id="kategori-icon" class="form-control" maxlength="60" placeholder="cth: bi-droplet">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Deskripsi</label>
                        <textarea name="description" id="kategori-description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="kategori-is-active" value="1" checked>
                        <label class="form-check-label small" for="kategori-is-active">Aktif</label>
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
