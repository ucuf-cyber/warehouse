<div class="modal fade" id="modal-supplier" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-supplier" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-supplier-title">Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="supplier-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Supplier</label>
                        <input type="text" name="name" id="supplier-name" class="form-control" required maxlength="150">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Kontak Person</label>
                            <input type="text" name="contact_person" id="supplier-contact" class="form-control" maxlength="100">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Telepon</label>
                            <input type="text" name="phone" id="supplier-phone" class="form-control" maxlength="20">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" id="supplier-email" class="form-control" maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alamat</label>
                        <textarea name="address" id="supplier-address" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Lead Time (hari)</label>
                        <input type="number" name="lead_time_days" id="supplier-lead-time" class="form-control" min="0" max="365" value="3">
                        <div class="form-text">Dipakai untuk rekomendasi restock otomatis.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="supplier-is-active" value="1" checked>
                        <label class="form-check-label small" for="supplier-is-active">Aktif</label>
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
