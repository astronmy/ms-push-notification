<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">Usuarios</h1>
            @can('permission', 'dispositivos.crear')
                <a href="{{ route('admin.devices.create') }}" wire:navigate class="rounded-md bg-[var(--bo-gold)] px-3 py-2 text-sm font-semibold text-white">Nuevo</a>
            @endcan
        </div>
    </x-slot>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div>
            <label class="block text-sm font-medium text-gray-700">Cliente</label>
            <select name="client_id" class="mt-1">
                <option value="">Todos</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">ID externo</label>
            <input type="text" name="external_user_id" value="{{ request('external_user_id') }}" class="mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Plataforma</label>
            <select name="platform" class="mt-1">
                <option value="">Todas</option>
                <option value="ios" @selected(request('platform') === 'ios')>iOS</option>
                <option value="android" @selected(request('platform') === 'android')>Android</option>
            </select>
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
        <a href="{{ route('admin.devices.index') }}" wire:navigate class="rounded-md border border-gray-300 px-3 py-2 text-sm">{{ __('admin.actions.clear') }}</a>
    </form>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-[var(--bo-bg-elevated)] text-left text-xs uppercase text-[var(--bo-sand)]">
                <tr>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">ID externo</th>
                    <th class="px-4 py-3">Plataforma</th>
                    <th class="px-4 py-3">Token</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($devices as $device)
                    <tr>
                        <td class="px-4 py-3">{{ $device->client?->name }}</td>
                        <td class="px-4 py-3">{{ $device->external_user_id }}</td>
                        <td class="px-4 py-3">{{ $device->platform->label() }}</td>
                        <td class="px-4 py-3">{{ $device->tokenHint() }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$device->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @can('permission', 'dispositivos.editar')
                                    <a href="{{ route('admin.devices.edit', $device) }}" wire:navigate class="rounded-md bg-[var(--bo-bg-elevated)] px-2.5 py-1.5 text-xs text-white">{{ __('admin.actions.edit') }}</a>
                                @endcan
                                @can('permission', 'dispositivos.eliminar')
                                    <form id="form-delete-device-{{ $device->id }}" method="POST" action="{{ route('admin.devices.destroy', $device) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="rounded-md bg-red-600 px-2.5 py-1.5 text-xs text-white" @click="$dispatch('open-delete-modal', { label: @js($device->external_user_id), form: 'form-delete-device-{{ $device->id }}' })">{{ __('admin.actions.delete') }}</button>
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
        @include('admin.partials.pager', ['paginator' => $devices])
    </div>
</x-admin-layout>
