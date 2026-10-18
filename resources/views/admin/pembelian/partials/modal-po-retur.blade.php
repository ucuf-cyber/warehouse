<div class="modal fade" id="modal-po-retur" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <form id="form-po-retur" novalidate>
                <input type="hidden" name="purchase_id" id="retur-purchase-id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Buat Retur Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Tanggal Retur</label>
                            <input type="date" name="return_date" class="form-control" required value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Alasan</label>
                            <input type="text" name="reason" class="form-control" maxlength="500" placeholder="cth: Barang rusak">
                        </div>
                    </div>
                    <label class="form-label fw-semibold small">Pilih Item yang Diretur</label>
                    <div id="retur-items"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3">Simpan Retur</button>
                </div>
            </form>
        </div>
    </div>
</div>
