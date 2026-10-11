<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Log #{{ $entry->id }}</h1></x-slot>
    @include('admin.logs._tabs')
    <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-700 shadow-sm">
        <div class="flex flex-wrap gap-4">
            <div>Acción: <strong>{{ $entry->action }}</strong></div>
            <div>Método: <strong>{{ $entry->method }}</strong></div>
            <div>Status: <strong>{{ $entry->status }}</strong></div>
            <div>Cliente: <strong>{{ $entry->client?->name ?: '—' }}</strong></div>
            <div>Fecha: {{ $entry->created_at?->format('d/m/Y H:i:s') }}</div>
        </div>
        <div>
            <h2 class="font-semibold text-gray-900">Payload</h2>
            <pre class="mt-2 overflow-auto rounded-md bg-gray-900 p-4 text-xs text-gray-100">{{ json_encode($entry->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
        <div>
            <h2 class="font-semibold text-gray-900">Response</h2>
            <pre class="mt-2 overflow-auto rounded-md bg-gray-900 p-4 text-xs text-gray-100">{{ json_encode($entry->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>
</x-admin-layout>
