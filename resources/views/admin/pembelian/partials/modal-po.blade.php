<datalist id="datalist-produk-po">
    @foreach ($products as $product)
        <option value="{{ $product->kode_barang }} — {{ $product->name }}"></option>
    @endforeach
</datalist>

<div class="modal fade" id="modal-po" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <form id="form-po" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Buat Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Supplier</label>
                            <select name="supplier_id" id="po-supplier" class="form-select" required>
                                <option value="">- Pilih Supplier -</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Tanggal PO</label>
                            <input type="date" name="purchase_date" id="po-date" class="form-control" required value="{{ now()->toDateString() }}">
                        </div>
                    </div>

                    <label class="form-label fw-semibold small">Item Produk</label>
                    <div id="po-items"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-po-item">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Item
                    </button>

                    <div class="text-end fw-bold fs-5 mt-3" id="po-total">Total: Rp 0</div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold small">Catatan</label>
                        <textarea name="notes" id="po-notes" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3">Simpan PO</button>
                </div>
            </form>
        </div>
    </div>
</div>
