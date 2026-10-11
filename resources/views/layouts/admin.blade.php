<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="admin-force-light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[var(--bo-bg)] font-sans antialiased" x-data="adminLayout()">
    @if (session('status') || session('error'))
        <div class="pointer-events-none fixed left-1/2 top-4 z-[100] w-full max-w-2xl -translate-x-1/2 px-4"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="pointer-events-auto flex items-center gap-3 rounded-lg px-4 py-3 text-sm text-white shadow-lg ring-1 ring-[var(--bo-border)] {{ session('error') ? 'bg-red-700' : 'bg-[var(--bo-bg-elevated)]' }}">
                <p class="min-w-0 flex-1">{{ session('error') ?? session('status') }}</p>
                <button type="button" @click="show = false" class="shrink-0 rounded p-1">&times;</button>
            </div>
        </div>
    @endif

    <div class="flex min-h-screen">
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="closeSidebar()"></div>

        <aside class="admin-sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-[var(--bo-border)] bg-[var(--bo-bg)] transition-all duration-300"
               :class="{
                    'translate-x-0': sidebarOpen,
                    '-translate-x-full': !sidebarOpen,
                    'lg:translate-x-0 lg:w-64': !sidebarCollapsed,
                    'lg:translate-x-0 lg:w-[4.5rem]': sidebarCollapsed,
               }">
            <div class="flex h-16 shrink-0 items-center border-b border-[var(--bo-border)] px-4">
                <a href="{{ route('admin.dashboard') }}" wire:navigate @click="closeSidebar()" class="truncate text-sm font-semibold text-white">
                    <span x-show="showSidebarLabels">{{ config('app.name') }}</span>
                    <span x-show="!showSidebarLabels">PN</span>
                </a>
            </div>
            <nav class="admin-sidebar-nav flex-1 overflow-y-auto px-3 py-4">
                <ul class="space-y-1">
                    @php
                        $user = auth()->user();
                        $menuItems = collect([
                            ['label' => __('admin.menu.dashboard'), 'route' => 'admin.dashboard', 'active' => request()->routeIs('admin.dashboard'), 'permission' => 'dashboard.ver'],
                            ['label' => __('admin.menu.clients'), 'route' => 'admin.clients.index', 'active' => request()->routeIs('admin.clients.*'), 'permission' => 'clientes.ver'],
                            ['label' => __('admin.menu.devices'), 'route' => 'admin.devices.index', 'active' => request()->routeIs('admin.devices.*'), 'permission' => 'dispositivos.ver'],
                            ['label' => __('admin.menu.notifications'), 'route' => 'admin.notifications.index', 'active' => request()->routeIs('admin.notifications.*'), 'permission' => 'notificaciones.ver'],
                            ['label' => __('admin.menu.queue'), 'route' => 'admin.queue.index', 'active' => request()->routeIs('admin.queue.*'), 'permission' => 'cola.ver'],
                            ['label' => __('admin.menu.logs'), 'route' => 'admin.logs.index', 'params' => ['type' => 'api'], 'active' => request()->routeIs('admin.logs.*'), 'permission' => 'logs.ver'],
                            ['label' => __('admin.menu.users'), 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*'), 'permission' => 'usuarios.ver'],
                            ['label' => __('admin.menu.roles'), 'route' => 'admin.roles.index', 'active' => request()->routeIs('admin.roles.*'), 'permission' => 'roles.ver'],
                        ])->filter(fn ($item) => $user && $user->canPermission($item['permission']))->values();
                    @endphp
                    @foreach ($menuItems as $item)
                        <li>
                            <a href="{{ route($item['route'], $item['params'] ?? []) }}" wire:navigate @click="closeSidebar()"
                               class="admin-sidebar-link {{ $item['active'] ? 'admin-sidebar-link-active' : '' }}">
                                <span class="admin-sidebar-icon h-2 w-2 rounded-full bg-current"></span>
                                <span x-show="showSidebarLabels" x-cloak>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>

        <div class="flex flex-1 flex-col" :class="sidebarCollapsed ? 'lg:pl-[4.5rem]' : 'lg:pl-64'">
            <header class="admin-header sticky top-0 z-[60] flex min-h-16 items-center gap-4 border-b border-[var(--bo-border)] bg-[var(--bo-bg)] px-4 sm:px-6 lg:px-8">
                <button type="button" @click="toggleSidebar()" class="rounded-md p-2 text-[var(--bo-accent)] hover:bg-white/10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0 flex-1 text-white">{{ $header ?? '' }}</div>
                <div class="relative" x-data="{ userOpen: false }" @click.outside="userOpen = false">
                    <button type="button" @click="userOpen = !userOpen" class="rounded-md px-3 py-2 text-sm text-white hover:bg-white/10">
                        {{ auth()->user()->name }}
                    </button>
                    <div x-show="userOpen" x-cloak class="absolute right-0 mt-2 w-44 rounded-md bg-[var(--bo-bg-elevated)] py-1 shadow-lg">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-left text-sm text-white hover:bg-white/10">{{ __('admin.user_menu.logout') }}</button>
                        </form>
                    </div>
                </div>
            </header>
            <main class="flex-1 bg-[var(--bo-surface)] p-4 sm:p-6 lg:p-8">
                {{ $slot }}
                <x-delete-confirm-modal />
            </main>
        </div>
    </div>

    <script>
        function adminLayout() {
            return {
                sidebarOpen: false,
                sidebarCollapsed: false,
                isDesktop() { return window.matchMedia('(min-width: 1024px)').matches; },
                get showSidebarLabels() { return !this.isDesktop() || !this.sidebarCollapsed; },
                closeSidebar() { this.sidebarOpen = false; },
                init() {
                    if (this.isDesktop()) {
                        this.sidebarCollapsed = localStorage.getItem('adminSidebarCollapsed') === 'true';
                    }
                    document.addEventListener('livewire:navigated', () => this.closeSidebar());
                },
                toggleSidebar() {
                    if (this.isDesktop()) {
                        this.sidebarCollapsed = !this.sidebarCollapsed;
                        localStorage.setItem('adminSidebarCollapsed', this.sidebarCollapsed);
                        return;
                    }
                    this.sidebarOpen = !this.sidebarOpen;
                },
            };
        }
    </script>
    @livewireScripts
</body>
</html>
