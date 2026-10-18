@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success rounded-3 py-2 small']) }}>
        {{ $status }}
    </div>
@endif
