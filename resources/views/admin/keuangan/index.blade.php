<x-app-layout pageTitle="Keuangan" pageSubtitle="Kas, pemasukan, pengeluaran, dan laporan keuangan">
    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-transaksi"><i class="bi bi-list-ul me-1"></i>Transaksi</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-labarugi"><i class="bi bi-graph-up-arrow me-1"></i>Laba Rugi</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-cashflow"><i class="bi bi-bar-chart-line me-1"></i>Cashflow</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-neraca"><i class="bi bi-columns-gap me-1"></i>Neraca</button></li>
    </ul>

    <div class="tab-content">
        {{-- ================= TRANSAKSI ================= --}}
        <div class="tab-pane fade show active" id="tab-transaksi">
            <div class="row g-3 mb-3">
                <div class="col-6 col-lg-3">
                    <div class="kpi-card">
                        <span class="kpi-label">Kas Saat Ini</span>
                        <span class="kpi-value skeleton skeleton-title" id="kpi-kas">-</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="kpi-card">
                        <span class="kpi-label">Pemasukan Bulan Ini</span>
                        <span class="kpi-value skeleton skeleton-title" id="kpi-income">-</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="kpi-card">
                        <span class="kpi-label">Pengeluaran Bulan Ini</span>
                        <span class="kpi-value skeleton skeleton-title" id="kpi-expense">-</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="kpi-card">
                        <span class="kpi-label">Selisih Bulan Ini</span>
                        <span class="kpi-value skeleton skeleton-title" id="kpi-net">-</span>
                    </div>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card__header flex-wrap gap-2">
                    <h6 class="mb-0">Daftar Transaksi</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <select id="filter-type-tx" class="form-select form-select-sm" style="width:140px">
                            <option value="">Semua Jenis</option>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                        <input type="date" id="filter-from-tx" class="form-control form-control-sm" style="width:150px">
                        <input type="date" id="filter-to-tx" class="form-control form-control-sm" style="width:150px">
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table-transaksi" class="table table-hover align-middle w-100">
                        <thead>
                            <tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Jumlah</th><th>Dicatat Oleh</th><th>Aksi</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= LABA RUGI ================= --}}
        <div class="tab-pane fade" id="tab-labarugi">
            <div class="app-card">
                <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
                    <div>
                        <label class="form-label small fw-semibold">Dari</label>
                        <input type="date" id="lr-from" class="form-control form-control-sm" value="{{ now()->startOfMonth()->toDateString() }}">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Sampai</label>
                        <input type="date" id="lr-to" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-lr-load">Tampilkan</button>
                    <div class="ms-auto d-flex gap-2">
                        <a class="btn btn-sm btn-outline-secondary" id="link-lr-pdf" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
                        <a class="btn btn-sm btn-outline-secondary" id="link-lr-excel"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
                    </div>
                </div>
                <table class="table" id="table-labarugi">
                    <tbody>
                        <tr><td>Omzet Penjualan</td><td class="text-end fw-semibold" id="lr-omzet">-</td></tr>
                        <tr><td>Harga Pokok Penjualan (HPP)</td><td class="text-end text-danger" id="lr-hpp">-</td></tr>
                        <tr class="border-top"><td class="fw-bold">Laba Kotor</td><td class="text-end fw-bold" id="lr-laba-kotor">-</td></tr>
                        <tr><td>Beban Operasional</td><td class="text-end text-danger" id="lr-beban">-</td></tr>
                        <tr class="border-top"><td class="fw-bold fs-5">Laba Bersih</td><td class="text-end fw-bold fs-5" id="lr-laba-bersih">-</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= CASHFLOW ================= --}}
        <div class="tab-pane fade" id="tab-cashflow">
            <div class="app-card">
                <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
                    <div>
                        <label class="form-label small fw-semibold">Dari</label>
                        <input type="date" id="cf-from" class="form-control form-control-sm" value="{{ now()->subMonthsNoOverflow(5)->startOfMonth()->toDateString() }}">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Sampai</label>
                        <input type="date" id="cf-to" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-cf-load">Tampilkan</button>
                    <div class="ms-auto">
                        <a class="btn btn-sm btn-outline-secondary" id="link-cf-excel"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
                    </div>
                </div>
                <div class="chart-wrapper" style="height:320px;">
                    <canvas id="chart-cashflow"></canvas>
                </div>
            </div>
        </div>

        {{-- ================= NERACA ================= --}}
        <div class="tab-pane fade" id="tab-neraca">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <div class="app-card h-100">
                        <h6 class="fw-bold mb-3">Aset</h6>
                        <div class="d-flex justify-content-between border-bottom py-2"><span>Kas</span><span id="nr-kas">-</span></div>
                        <div class="d-flex justify-content-between border-bottom py-2"><span>Nilai Persediaan</span><span id="nr-persediaan">-</span></div>
                        <div class="d-flex justify-content-between py-2 fw-bold"><span>Total Aset</span><span id="nr-total-aset">-</span></div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="app-card h-100">
                        <h6 class="fw-bold mb-3">Kewajiban &amp; Modal</h6>
                        <div class="d-flex justify-content-between border-bottom py-2"><span>Hutang Supplier</span><span id="nr-hutang">-</span></div>
                        <div class="d-flex justify-content-between border-bottom py-2"><span>Modal</span><span id="nr-modal">-</span></div>
                        <div class="d-flex justify-content-between py-2 fw-bold"><span>Total</span><span id="nr-total-pasiva">-</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="fab">
        <button type="button" class="fab-btn" data-bs-toggle="modal" data-bs-target="#modal-transaksi" title="Catat Transaksi">
            <i class="bi bi-plus-lg"></i>
        </button>
    </x-slot>

    @include('admin.keuangan.partials.modal-transaksi')

    @push('scripts')
        <script>
            window.__keuanganPage = {
                expenseCategories: @json($expenseCategories),
                routes: {
                    summary: '{{ route('keuangan.summary') }}',
                    data: '{{ route('keuangan.data') }}',
                    store: '{{ route('keuangan.store') }}',
                    base: '{{ url('/keuangan') }}',
                    labaRugi: '{{ route('keuangan.laba-rugi') }}',
                    cashflow: '{{ route('keuangan.cashflow') }}',
                    neraca: '{{ route('keuangan.neraca') }}',
                    exportPdf: '{{ url('/laporan/export-sync') }}',
                    exportExcel: '{{ url('/laporan/export-sync') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
