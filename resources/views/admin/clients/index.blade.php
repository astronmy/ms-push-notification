<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">Clientes</h1>
            @can('permission', 'clientes.crear')
                <a href="{{ route('admin.clients.create') }}" wire:navigate class="rounded-md bg-[var(--bo-gold)] px-3 py-2 text-sm font-semibold text-white">Nuevo</a>
            @endcan
        </div>
    </x-slot>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="name" value="{{ request('name') }}" class="mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Estado</label>
            <select name="status" class="mt-1">
                <option value="">Todos</option>
                <option value="active" @selected(request('status') === 'active')>Activos</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactivos</option>
            </select>
        </div>
        <button class="rounded-md bg-[var(--bo-bg-elevated)] px-3 py-2 text-sm text-white">{{ __('admin.actions.filter') }}</button>
        <a href="{{ route('admin.clients.index') }}" wire:navigate class="rounded-md border border-gray-300 px-3 py-2 text-sm">{{ __('admin.actions.clear') }}</a>
    </form>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[var(--bo-bg-elevated)] text-left text-xs uppercase text-[var(--bo-sand)]">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Contacto</th>
                    <th class="px-4 py-3">API key</th>
                    <th class="px-4 py-3">Firebase</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($clients as $client)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $client->name }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$client->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $client->contact_email ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $client->maskedApiKey() }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $client->firebase_project_id ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @can('permission', 'clientes.editar')
                                    <a href="{{ route('admin.clients.edit', $client) }}" wire:navigate class="rounded-md bg-[var(--bo-bg-elevated)] px-2.5 py-1.5 text-xs text-white">{{ __('admin.actions.edit') }}</a>
                                @endcan
                                @can('permission', 'clientes.eliminar')
                                    <form id="form-delete-client-{{ $client->id }}" method="POST" action="{{ route('admin.clients.destroy', $client) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="rounded-md bg-red-600 px-2.5 py-1.5 text-xs text-white" @click="$dispatch('open-delete-modal', { label: @js($client->name), form: 'form-delete-client-{{ $client->id }}' })">{{ __('admin.actions.delete') }}</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('admin.table.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @include('admin.partials.pager', ['paginator' => $clients])
    </div>
</x-admin-layout>
