<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">Notificaciones</h1>
            @can('permission', 'notificaciones.crear')
                <a href="{{ route('admin.notifications.create') }}" wire:navigate class="btn btn-primary">Nuevo envío</a>
            @endcan
        </div>
    </x-slot>
    <x-filter-panel :clear-url="route('admin.notifications.index')">
        <div class="filter-field">
            <label>Cliente</label>
            <x-client-picker :clients="$clients" :selected="request('client_id')" allow-empty empty-label="Todos los clientes" />
        </div>
        <div class="filter-field">
            <label for="filter_status">Estado</label>
            <select id="filter_status" name="status">
                <option value="">Todos</option>
                @foreach (['scheduled' => 'Programado', 'pending' => 'Pendiente', 'processing' => 'En proceso', 'sent' => 'Enviado', 'failed' => 'Fallido', 'cancelled' => 'Cancelado'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label for="filter_from">Desde</label>
            <input type="date" id="filter_from" name="from" value="{{ request('from') }}">
        </div>
        <div class="filter-field">
            <label for="filter_to">Hasta</label>
            <input type="date" id="filter_to" name="to" value="{{ request('to') }}">
        </div>
    </x-filter-panel>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-[var(--bo-bg-elevated)] text-left text-xs uppercase text-[var(--bo-sand)]">
                <tr>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Destinatarios</th>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($notifications as $notification)
                    <tr>
                        <td class="px-4 py-3">{{ $notification->client?->name }}</td>
                        <td class="px-4 py-3">{{ $notification->title }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$notification->status" /></td>
                        <td class="px-4 py-3">{{ $notification->recipients_count }}</td>
                        <td class="px-4 py-3">{{ $notification->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.notifications.show', $notification) }}" wire:navigate class="btn btn-muted btn-sm">{{ __('admin.actions.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('admin.table.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @include('admin.partials.pager', ['paginator' => $notifications])
    </div>
</x-admin-layout>
