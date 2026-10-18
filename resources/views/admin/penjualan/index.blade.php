<x-app-layout pageTitle="Penjualan" pageSubtitle="Semua transaksi penjualan — manual, Shopee, TikTok">
    <div class="app-card">
        <div class="app-card__header flex-wrap gap-2">
            <h6 class="mb-0">Daftar Penjualan</h6>
            <div class="d-flex gap-2 flex-wrap">
                <select id="filter-marketplace" class="form-select form-select-sm" style="width:150px">
                    <option value="">Semua Channel</option>
                    <option value="manual">Manual</option>
                    <option value="shopee">Shopee</option>
                    <option value="tiktok">TikTok</option>
                </select>
                <select id="filter-status-so" class="form-select form-select-sm" style="width:150px">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Diproses</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Batal</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table id="table-penjualan" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Channel</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-slot name="fab">
        <button type="button" class="fab-btn" data-bs-toggle="modal" data-bs-target="#modal-penjualan" title="Catat Penjualan Manual">
            <i class="bi bi-plus-lg"></i>
        </button>
    </x-slot>

    @include('admin.penjualan.partials.modal-penjualan')
    @include('admin.penjualan.partials.modal-penjualan-detail')

    @push('scripts')
        <script>
            window.__penjualanPage = {
                products: @json($products),
                routes: {
                    data: '{{ route('penjualan.data') }}',
                    store: '{{ route('penjualan.store') }}',
                    base: '{{ url('/penjualan') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
