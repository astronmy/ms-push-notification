<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">Roles</h1>
            @can('permission', 'roles.crear')
                <a href="{{ route('admin.roles.create') }}" wire:navigate class="btn btn-primary">Nuevo</a>
            @endcan
        </div>
    </x-slot>
    <x-filter-panel :clear-url="route('admin.roles.index')">
        <div class="filter-field">
            <label for="filter_name">Nombre</label>
            <input type="search" id="filter_name" name="name" value="{{ request('name') }}" placeholder="Buscar por nombre">
        </div>
    </x-filter-panel>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-[var(--bo-bg-elevated)] text-left text-xs uppercase text-[var(--bo-sand)]">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Usuarios</th>
                    <th class="px-4 py-3">Permisos</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($roles as $role)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $role->name }}</td>
                        <td class="px-4 py-3">{{ $role->slug }}</td>
                        <td class="px-4 py-3">{{ $role->users_count }}</td>
                        <td class="px-4 py-3">{{ $role->permissions_count }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$role->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @can('permission', 'roles.editar')
                                    <a href="{{ route('admin.roles.edit', $role) }}" wire:navigate class="btn btn-muted btn-sm">{{ __('admin.actions.edit') }}</a>
                                @endcan
                                @can('permission', 'roles.eliminar')
                                    @unless ($role->isAdmin())
                                        <form id="form-delete-role-{{ $role->id }}" method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" @click="$dispatch('open-delete-modal', { label: @js($role->name), form: 'form-delete-role-{{ $role->id }}' })">{{ __('admin.actions.delete') }}</button>
                                        </form>
                                    @endunless
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('admin.table.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @include('admin.partials.pager', ['paginator' => $roles])
    </div>
</x-admin-layout>
