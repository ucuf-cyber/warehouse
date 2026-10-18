<section>
    <header class="mb-3">
        <h6 class="fw-bold mb-1">Ubah Password</h6>
        <p class="text-secondary-app small mb-0">Gunakan password yang panjang dan acak agar akun tetap aman.</p>
    </header>

    <form id="form-password-update" method="post" action="{{ route('password.update') }}" novalidate>
        @csrf
        @method('put')

        <div class="mb-3">
            <x-input-label for="update_password_current_password" value="Password Saat Ini" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password" value="Password Baru" />
            <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi Password Baru" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <x-primary-button>Simpan</x-primary-button>
    </form>
</section>

@push('scripts')
    <script>
        $(function () {
            $('#form-password-update').on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                }).done(function (res) {
                    window.appToast('success', res.message || 'Password berhasil diperbarui.');
                    $form.trigger('reset');
                }).fail(window.handleAjaxError);
            });
        });
    </script>
@endpush
