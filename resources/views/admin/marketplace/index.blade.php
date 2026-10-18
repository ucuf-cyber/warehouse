<x-app-layout pageTitle="Marketplace" pageSubtitle="Import pesanan Shopee/TikTok dan mapping SKU">
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-import">
                <i class="bi bi-cloud-upload me-1"></i>Import Data
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-mapping">
                <i class="bi bi-link-45deg me-1"></i>Mapping SKU
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- ================= IMPORT ================= --}}
        <div class="tab-pane fade show active" id="tab-import">
            <div class="row g-3">
                <div class="col-12 col-lg-7">
                    <div class="app-card">
                        <h6 class="fw-bold mb-3">Import Pesanan dari File</h6>
                        <form id="form-import">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Sumber</label>
                                <select name="source" id="import-source" class="form-select" required>
                                    <option value="manual">Manual / Export Excel</option>
                                    <option value="shopee">Shopee (API — belum aktif)</option>
                                    <option value="tiktok">TikTok Shop (API — belum aktif)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">File (.xlsx, .xls, .csv)</label>
                                <input type="file" name="file" id="import-file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-3">
                                <i class="bi bi-cloud-upload me-1"></i>Import Sekarang
                            </button>
                            <a href="{{ route('marketplace.template') }}" class="btn btn-outline-secondary rounded-3">
                                <i class="bi bi-download me-1"></i>Download Template
                            </a>
                        </form>
                        <div id="import-result" class="mt-3"></div>
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <div class="app-card h-100">
                        <h6 class="fw-bold mb-3">Status Integrasi</h6>
                        <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                            <span><i class="bi bi-bag-check me-2"></i>Shopee API</span>
                            <span class="badge-status {{ $shopeeEnabled ? 'badge-success' : 'badge-muted' }}">{{ $shopeeEnabled ? 'Aktif' : 'Belum Aktif' }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between py-2">
                            <span><i class="bi bi-music-note me-2"></i>TikTok Shop API</span>
                            <span class="badge-status {{ $tiktokEnabled ? 'badge-success' : 'badge-muted' }}">{{ $tiktokEnabled ? 'Aktif' : 'Belum Aktif' }}</span>
                        </div>
                        <p class="text-secondary-app small mt-3 mb-0">
                            Integrasi API resmi dapat diaktifkan nanti melalui pengaturan <code>.env</code> tanpa mengubah alur import — sistem menggunakan interface <code>MarketplaceSyncInterface</code> yang sama.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= MAPPING SKU ================= --}}
        <div class="tab-pane fade" id="tab-mapping">
            <div class="app-card">
                <div class="app-card__header">
                    <h6 class="mb-0">Mapping SKU Internal ↔ Marketplace</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-mapping" data-mode="create">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Mapping
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-mapping" class="table table-hover align-middle w-100">
                        <thead>
                            <tr><th>Produk</th><th>Marketplace</th><th>SKU Marketplace</th><th>Nama di Marketplace</th><th>Aksi</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.marketplace.partials.modal-mapping')

    @push('scripts')
        <script>
            window.__marketplacePage = {
                products: @json($products),
                routes: {
                    import: '{{ route('marketplace.import') }}',
                    mappingData: '{{ route('marketplace.mapping.data') }}',
                    mappingStore: '{{ route('marketplace.mapping.store') }}',
                    mappingBase: '{{ url('/marketplace/mapping') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
