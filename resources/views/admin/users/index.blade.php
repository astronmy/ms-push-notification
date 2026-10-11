<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">{{ __('admin.menu.users') }}</h1>
            @can('permission', 'usuarios.crear')
                <a href="{{ route('admin.users.create') }}" wire:navigate class="btn btn-primary">Nuevo</a>
            @endcan
        </div>
    </x-slot>
    <x-filter-panel :clear-url="route('admin.users.index')">
        <div class="filter-field">
            <label for="filter_name">Nombre</label>
            <input type="search" id="filter_name" name="name" value="{{ request('name') }}" placeholder="Buscar por nombre">
        </div>
        <div class="filter-field">
            <label for="filter_email">Email</label>
            <input type="search" id="filter_email" name="email" value="{{ request('email') }}" placeholder="Buscar por email">
        </div>
    </x-filter-panel>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-[var(--bo-bg-elevated)] text-left text-xs uppercase text-[var(--bo-sand)]">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Rol</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->role?->name ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @can('permission', 'usuarios.editar')
                                    <a href="{{ route('admin.users.edit', $user) }}" wire:navigate class="btn btn-muted btn-sm">{{ __('admin.actions.edit') }}</a>
                                @endcan
                                @can('permission', 'usuarios.eliminar')
                                    @if ($user->id !== auth()->id())
                                        <form id="form-delete-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" @click="$dispatch('open-delete-modal', { label: @js($user->name), form: 'form-delete-user-{{ $user->id }}' })">{{ __('admin.actions.delete') }}</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ __('admin.table.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @include('admin.partials.pager', ['paginator' => $users])
    </div>
</x-admin-layout>
