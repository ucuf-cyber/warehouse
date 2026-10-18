@php
    $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
@endphp

<header class="app-topbar">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <button type="button" class="btn btn-icon border-0 fs-5 p-1 lh-1" data-sidebar-toggle aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>
        <div class="min-w-0">
            <h5 class="mb-0 fw-bold text-truncate">{{ $pageTitle ?? 'Dashboard' }}</h5>
            @isset($pageSubtitle)
                <small class="text-secondary-app d-none d-sm-block text-truncate">{{ $pageSubtitle }}</small>
            @endisset
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-shrink-0">
        {{-- Notifications --}}
        <div class="dropdown">
            <button class="btn btn-icon border-0 fs-5 p-1 lh-1 notif-bell" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                @if ($unreadCount > 0)
                    <span class="notif-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end glass-panel p-2 shadow-none" style="width: 320px;">
                <div class="d-flex align-items-center justify-content-between px-2 py-1">
                    <span class="fw-bold small">Notifikasi</span>
                    @if ($unreadCount > 0)
                        <span class="badge-status badge-info">{{ $unreadCount }} baru</span>
                    @endif
                </div>
                <hr class="my-1">
                @forelse (auth()->user()?->notifications()->latest()->take(5)->get() ?? [] as $notification)
                    <div class="dropdown-item small text-wrap rounded-3 py-2">
                        {{ $notification->data['message'] ?? 'Notifikasi baru' }}
                        <div class="text-secondary-app" style="font-size: .7rem;">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="empty-state py-3">
                        <i class="bi bi-bell-slash empty-state__icon" style="font-size: 1.75rem;"></i>
                        <div class="small text-secondary-app">Belum ada notifikasi</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Theme toggle --}}
        <button type="button" class="btn btn-icon border-0 fs-5 p-1 lh-1" data-theme-toggle aria-label="Toggle tema">
            <i class="bi bi-moon-stars" data-theme-icon></i>
        </button>

        {{-- Avatar dropdown --}}
        <div class="dropdown">
            <button class="btn border-0 p-0 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar-circle">{{ auth()->user()?->initials() }}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end glass-panel p-2 shadow-none">
                <div class="px-2 py-1">
                    <div class="fw-bold small">{{ auth()->user()?->name }}</div>
                    <div class="text-secondary-app" style="font-size: .75rem;">{{ auth()->user()?->email }}</div>
                </div>
                <hr class="my-1">
                <a class="dropdown-item rounded-3" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person me-2"></i>Profil
                </a>
                <a class="dropdown-item rounded-3" href="{{ Route::has('pengaturan.index') ? route('pengaturan.index') : '#' }}">
                    <i class="bi bi-gear me-2"></i>Pengaturan
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item rounded-3 text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
