<div class="d-flex gap-1">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="detail" data-id="{{ $sale->id }}" title="Detail">
        <i class="bi bi-eye"></i>
    </button>
    @if ($sale->status !== 'cancelled')
        <button type="button" class="btn btn-sm btn-outline-danger" data-action="cancel" data-id="{{ $sale->id }}" title="Batalkan">
            <i class="bi bi-x-lg"></i>
        </button>
    @endif
</div>
