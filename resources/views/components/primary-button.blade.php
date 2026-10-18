<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary rounded-3 fw-semibold']) }}>
    {{ $slot }}
</button>
