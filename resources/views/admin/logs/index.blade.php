<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Logs</h1></x-slot>
    @include('admin.logs._tabs')
    <x-filter-panel :clear-url="route('admin.logs.index', ['type' => $type])">
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
            <label for="filter_action">Acción</label>
            <input type="search" id="filter_action" name="action" value="{{ request('action') }}" placeholder="registerUser, send…">
        </div>
        <div class="filter-field">
            <label for="filter_method">Método</label>
            <input type="text" id="filter_method" name="method" value="{{ request('method') }}" placeholder="GET, POST">
        </div>
        <div class="filter-field">
            <label for="filter_http_status">Status</label>
            <input type="number" id="filter_http_status" name="status" value="{{ request('status') }}" placeholder="200">
        </div>
        @if ($type !== 'api')
            <div class="filter-field">
                <label for="filter_notification">Notificación</label>
                <input type="number" id="filter_notification" name="notification_id" value="{{ request('notification_id') }}">
            </div>
        @endif
        <div class="filter-field">
            <label for="filter_from">Desde</label>
            <input type="date" id="filter_from" name="from" value="{{ request('from') }}">
        </div>
        <div class="filter-field">
            <label for="filter_to">Hasta</label>
            <input type="date" id="filter_to" name="to" value="{{ request('to') }}">
        </div>
        <div class="filter-field">
            <label for="filter_q">Buscar en payload</label>
            <input type="search" id="filter_q" name="q" value="{{ request('q') }}" placeholder="Texto dentro del JSON">
        </div>
    </x-filter-panel>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-[var(--bo-bg-elevated)] text-left text-xs uppercase text-[var(--bo-sand)]">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Acción</th>
                    <th class="px-4 py-3">Método</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3">{{ $log->client?->name ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $log->action }}</td>
                        <td class="px-4 py-3">{{ $log->method }}</td>
                        <td class="px-4 py-3">{{ $log->status }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.logs.show', ['type' => $type, 'log' => $log->id]) }}" wire:navigate class="btn btn-muted btn-sm">{{ __('admin.actions.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('admin.table.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @include('admin.partials.pager', ['paginator' => $logs])
    </div>
</x-admin-layout>
