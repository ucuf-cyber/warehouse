<section>
    <header class="mb-3">
        <h6 class="fw-bold mb-1">Informasi Profil</h6>
        <p class="text-secondary-app small mb-0">Perbarui nama, email, dan nomor telepon akun Anda.</p>
    </header>

    <form id="form-profile-update" method="post" action="{{ route('profile.update') }}" novalidate>
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="phone" value="No. Telepon" />
            <x-text-input id="phone" name="phone" type="text" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <x-primary-button>Simpan</x-primary-button>
    </form>
</section>

@push('scripts')
    <script>
        $(function () {
            $('#form-profile-update').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                }).done(function (res) {
                    window.appToast('success', res.message || 'Profil berhasil diperbarui.');
                }).fail(window.handleAjaxError);
            });
        });
    </script>
@endpush
