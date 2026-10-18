<datalist id="datalist-produk-so">
    @foreach ($products as $product)
        <option value="{{ $product->kode_barang }} — {{ $product->name }}"></option>
    @endforeach
</datalist>

<div class="modal fade" id="modal-penjualan" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <form id="form-penjualan" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Catat Penjualan Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Nama Pelanggan</label>
                            <input type="text" name="customer_name" class="form-control" maxlength="150">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Tanggal</label>
                            <input type="date" name="sale_date" class="form-control" required value="{{ now()->toDateString() }}">
                        </div>
                    </div>

                    <label class="form-label fw-semibold small">Item Produk</label>
                    <div id="so-items"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-so-item">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Item
                    </button>

                    <div class="text-end fw-bold fs-5 mt-3" id="so-total">Total: Rp 0</div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold small">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3">Simpan Penjualan</button>
                </div>
            </form>
        </div>
    </div>
</div>
