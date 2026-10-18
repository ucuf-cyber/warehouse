<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger rounded-3 fw-semibold']) }}>
    {{ $slot }}
</button>
