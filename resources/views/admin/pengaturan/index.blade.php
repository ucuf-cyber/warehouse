<x-app-layout pageTitle="Pengaturan" pageSubtitle="Profil toko, printer, backup, dan log aktivitas">
    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-profil"><i class="bi bi-shop me-1"></i>Profil Toko</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-printer"><i class="bi bi-printer me-1"></i>Printer &amp; Barcode</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-backup"><i class="bi bi-cloud-arrow-down me-1"></i>Backup</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-log"><i class="bi bi-journal-text me-1"></i>Log Aktivitas</button></li>
    </ul>

    <div class="tab-content">
        {{-- ================= PROFIL TOKO ================= --}}
        <div class="tab-pane fade show active" id="tab-profil">
            <div class="app-card" style="max-width:640px;">
                <h6 class="fw-bold mb-3">Profil Toko</h6>
                <form id="form-profil-toko" novalidate>
                    <div class="mb-3 d-flex align-items-center gap-3">
                        <div class="avatar-circle" style="width:64px;height:64px;font-size:1.4rem;" id="logo-preview">
                            @if ($storeLogo)
                                <img src="{{ asset('storage/'.$storeLogo) }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            @else
                                <i class="bi bi-shop"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label fw-semibold small">Logo Toko</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Toko</label>
                        <input type="text" name="store_name" class="form-control" value="{{ $storeName }}" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">No. Telepon</label>
                        <input type="text" name="store_phone" class="form-control" value="{{ $storePhone }}" maxlength="30">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alamat</label>
                        <textarea name="store_address" class="form-control" rows="2" maxlength="500">{{ $storeAddress }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-3">Simpan</button>
                </form>
            </div>
        </div>

        {{-- ================= PRINTER ================= --}}
        <div class="tab-pane fade" id="tab-printer">
            <div class="app-card" style="max-width:480px;">
                <h6 class="fw-bold mb-3">Ukuran Label Barcode</h6>
                <form id="form-printer" novalidate>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Lebar (mm)</label>
                            <input type="number" name="barcode_width_mm" class="form-control" value="{{ $barcodeWidth }}" min="10" max="200" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Tinggi (mm)</label>
                            <input type="number" name="barcode_height_mm" class="form-control" value="{{ $barcodeHeight }}" min="10" max="200" required>
                        </div>
                    </div>
                    <p class="text-secondary-app small">Ukuran ini dipakai saat mencetak label barcode/QR dari halaman Produk.</p>
                    <button type="submit" class="btn btn-primary rounded-3">Simpan</button>
                </form>
            </div>
        </div>

        {{-- ================= BACKUP ================= --}}
        <div class="tab-pane fade" id="tab-backup">
            <div class="app-card">
                <div class="app-card__header">
                    <h6 class="mb-0">Backup Database</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-run-backup">
                        <i class="bi bi-cloud-arrow-up me-1"></i>Buat Backup Sekarang
                    </button>
                </div>
                <p class="text-secondary-app small">Backup otomatis berjalan setiap hari pukul 01:30. Anda juga bisa membuat backup manual kapan saja.</p>
                <div class="table-responsive">
                    <table class="table table-sm" id="table-backup">
                        <thead><tr><th>Nama File</th><th>Ukuran</th><th>Tanggal</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($backups as $backup)
                                <tr>
                                    <td>{{ $backup['name'] }}</td>
                                    <td>{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                                    <td>{{ \Illuminate\Support\Carbon::createFromTimestamp($backup['date'])->translatedFormat('d M Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('pengaturan.backup.download', ['path' => $backup['path']]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-backup" data-path="{{ $backup['path'] }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary-app py-3">Belum ada backup</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= LOG AKTIVITAS ================= --}}
        <div class="tab-pane fade" id="tab-log">
            <div class="app-card">
                <div class="app-card__header flex-wrap gap-2">
                    <h6 class="mb-0">Log Aktivitas</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <select id="filter-log-name" class="form-select form-select-sm" style="width:160px">
                            <option value="">Semua Modul</option>
                            <option value="produk">Produk</option>
                            <option value="pembelian">Pembelian</option>
                            <option value="penjualan">Penjualan</option>
                            <option value="keuangan">Keuangan</option>
                        </select>
                        <input type="date" id="filter-log-from" class="form-control form-control-sm" style="width:150px">
                        <input type="date" id="filter-log-to" class="form-control form-control-sm" style="width:150px">
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table-activity-log" class="table table-hover align-middle w-100">
                        <thead><tr><th>Waktu</th><th>Modul</th><th>Deskripsi</th><th>Oleh</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.__pengaturanPage = {
                routes: {
                    updateProfile: '{{ route('pengaturan.profil') }}',
                    updatePrinter: '{{ route('pengaturan.printer') }}',
                    backupRun: '{{ route('pengaturan.backup.run') }}',
                    backupDelete: '{{ route('pengaturan.backup.delete') }}',
                    activityLogData: '{{ route('pengaturan.log.data') }}',
                },
            };
        </script>
    @endpush
</x-app-layout>
