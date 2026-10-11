<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Queue</h1></x-slot>
    @php
        $tabs = [
            'pending' => 'Pendientes / programados',
            'processing' => 'En proceso',
            'sent' => 'Enviados',
            'failed' => 'Fallidos',
            'cancelled' => 'Cancelados',
        ];
    @endphp
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.queue.index', array_merge(request()->except('page'), ['tab' => $key])) }}"
               class="rounded-md px-3 py-2 text-sm {{ $tab === $key ? 'bg-[var(--bo-bg)] text-white' : 'bg-white text-gray-700 border border-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
    <x-filter-panel :clear-url="route('admin.queue.index', ['tab' => $tab])">
        <input type="hidden" name="tab" value="{{ $tab }}">
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
                    <th class="px-4 py-3">Job</th>
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
                        <td class="px-4 py-3">{{ $notification->job_id ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $notification->scheduled_at?->format('d/m/Y H:i') ?: $notification->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.queue.show', $notification) }}" wire:navigate class="btn btn-muted btn-sm">{{ __('admin.actions.view') }}</a>
                                @if ($notification->status->isCancellable())
                                    @can('permission', 'cola.cancelar')
                                        <form method="POST" action="{{ route('admin.queue.cancel', $notification) }}" onsubmit="return confirm('¿Cancelar este envío?')">
                                            @csrf
                                            <button class="btn btn-danger btn-sm">Cancelar</button>
                                        </form>
                                    @endcan
                                @endif
                                @if ($notification->status->value === 'failed')
                                    @can('permission', 'cola.reintentar')
                                        <form method="POST" action="{{ route('admin.queue.retry', $notification) }}">
                                            @csrf
                                            <button class="btn btn-primary btn-sm">Reintentar</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
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
