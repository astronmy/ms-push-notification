<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">Usuarios del backoffice</h1>
            @can('permission', 'usuarios.crear')
                <a href="{{ route('admin.users.create') }}" wire:navigate class="rounded-md bg-[var(--bo-gold)] px-3 py-2 text-sm font-semibold text-white">Nuevo</a>
            @endcan
        </div>
    </x-slot>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="name" value="{{ request('name') }}" class="mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="text" name="email" value="{{ request('email') }}" class="mt-1">
        </div>
        <button class="rounded-md bg-[var(--bo-bg-elevated)] px-3 py-2 text-sm text-white">{{ __('admin.actions.filter') }}</button>
        <a href="{{ route('admin.users.index') }}" wire:navigate class="rounded-md border border-gray-300 px-3 py-2 text-sm">{{ __('admin.actions.clear') }}</a>
    </form>
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
                                    <a href="{{ route('admin.users.edit', $user) }}" wire:navigate class="rounded-md bg-[var(--bo-bg-elevated)] px-2.5 py-1.5 text-xs text-white">{{ __('admin.actions.edit') }}</a>
                                @endcan
                                @can('permission', 'usuarios.eliminar')
                                    @if ($user->id !== auth()->id())
                                        <form id="form-delete-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="rounded-md bg-red-600 px-2.5 py-1.5 text-xs text-white" @click="$dispatch('open-delete-modal', { label: @js($user->name), form: 'form-delete-user-{{ $user->id }}' })">{{ __('admin.actions.delete') }}</button>
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
