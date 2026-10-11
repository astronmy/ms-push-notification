<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Logs</h1></x-slot>
    @include('admin.logs._tabs')
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
            <label class="block text-sm font-medium text-gray-700">Acción</label>
            <input type="text" name="action" value="{{ request('action') }}" class="mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Método</label>
            <input type="text" name="method" value="{{ request('method') }}" class="mt-1 w-28">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <input type="number" name="status" value="{{ request('status') }}" class="mt-1 w-28">
        </div>
        @if ($type !== 'api')
            <div>
                <label class="block text-sm font-medium text-gray-700">Notificación</label>
                <input type="number" name="notification_id" value="{{ request('notification_id') }}" class="mt-1 w-28">
            </div>
        @endif
        <div>
            <label class="block text-sm font-medium text-gray-700">Desde</label>
            <input type="date" name="from" value="{{ request('from') }}" class="mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Hasta</label>
            <input type="date" name="to" value="{{ request('to') }}" class="mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Buscar en payload</label>
            <input type="text" name="q" value="{{ request('q') }}" class="mt-1">
        </div>
        <button class="rounded-md bg-[var(--bo-bg-elevated)] px-3 py-2 text-sm text-white">{{ __('admin.actions.filter') }}</button>
        <a href="{{ route('admin.logs.index', ['type' => $type]) }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm">{{ __('admin.actions.clear') }}</a>
    </form>
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
                            <a href="{{ route('admin.logs.show', ['type' => $type, 'log' => $log->id]) }}" wire:navigate class="rounded-md bg-[var(--bo-bg-elevated)] px-2.5 py-1.5 text-xs text-white">{{ __('admin.actions.view') }}</a>
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
