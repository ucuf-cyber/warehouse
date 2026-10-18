<x-app-layout pageTitle="Produk" pageSubtitle="Kelola produk, kategori, brand, dan supplier">
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-produk">
                <i class="bi bi-box-seam me-1"></i>Produk
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-kategori">
                <i class="bi bi-tags me-1"></i>Kategori
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-brand">
                <i class="bi bi-award me-1"></i>Brand
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-supplier">
                <i class="bi bi-truck me-1"></i>Supplier
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- ================= PRODUK ================= --}}
        <div class="tab-pane fade show active" id="tab-produk">
            <div class="app-card">
                <div class="app-card__header flex-wrap gap-2">
                    <h6 class="mb-0">Daftar Produk</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <select id="filter-kategori" class="form-select form-select-sm" style="width:170px">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <select id="filter-status" class="form-select form-select-sm" style="width:140px">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        <div class="form-check d-flex align-items-center gap-1 mb-0">
                            <input type="checkbox" class="form-check-input mt-0" id="filter-low-stock" {{ request('low_stock') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="filter-low-stock">Stok Rendah</label>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-cetak-terpilih" disabled>
                            <i class="bi bi-upc me-1"></i>Cetak Terpilih
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table-produk" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="form-check-input" id="check-all-produk"></th>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga Jual</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= KATEGORI ================= --}}
        <div class="tab-pane fade" id="tab-kategori">
            <div class="app-card">
                <div class="app-card__header">
                    <h6 class="mb-0">Daftar Kategori</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-kategori" data-mode="create">
                        <i class="bi bi-plus-lg me-1"></i>Tambah
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-kategori" class="table table-hover align-middle w-100">
                        <thead>
                            <tr><th>Prefix</th><th>Nama</th><th>Jumlah Produk</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= BRAND ================= --}}
        <div class="tab-pane fade" id="tab-brand">
            <div class="app-card">
                <div class="app-card__header">
                    <h6 class="mb-0">Daftar Brand</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-brand" data-mode="create">
                        <i class="bi bi-plus-lg me-1"></i>Tambah
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-brand" class="table table-hover align-middle w-100">
                        <thead>
                            <tr><th>Nama</th><th>Jumlah Produk</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= SUPPLIER ================= --}}
        <div class="tab-pane fade" id="tab-supplier">
            <div class="app-card">
                <div class="app-card__header">
                    <h6 class="mb-0">Daftar Supplier</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-supplier" data-mode="create">
                        <i class="bi bi-plus-lg me-1"></i>Tambah
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-supplier" class="table table-hover align-middle w-100">
                        <thead>
                            <tr><th>Nama</th><th>Kontak</th><th>Telepon</th><th>Lead Time</th><th>Aksi</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="fab">
        <button type="button" class="fab-btn" data-bs-toggle="modal" data-bs-target="#modal-produk" data-mode="create" title="Tambah Produk">
            <i class="bi bi-plus-lg"></i>
        </button>
    </x-slot>

    @include('admin.produk.partials.modal-produk')
    @include('admin.produk.partials.modal-kategori')
    @include('admin.produk.partials.modal-brand')
    @include('admin.produk.partials.modal-supplier')

    @push('scripts')
        <script>
            window.__produkPage = {
                categories: @json($categories),
                brands: @json($brands),
                suppliers: @json($suppliers),
                routes: {
                    produkData: '{{ route('produk.data') }}',
                    produkStore: '{{ route('produk.store') }}',
                    produkShow: '{{ url('/produk') }}',
                    barcodePrint: '{{ route('produk.barcode.print') }}',
                    kategoriData: '{{ route('produk.kategori.data') }}',
                    kategoriStore: '{{ route('produk.kategori.store') }}',
                    kategoriBase: '{{ url('/produk/kategori') }}',
                    brandData: '{{ route('produk.brand.data') }}',
                    brandStore: '{{ route('produk.brand.store') }}',
                    brandBase: '{{ url('/produk/brand') }}',
                    supplierData: '{{ route('produk.supplier.data') }}',
                    supplierStore: '{{ route('produk.supplier.store') }}',
                    supplierBase: '{{ url('/produk/supplier') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
