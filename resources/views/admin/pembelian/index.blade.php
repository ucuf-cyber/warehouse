<x-app-layout pageTitle="Pembelian" pageSubtitle="Purchase order, pembayaran, dan retur ke supplier">
    <div class="app-card">
        <div class="app-card__header flex-wrap gap-2">
            <h6 class="mb-0">Daftar Purchase Order</h6>
            <select id="filter-status-po" class="form-select form-select-sm" style="width:180px">
                <option value="">Semua Status</option>
                <option value="ordered">Dipesan</option>
                <option value="received">Diterima</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>
        <div class="table-responsive">
            <table id="table-po" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Kode PO</th>
                        <th>Supplier</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Dibayar</th>
                        <th>Sisa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-slot name="fab">
        <button type="button" class="fab-btn" data-bs-toggle="modal" data-bs-target="#modal-po" title="Buat PO Baru">
            <i class="bi bi-plus-lg"></i>
        </button>
    </x-slot>

    @include('admin.pembelian.partials.modal-po')
    @include('admin.pembelian.partials.modal-po-detail')
    @include('admin.pembelian.partials.modal-po-retur')

    @push('scripts')
        <script>
            window.__pembelianPage = {
                suppliers: @json($suppliers),
                products: @json($products),
                routes: {
                    data: '{{ route('pembelian.data') }}',
                    store: '{{ route('pembelian.store') }}',
                    base: '{{ url('/pembelian') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
