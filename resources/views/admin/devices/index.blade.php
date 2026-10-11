<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">{{ __('admin.menu.devices') }}</h1>
            @can('permission', 'dispositivos.crear')
                <a href="{{ route('admin.devices.create') }}" wire:navigate class="btn btn-primary">Nuevo</a>
            @endcan
        </div>
    </x-slot>
    <x-filter-panel :clear-url="route('admin.devices.index')">
        <div class="filter-field">
            <label for="filter_client">Cliente</label>
            <select id="filter_client" name="client_id">
                <option value="">Todos</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label for="filter_external">ID externo</label>
            <input type="search" id="filter_external" name="external_user_id" value="{{ request('external_user_id') }}" placeholder="ID del sistema cliente">
        </div>
        <div class="filter-field">
            <label for="filter_platform">Plataforma</label>
            <select id="filter_platform" name="platform">
                <option value="">Todas</option>
                <option value="ios" @selected(request('platform') === 'ios')>iOS</option>
                <option value="android" @selected(request('platform') === 'android')>Android</option>
            </select>
        </div>
        <div class="filter-field">
            <label for="filter_status">Estado</label>
            <select id="filter_status" name="status">
                <option value="">Todos</option>
                <option value="active" @selected(request('status') === 'active')>Activos</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactivos</option>
            </select>
        </div>
    </x-filter-panel>
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
                                    <a href="{{ route('admin.devices.edit', $device) }}" wire:navigate class="btn btn-muted btn-sm">{{ __('admin.actions.edit') }}</a>
                                @endcan
                                @can('permission', 'dispositivos.eliminar')
                                    <form id="form-delete-device-{{ $device->id }}" method="POST" action="{{ route('admin.devices.destroy', $device) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm" @click="$dispatch('open-delete-modal', { label: @js($device->external_user_id), form: 'form-delete-device-{{ $device->id }}' })">{{ __('admin.actions.delete') }}</button>
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
