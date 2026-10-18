<x-app-layout pageTitle="Dashboard" pageSubtitle="Ringkasan bisnis Anda">
    <div class="d-flex justify-content-end mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh-dashboard">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Kas / Saldo Saat Ini</span>
                    <span class="kpi-icon" style="background:rgba(34,197,94,.12); color:#22C55E;"><i class="bi bi-wallet2"></i></span>
                </div>
                <span class="kpi-value skeleton skeleton-title" id="kpi-kas">-</span>
                <div class="kpi-sparkline"><canvas id="spark-kas"></canvas></div>
                <span class="kpi-sub" id="kpi-kas-sub">-</span>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Pendapatan (Income)</span>
                    <span class="kpi-icon" style="background:rgba(47,111,237,.12); color:#2F6FED;"><i class="bi bi-graph-up-arrow"></i></span>
                </div>
                <span class="kpi-value skeleton skeleton-title" id="kpi-income">-</span>
                <div class="kpi-sparkline"><canvas id="spark-income"></canvas></div>
                <span class="kpi-sub" id="kpi-income-sub">-</span>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Pengeluaran (Expense)</span>
                    <span class="kpi-icon" style="background:rgba(239,68,68,.12); color:#EF4444;"><i class="bi bi-graph-down-arrow"></i></span>
                </div>
                <span class="kpi-value skeleton skeleton-title" id="kpi-expense">-</span>
                <div class="kpi-sparkline"><canvas id="spark-expense"></canvas></div>
                <span class="kpi-sub" id="kpi-expense-sub">-</span>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('produk.index') }}?low_stock=1" class="text-decoration-none d-flex h-100 w-100">
                <div class="kpi-card kpi-clickable w-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="kpi-label">Produk Perlu Restock</span>
                        <span class="kpi-icon" style="background:rgba(245,158,11,.12); color:#F59E0B;"><i class="bi bi-exclamation-triangle"></i></span>
                    </div>
                    <span class="kpi-value skeleton skeleton-title" id="kpi-restock">-</span>
                    <span class="kpi-sub">Segera lakukan restock</span>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-5">
            <div class="app-card h-100">
                <div class="app-card__header">
                    <h6>Distribusi Stok per Kategori</h6>
                </div>
                <div class="chart-wrapper" style="height:280px;">
                    <canvas id="chart-distribusi"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-7">
            <div class="app-card h-100">
                <div class="app-card__header">
                    <h6>Ringkasan Performa 12 Bulan Terakhir</h6>
                    <select id="filter-tahun" class="form-select form-select-sm" style="width:110px">
                        @foreach (range(now()->year, now()->year - 3) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="chart-wrapper" style="height:280px;">
                    <canvas id="chart-performa"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="app-card">
        <div class="app-card__header">
            <h6>Penjualan Terbaru</h6>
            <a href="{{ route('penjualan.index') }}" class="small fw-semibold">See all →</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:36px;"></th>
                        <th>No. Order</th>
                        <th>Status</th>
                        <th>Marketplace</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                    </tr>
                </thead>
                <tbody id="recent-sales-body">
                    <tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            window.__dashboardPage = {
                routes: {
                    summary: '{{ url('/api/dashboard/summary') }}',
                    stockDistribution: '{{ url('/api/dashboard/chart/stock-distribution') }}',
                    financeSummary: '{{ url('/api/dashboard/chart/finance-summary') }}',
                    recentSales: '{{ url('/api/sales/recent') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
