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
        </div>
    </x-slot>

    <div class="space-y-4">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $notification->client?->name ?: 'Sin cliente' }}</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ $notification->title }}</h2>
                    </div>
                    <x-status-badge :status="$notification->status" />
                </div>
                <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-700">{{ $notification->body }}</p>
                @if ($notification->image_url)
                    <a class="mt-3 inline-flex text-sm font-medium text-sky-700 underline" href="{{ $notification->image_url }}" target="_blank" rel="noopener">Ver imagen</a>
                @endif
                @if ($notification->last_error)
                    <p class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ $notification->last_error }}</p>
                @endif
                @if ($notification->data)
                    <pre class="mt-4 overflow-auto rounded-lg bg-slate-900 p-4 text-xs text-slate-100">{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif
            </div>

            <dl class="grid grid-cols-2 gap-4 px-5 py-4 sm:grid-cols-4 sm:px-6">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Origen</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $notification->origin->label() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Intentos</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $notification->attempts }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Programado</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $notification->scheduled_at?->format('d/m/Y H:i') ?: 'Inmediato' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Creado</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $notification->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>

            <div class="flex flex-wrap gap-2 border-t border-slate-100 px-5 py-3 sm:px-6">
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.logs.index', ['type' => 'firebase-requests', 'notification_id' => $notification->id]) }}">Firebase requests</a>
                <a class="btn btn-secondary btn-sm" href="{{ route('admin.logs.index', ['type' => 'firebase-logs', 'notification_id' => $notification->id]) }}">Firebase logs</a>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3 sm:px-6">
                <h3 class="text-sm font-semibold text-slate-900">Destinatarios</h3>
                <span class="text-sm text-slate-500">{{ $notification->recipients->count() }}</span>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[var(--bo-bg-elevated)] text-left text-xs uppercase text-[var(--bo-sand)]">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Plataforma</th>
                        <th class="px-4 py-3">Token</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($notification->recipients as $recipient)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $recipient->deviceUser?->external_user_id ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $recipient->deviceUser?->platform?->label() ?: '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $recipient->tokenHint() }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$recipient->status" /></td>
                            <td class="px-4 py-3 text-slate-600">{{ $recipient->error_message ?: $recipient->error_code ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Sin destinatarios</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</x-admin-layout>
