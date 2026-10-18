<x-app-layout pageTitle="Stock Opname" pageSubtitle="Sesi hitung stok fisik">
    <div class="app-card">
        <div class="app-card__header">
            <h6 class="mb-0">Riwayat Sesi Stock Opname</h6>
            <button type="button" class="btn btn-sm btn-primary" id="btn-buat-sesi">
                <i class="bi bi-plus-lg me-1"></i>Buat Sesi Baru
            </button>
        </div>

        @if ($sessions->isEmpty())
            <div class="empty-state">
                <i class="bi bi-clipboard-check empty-state__icon"></i>
                <div class="empty-state__title">Belum ada sesi stock opname</div>
                <p class="mb-3">Mulai sesi baru untuk menghitung stok fisik seluruh produk aktif.</p>
                <button type="button" class="btn btn-primary rounded-3" id="btn-buat-sesi-empty">
                    <i class="bi bi-plus-lg me-1"></i>Buat Sesi Baru
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Kode Sesi</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Dibuat Oleh</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            <tr>
                                <td class="fw-semibold">{{ $session->code }}</td>
                                <td>
                                    @if ($session->status === 'completed')
                                        <span class="badge-status badge-success">Selesai</span>
                                    @else
                                        <span class="badge-status badge-warning">Berlangsung</span>
                                    @endif
                                </td>
                                <td style="width:160px;">
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar" style="width: {{ $session->items_count ? round($session->checked_count / $session->items_count * 100) : 0 }}%"></div>
                                    </div>
                                    <small class="text-secondary-app">{{ $session->checked_count }}/{{ $session->items_count }} produk</small>
                                </td>
                                <td>{{ $session->user->name ?? '-' }}</td>
                                <td>{{ $session->started_at?->translatedFormat('d M Y H:i') }}</td>
                                <td>{{ $session->completed_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('stok.opname.show', $session) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-right"></i> Buka
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            window.__opnamePage = {
                page: 'index',
                routes: {
                    store: '{{ route('stok.opname.store') }}',
                    show: '{{ url('/stok/opname') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
