<div class="modal fade" id="modal-transaksi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-transaksi" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Catat Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Jenis</label>
                        <select name="type" id="transaksi-type" class="form-select" required>
                            <option value="expense">Pengeluaran</option>
                            <option value="income">Pemasukan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kategori</label>
                        <select name="category" id="transaksi-category" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Jumlah</label>
                        <input type="number" name="amount" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tanggal</label>
                        <input type="date" name="transaction_date" class="form-control" required value="{{ now()->toDateString() }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Keterangan</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="255"></textarea>
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
