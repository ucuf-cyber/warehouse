<x-guest-layout>
    <p class="text-secondary-app small mb-3">
        Ini adalah area aman. Silakan konfirmasi password Anda sebelum melanjutkan.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" novalidate>
        @csrf

        <div class="mb-3">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-100 py-2">Konfirmasi</x-primary-button>
    </form>
</x-guest-layout>
