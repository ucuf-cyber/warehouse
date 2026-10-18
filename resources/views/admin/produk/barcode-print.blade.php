@php
    use Milon\Barcode\Facades\DNS1DFacade;
    use Milon\Barcode\Facades\DNS2DFacade;

    $widthMm = \App\Models\Setting::get('barcode_width_mm', config('bms.barcode_printer.width_mm', 40));
    $heightMm = \App\Models\Setting::get('barcode_height_mm', config('bms.barcode_printer.height_mm', 25));
@endphp
<!doctype html>
{{-- Labels are always printed black-on-white, regardless of the viewer's dark/light preference. --}}
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <title>Cetak {{ $type === 'qr' ? 'QR Code' : 'Barcode' }} Produk</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    {{-- CSS only — this page needs no JS (Print button is plain window.print()),
         and skipping app.js means theme.js can't override the forced light theme above. --}}
    @vite(['resources/css/app.scss'])
    <style>
        .label-sheet {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 16px;
        }

        .label {
            width: {{ $widthMm }}mm;
            height: {{ $heightMm }}mm;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }

        .label .name {
            font-size: 7px;
            font-weight: 700;
            line-height: 1.15;
            max-height: 16px;
            overflow: hidden;
            width: 100%;
        }

        .label .code {
            font-size: 6px;
            color: #64748b;
            letter-spacing: .02em;
        }

        .label .price {
            font-size: 7.5px;
            font-weight: 600;
        }

        .label svg {
            max-width: 100%;
            max-height: 20mm;
            height: auto;
        }

        @media print {
            .no-print { display: none !important; }
            .label { border: none; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body>
    <div class="no-print d-flex align-items-center justify-content-between p-3 border-bottom">
        <div>
            <h5 class="fw-bold mb-0">Cetak {{ $type === 'qr' ? 'QR Code' : 'Barcode' }} — {{ $products->count() }} produk</h5>
            <small class="text-secondary-app">Ukuran label: {{ $widthMm }}mm × {{ $heightMm }}mm</small>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('produk.barcode.print', ['ids' => $products->pluck('id')->implode(','), 'type' => $type === 'qr' ? 'barcode' : 'qr']) }}">
                Tampilkan {{ $type === 'qr' ? 'Barcode' : 'QR Code' }}
            </a>
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    @if ($products->isEmpty())
        <div class="empty-state">
            <i class="bi bi-upc-scan empty-state__icon"></i>
            <div class="empty-state__title">Tidak ada produk dipilih</div>
        </div>
    @else
        <div class="label-sheet">
            @foreach ($products as $product)
                <div class="label">
                    @if ($type === 'qr')
                        {!! DNS2DFacade::getBarcodeSVG($product->qr_code, 'QRCODE', 3, 3) !!}
                    @else
                        {!! DNS1DFacade::getBarcodeSVG($product->barcode, 'C128', 1.4, 28, 'black', false) !!}
                    @endif
                    <span class="name">{{ $product->name }}</span>
                    <span class="code">{{ $product->kode_barang }}</span>
                    <span class="price">{{ 'Rp '.number_format($product->harga_jual, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
