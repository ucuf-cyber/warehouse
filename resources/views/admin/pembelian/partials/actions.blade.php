<div class="d-flex gap-1">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="detail" data-id="{{ $purchase->id }}" title="Detail">
        <i class="bi bi-eye"></i>
    </button>
    @if ($purchase->status === 'ordered')
        <button type="button" class="btn btn-sm btn-outline-success" data-action="terima" data-id="{{ $purchase->id }}" title="Terima Barang">
            <i class="bi bi-box-seam"></i>
        </button>
    @endif
</div>
