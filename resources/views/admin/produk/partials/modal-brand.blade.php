<div class="modal fade" id="modal-brand" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-brand" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-brand-title">Tambah Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="brand-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Brand</label>
                        <input type="text" name="name" id="brand-name" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Deskripsi</label>
                        <textarea name="description" id="brand-description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="brand-is-active" value="1" checked>
                        <label class="form-check-label small" for="brand-is-active">Aktif</label>
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
