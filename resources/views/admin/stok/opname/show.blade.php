<x-app-layout :pageTitle="$opname->code" pageSubtitle="Sesi Stock Opname">
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-8">
            <div class="app-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0">Progress Sesi</h6>
                    @if ($opname->status === 'completed')
                        <span class="badge-status badge-success">Selesai</span>
                    @else
                        <span class="badge-status badge-warning">Berlangsung</span>
                    @endif
                </div>
                @php $progress = $opname->progress(); @endphp
                <div class="progress mb-2" style="height:10px;">
                    <div class="progress-bar bg-success" id="opname-progress-bar" style="width: {{ $progress['total'] ? round($progress['checked'] / $progress['total'] * 100) : 0 }}%"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary-app small" id="opname-progress-text">{{ $progress['checked'] }}/{{ $progress['total'] }} produk selesai dihitung</span>
                    @if ($opname->status !== 'completed')
                        <button type="button" class="btn btn-sm btn-success" id="btn-finalisasi">
                            <i class="bi bi-check2-circle me-1"></i>Finalisasi Sesi
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="app-card h-100">
                <h6 class="mb-2"><i class="bi bi-upc-scan me-2"></i>Cari / Scan Produk</h6>
                <input type="text" id="opname-scan-input" class="form-control" placeholder="Scan barcode atau ketik kode produk..." autocomplete="off" {{ $opname->status === 'completed' ? 'disabled' : '' }}>
            </div>
        </div>
    </div>

    <div class="app-card mb-3 d-none" id="opname-item-form-card">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
                <div class="text-secondary-app small">Produk</div>
                <div class="fw-bold" id="opname-item-name">-</div>
                <div class="text-secondary-app small" id="opname-item-code">-</div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Stok Sistem</label>
                <input type="text" class="form-control" id="opname-item-system" disabled>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Stok Fisik</label>
                <input type="number" class="form-control" id="opname-item-physical" min="0">
            </div>
            <div class="col-12">
                <button type="button" class="btn btn-primary rounded-3" id="btn-simpan-item">
                    <i class="bi bi-check-lg me-1"></i>Simpan Hitungan
                </button>
            </div>
        </div>
    </div>

    <div class="app-card">
        <div class="app-card__header">
            <h6 class="mb-0">Daftar Produk</h6>
        </div>
        <div class="table-responsive">
            <table id="table-opname-items" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Stok Sistem</th>
                        <th>Stok Fisik</th>
                        <th>Selisih</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($opname->items as $item)
                        <tr data-item-id="{{ $item->id }}" data-code="{{ $item->product->kode_barang }}">
                            <td class="dt-nowrap">{{ $item->product->kode_barang }}</td>
                            <td><span class="dt-truncate" title="{{ $item->product->name }}">{{ $item->product->name }}</span></td>
                            <td class="text-end">{{ $item->system_qty }}</td>
                            <td class="text-end cell-physical">{{ $item->physical_qty ?? '-' }}</td>
                            <td class="text-end cell-difference">
                                @if (!is_null($item->difference))
                                    <span class="{{ $item->difference == 0 ? 'text-secondary-app' : ($item->difference > 0 ? 'text-success' : 'text-danger') }}">
                                        {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="cell-status">
                                @if ($item->status === 'checked')
                                    <span class="badge-status badge-success">Selesai</span>
                                @else
                                    <span class="badge-status badge-muted">Belum</span>
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
            window.__opnamePage = {
                page: 'show',
                opnameId: {{ $opname->id }},
                status: '{{ $opname->status }}',
                routes: {
                    find: '{{ url("/stok/opname/{$opname->id}/find") }}',
                    updateItem: '{{ url("/stok/opname/{$opname->id}/item") }}',
                    finalize: '{{ route('stok.opname.finalize', $opname) }}',
                    index: '{{ route('stok.opname.index') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
