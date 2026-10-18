@php
    $menu = [
        ['route' => 'dashboard', 'icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
        ['route' => 'produk.index', 'icon' => 'bi-box-seam', 'label' => 'Produk'],
        ['route' => 'stok.index', 'active' => 'stok.*', 'icon' => 'bi-boxes', 'label' => 'Stok'],
        ['route' => 'scan.index', 'icon' => 'bi-upc-scan', 'label' => 'Scan'],
        ['route' => 'pembelian.index', 'icon' => 'bi-cart-plus', 'label' => 'Pembelian'],
        ['route' => 'penjualan.index', 'icon' => 'bi-receipt', 'label' => 'Penjualan'],
        ['route' => 'marketplace.index', 'icon' => 'bi-shop', 'label' => 'Marketplace'],
        ['route' => 'keuangan.index', 'icon' => 'bi-wallet2', 'label' => 'Keuangan'],
        ['route' => 'laporan.index', 'icon' => 'bi-bar-chart-line', 'label' => 'Laporan'],
        ['route' => 'pengaturan.index', 'icon' => 'bi-gear', 'label' => 'Pengaturan'],
    ];
@endphp

<aside class="app-sidebar" data-sidebar>
    <div class="sidebar-brand">
        <span class="brand-mark">B</span>
        <span class="brand-name">Business<br>Monitoring</span>
    </div>

    <nav class="sidebar-nav">
        @foreach ($menu as $item)
            @php $routeExists = \Illuminate\Support\Facades\Route::has($item['route']); @endphp
            <a href="{{ $routeExists ? route($item['route']) : '#' }}"
               class="sidebar-link {{ $routeExists && request()->routeIs($item['active'] ?? $item['route'].'*') ? 'active' : '' }}">
                <i class="bi {{ $item['icon'] }}"></i>
                <span class="link-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link w-100 border-0 bg-transparent text-start">
                <i class="bi bi-box-arrow-right"></i>
                <span class="link-label">Logout</span>
            </button>
        </form>
    </div>
</aside>

<div class="sidebar-backdrop" data-sidebar-backdrop></div>

<nav class="app-bottom-nav">
    @foreach (array_slice($menu, 0, 5) as $item)
        @php $routeExists = \Illuminate\Support\Facades\Route::has($item['route']); @endphp
        <a href="{{ $routeExists ? route($item['route']) : '#' }}"
           class="{{ $routeExists && request()->routeIs($item['active'] ?? $item['route'].'*') ? 'active' : '' }}">
            <i class="bi {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
