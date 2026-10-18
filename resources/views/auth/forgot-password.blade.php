<x-guest-layout>
    <p class="text-secondary-app small mb-3">
        Lupa password? Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.
    </p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-100 py-2">Kirim Tautan Reset Password</x-primary-button>
    </form>
</x-guest-layout>
