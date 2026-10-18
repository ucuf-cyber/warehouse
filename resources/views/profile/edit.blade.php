<x-app-layout pageTitle="Profil" pageSubtitle="Kelola informasi akun dan password Anda">
    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="app-card mb-3">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="app-card">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-app-layout>
