<x-app-layout pageTitle="Laporan" pageSubtitle="Export laporan ke PDF dan Excel">
    <div class="row g-3 mb-3">
        @foreach ([
            ['type' => 'laba-rugi', 'title' => 'Laba Rugi', 'icon' => 'bi-graph-up-arrow', 'sync' => true],
            ['type' => 'cashflow', 'title' => 'Cashflow', 'icon' => 'bi-bar-chart-line', 'sync' => true],
            ['type' => 'neraca', 'title' => 'Neraca', 'icon' => 'bi-columns-gap', 'sync' => true],
            ['type' => 'penjualan', 'title' => 'Penjualan', 'icon' => 'bi-receipt', 'sync' => false],
            ['type' => 'pembelian', 'title' => 'Pembelian', 'icon' => 'bi-cart-plus', 'sync' => false],
            ['type' => 'stok', 'title' => 'Pergerakan Stok', 'icon' => 'bi-boxes', 'sync' => false],
        ] as $report)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="app-card h-100 report-card" data-type="{{ $report['type'] }}" data-sync="{{ $report['sync'] ? '1' : '0' }}">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="kpi-icon" style="background: rgba(47,111,237,.1); color: #2F6FED;">
                            <i class="bi {{ $report['icon'] }}"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $report['title'] }}</h6>
                            <small class="text-secondary-app">{{ $report['sync'] ? 'Unduh langsung' : 'Diproses di latar belakang' }}</small>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Dari</label>
                            <input type="date" class="form-control form-control-sm report-from" value="{{ now()->startOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Sampai</label>
                            <input type="date" class="form-control form-control-sm report-to" value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" data-format="pdf">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" data-format="excel">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="app-card">
        <div class="app-card__header">
            <h6 class="mb-0">Riwayat Export</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm" id="table-riwayat-laporan">
                <thead><tr><th>Jenis</th><th>Format</th><th>Status</th><th>Diminta</th><th>Selesai</th><th></th></tr></thead>
                <tbody>
                    @foreach ($history as $log)
                        <tr>
                            <td class="text-capitalize">{{ $log->type }}</td>
                            <td class="text-uppercase">{{ $log->format }}</td>
                            <td>
                                @if ($log->status === 'completed')
                                    <span class="badge-status badge-success">Selesai</span>
                                @elseif ($log->status === 'failed')
                                    <span class="badge-status badge-danger">Gagal</span>
                                @else
                                    <span class="badge-status badge-warning">Diproses</span>
                                @endif
                            </td>
                            <td>{{ $log->requestedBy->name ?? '-' }}</td>
                            <td>{{ $log->completed_at?->diffForHumans() ?? '-' }}</td>
                            <td>
                                @if ($log->status === 'completed')
                                    <a href="{{ route('laporan.download', $log) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            window.__laporanPage = {
                routes: {
                    exportSync: '{{ url('/laporan/export-sync') }}',
                    exportAsync: '{{ route('laporan.export-async') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
