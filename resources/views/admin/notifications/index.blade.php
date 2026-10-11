<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">Notificaciones</h1>
            @can('permission', 'notificaciones.crear')
                <a href="{{ route('admin.notifications.create') }}" wire:navigate class="rounded-md bg-[var(--bo-gold)] px-3 py-2 text-sm font-semibold text-white">Nuevo envío</a>
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
            <label class="block text-sm font-medium text-gray-700">Estado</label>
            <select name="status" class="mt-1">
                <option value="">Todos</option>
                @foreach (['scheduled' => 'Programado', 'pending' => 'Pendiente', 'processing' => 'En proceso', 'sent' => 'Enviado', 'failed' => 'Fallido', 'cancelled' => 'Cancelado'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Desde</label>
            <input type="date" name="from" value="{{ request('from') }}" class="mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Hasta</label>
            <input type="date" name="to" value="{{ request('to') }}" class="mt-1">
        </div>
        <button class="rounded-md bg-[var(--bo-bg-elevated)] px-3 py-2 text-sm text-white">{{ __('admin.actions.filter') }}</button>
        <a href="{{ route('admin.notifications.index') }}" wire:navigate class="rounded-md border border-gray-300 px-3 py-2 text-sm">{{ __('admin.actions.clear') }}</a>
    </form>
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
                            <a href="{{ route('admin.notifications.show', $notification) }}" wire:navigate class="rounded-md bg-[var(--bo-bg-elevated)] px-2.5 py-1.5 text-xs text-white">{{ __('admin.actions.view') }}</a>
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
