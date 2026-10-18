<div class="modal fade" id="modal-rak" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-rak" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-rak-title">Tambah Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="rak-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kode Rak</label>
                        <input type="text" name="code" id="rak-code" class="form-control" required maxlength="20" placeholder="cth: R1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Rak</label>
                        <input type="text" name="name" id="rak-name" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Deskripsi</label>
                        <textarea name="description" id="rak-description" class="form-control" rows="2" maxlength="255"></textarea>
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

<div class="modal fade" id="modal-subrak" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-subrak" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-subrak-title">Tambah Sub Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="subrak-id">
                    <input type="hidden" name="rack_id" id="subrak-rack-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Rak Induk</label>
                        <input type="text" id="subrak-rack-name" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kode Sub Rak</label>
                        <input type="text" name="code" id="subrak-code" class="form-control" required maxlength="20" placeholder="cth: R1-A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Sub Rak</label>
                        <input type="text" name="name" id="subrak-name" class="form-control" required maxlength="100">
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
