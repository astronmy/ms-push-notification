<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">Envío #{{ $notification->id }}</h1>
            <div class="flex gap-2">
                @if ($notification->status->isEditable())
                    @can('permission', 'notificaciones.editar')
                        <a href="{{ route('admin.notifications.edit', $notification) }}" wire:navigate class="rounded-md bg-white/10 px-3 py-2 text-sm">Editar</a>
                    @endcan
                @endif
                @if ($notification->status->isCancellable())
                    @can('permission', 'cola.cancelar')
                        <form method="POST" action="{{ route('admin.queue.cancel', $notification) }}" onsubmit="return confirm('¿Cancelar este envío?')">
                            @csrf
                            <button class="rounded-md bg-red-600 px-3 py-2 text-sm text-white">Cancelar</button>
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
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm text-sm text-gray-700">
            <div class="flex flex-wrap gap-4">
                <div>Cliente: <strong>{{ $notification->client?->name }}</strong></div>
                <div>Estado: <x-status-badge :status="$notification->status" /></div>
                <div>Origen: {{ $notification->origin->label() }}</div>
                <div>Intentos: {{ $notification->attempts }}</div>
                @if ($notification->scheduled_at)
                    <div>Programado: {{ $notification->scheduled_at->format('d/m/Y H:i') }}</div>
                @endif
            </div>
            <h2 class="mt-4 text-lg font-semibold text-gray-900">{{ $notification->title }}</h2>
            <p class="mt-2 whitespace-pre-wrap">{{ $notification->body }}</p>
            @if ($notification->image_url)
                <p class="mt-2">Imagen: <a class="text-sky-700 underline" href="{{ $notification->image_url }}" target="_blank" rel="noopener">{{ $notification->image_url }}</a></p>
            @endif
            @if ($notification->last_error)
                <p class="mt-3 rounded-md bg-red-50 p-3 text-red-800">{{ $notification->last_error }}</p>
            @endif
            @if ($notification->data)
                <pre class="mt-4 overflow-auto rounded-md bg-gray-900 p-4 text-xs text-gray-100">{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @endif
            <div class="mt-4 flex flex-wrap gap-3">
                <a class="text-sky-700 underline" href="{{ route('admin.logs.index', ['type' => 'firebase-requests', 'notification_id' => $notification->id]) }}">Firebase requests</a>
                <a class="text-sky-700 underline" href="{{ route('admin.logs.index', ['type' => 'firebase-logs', 'notification_id' => $notification->id]) }}">Firebase logs</a>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-[var(--bo-bg-elevated)] text-left text-xs uppercase text-[var(--bo-sand)]">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Plataforma</th>
                        <th class="px-4 py-3">Token</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($notification->recipients as $recipient)
                        <tr>
                            <td class="px-4 py-3">{{ $recipient->deviceUser?->external_user_id ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $recipient->deviceUser?->platform?->label() ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $recipient->tokenHint() }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$recipient->status" /></td>
                            <td class="px-4 py-3">{{ $recipient->error_message ?: $recipient->error_code ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
