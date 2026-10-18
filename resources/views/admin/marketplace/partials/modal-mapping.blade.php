<div class="modal fade" id="modal-mapping" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <form id="form-mapping" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-mapping-title">Tambah Mapping SKU</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="mapping-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Produk Internal</label>
                        <select name="product_id" id="mapping-product" class="form-select" required>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->kode_barang }} — {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Marketplace</label>
                        <select name="marketplace" id="mapping-marketplace" class="form-select" required>
                            <option value="shopee">Shopee</option>
                            <option value="tiktok">TikTok</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">SKU di Marketplace</label>
                        <input type="text" name="marketplace_sku" id="mapping-sku" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Produk di Marketplace</label>
                        <input type="text" name="marketplace_product_name" id="mapping-name" class="form-control" maxlength="255">
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
