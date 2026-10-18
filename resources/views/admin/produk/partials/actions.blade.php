@php
    $product ??= null;
@endphp
<div class="d-flex gap-1">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="detail" data-id="{{ $product->id }}" title="Detail">
        <i class="bi bi-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-id="{{ $product->id }}" title="Edit">
        <i class="bi bi-pencil"></i>
    </button>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('produk.barcode.print', ['ids' => $product->id]) }}" target="_blank" title="Cetak Barcode">
        <i class="bi bi-upc"></i>
    </a>
    <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-id="{{ $product->id }}" title="Hapus">
        <i class="bi bi-trash"></i>
    </button>
</div>
