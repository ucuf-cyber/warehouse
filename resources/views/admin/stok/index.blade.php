<x-app-layout pageTitle="Stok" pageSubtitle="Stock in/out, penyesuaian, mutasi lokasi, dan riwayat">
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-riwayat">
                <i class="bi bi-clock-history me-1"></i>Riwayat Stok
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-rak">
                <i class="bi bi-grid-3x3-gap me-1"></i>Lokasi Rak
            </button>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('stok.opname.index') }}">
                <i class="bi bi-clipboard-check me-1"></i>Stock Opname
            </a>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-analitik">
                <i class="bi bi-graph-up me-1"></i>Analitik
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- ================= RIWAYAT ================= --}}
        <div class="tab-pane fade show active" id="tab-riwayat">
            <div class="app-card">
                <div class="app-card__header flex-wrap gap-2">
                    <h6 class="mb-0">Riwayat Pergerakan Stok</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <select id="filter-produk" class="form-select form-select-sm" style="width:220px">
                            <option value="">Semua Produk</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->kode_barang }} — {{ $product->name }}</option>
                            @endforeach
                        </select>
                        <select id="filter-type" class="form-select form-select-sm" style="width:160px">
                            <option value="">Semua Jenis</option>
                            <option value="in">Masuk</option>
                            <option value="out">Keluar</option>
                            <option value="adjustment">Penyesuaian</option>
                            <option value="opname">Stock Opname</option>
                            <option value="damage">Rusak</option>
                            <option value="lost">Hilang</option>
                            <option value="return">Retur</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table-riwayat" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Produk</th>
                                <th>Jenis</th>
                                <th>Qty</th>
                                <th>Sebelum</th>
                                <th>Sesudah</th>
                                <th>Catatan</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= LOKASI RAK ================= --}}
        <div class="tab-pane fade" id="tab-rak">
            <div class="app-card">
                <div class="app-card__header">
                    <h6 class="mb-0">Rak &amp; Sub Rak</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-rak" data-mode="create">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Rak
                    </button>
                </div>
                <div id="rak-list"></div>
            </div>
        </div>

        {{-- ================= ANALITIK ================= --}}
        <div class="tab-pane fade" id="tab-analitik">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <div class="app-card h-100">
                        <div class="app-card__header">
                            <h6 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>Prediksi Stok Habis</h6>
                        </div>
                        <p class="text-secondary-app small">Berdasarkan rata-rata penjualan 30 hari terakhir.</p>
                        <div class="table-responsive" style="max-height:360px; overflow-y:auto;">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Produk</th><th class="text-end">Stok</th><th class="text-end">Rata2/Hari</th><th class="text-end">Estimasi Habis</th></tr></thead>
                                <tbody id="analitik-stockout-body">
                                    <tr><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="app-card h-100">
                        <div class="app-card__header">
                            <h6 class="mb-0"><i class="bi bi-cart-check me-2"></i>Rekomendasi Restock</h6>
                        </div>
                        <p class="text-secondary-app small">Produk yang akan habis sebelum supplier bisa mengirim ulang.</p>
                        <div class="table-responsive" style="max-height:360px; overflow-y:auto;">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Produk</th><th class="text-end">Sisa Hari</th><th class="text-end">Lead Time</th><th class="text-end">Saran Order</th></tr></thead>
                                <tbody id="analitik-restock-body">
                                    <tr><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="app-card">
                        <div class="app-card__header">
                            <h6 class="mb-0"><i class="bi bi-snow me-2"></i>Produk Slow Moving</h6>
                            <select id="analitik-slowmoving-days" class="form-select form-select-sm" style="width:160px">
                                <option value="30">Tanpa penjualan 30 hari</option>
                                <option value="60">Tanpa penjualan 60 hari</option>
                                <option value="90">Tanpa penjualan 90 hari</option>
                            </select>
                        </div>
                        <div class="table-responsive" style="max-height:360px; overflow-y:auto;">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Produk</th><th>Kategori</th><th class="text-end">Stok Saat Ini</th></tr></thead>
                                <tbody id="analitik-slowmoving-body">
                                    <tr><td colspan="3" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="fab">
        <div class="dropdown dropup">
            <button type="button" class="fab-btn" data-bs-toggle="dropdown" title="Aksi Stok">
                <i class="bi bi-plus-lg"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end glass-panel shadow-none">
                <li><button class="dropdown-item rounded-3" type="button" data-bs-toggle="modal" data-bs-target="#modal-stock-in"><i class="bi bi-box-arrow-in-down-left me-2"></i>Stock In</button></li>
                <li><button class="dropdown-item rounded-3" type="button" data-bs-toggle="modal" data-bs-target="#modal-stock-out"><i class="bi bi-box-arrow-up-right me-2"></i>Stock Out</button></li>
                <li><button class="dropdown-item rounded-3" type="button" data-bs-toggle="modal" data-bs-target="#modal-adjustment"><i class="bi bi-sliders me-2"></i>Penyesuaian</button></li>
                <li><button class="dropdown-item rounded-3" type="button" data-bs-toggle="modal" data-bs-target="#modal-mutasi"><i class="bi bi-arrow-left-right me-2"></i>Mutasi Lokasi</button></li>
            </ul>
        </div>
    </x-slot>

    @include('admin.stok.partials.modal-stock-in')
    @include('admin.stok.partials.modal-stock-out')
    @include('admin.stok.partials.modal-adjustment')
    @include('admin.stok.partials.modal-mutasi')
    @include('admin.stok.partials.modal-rak')

    @push('scripts')
        <script>
            window.__stokPage = {
                products: @json($products),
                subRacks: @json($subRacks),
                routes: {
                    riwayatData: '{{ route('stok.data') }}',
                    stockIn: '{{ route('stok.in') }}',
                    stockOut: '{{ route('stok.out') }}',
                    adjustment: '{{ route('stok.adjustment') }}',
                    mutasi: '{{ route('stok.mutasi') }}',
                    rakData: '{{ route('stok.rak.data') }}',
                    rakStore: '{{ route('stok.rak.store') }}',
                    rakBase: '{{ url('/stok/rak') }}',
                    subRakStore: '{{ route('stok.subrak.store') }}',
                    subRakBase: '{{ url('/stok/sub-rak') }}',
                    analitikStockOut: '{{ route('stok.analitik.stockout') }}',
                    analitikRestock: '{{ route('stok.analitik.restock') }}',
                    analitikSlowMoving: '{{ route('stok.analitik.slowmoving') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
