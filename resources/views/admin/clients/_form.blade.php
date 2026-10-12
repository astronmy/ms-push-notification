@php
    $editing = isset($client);
@endphp
<div class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    @if (session('api_key'))
        <div class="rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">API key (se muestra una sola vez)</p>
            <code class="mt-2 block break-all">{{ session('api_key') }}</code>
        </div>
    @endif
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $editing ? $client->name : '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $client->is_active : true))>
        Activo
    </label>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="contact_name" value="Contacto" />
            <x-text-input id="contact_name" name="contact_name" class="mt-1 block w-full" :value="old('contact_name', $editing ? $client->contact_name : '')" />
        </div>
        <div>
            <x-input-label for="contact_email" value="Email de contacto" />
            <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" :value="old('contact_email', $editing ? $client->contact_email : '')" />
            <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
        </div>
    </div>
    <div>
        <x-input-label for="notes" value="Observaciones" />
        <textarea id="notes" name="notes" class="mt-1 block w-full rounded-md border border-gray-300">{{ old('notes', $editing ? $client->notes : '') }}</textarea>
    </div>
    <div>
        <x-input-label for="rate_limit_per_minute" value="Rate limit por minuto" />
        <x-text-input id="rate_limit_per_minute" name="rate_limit_per_minute" type="number" min="1" class="mt-1 block w-full" :value="old('rate_limit_per_minute', $editing ? $client->rate_limit_per_minute : '')" />
    </div>

    <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-slate-900">Firebase</h2>
            @if ($editing)
                @if ($client->hasFirebase())
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Service account configurada</span>
                @else
                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">Sin service account</span>
                @endif
            @endif
        </div>

        <div class="mt-4">
            <x-input-label for="firebase_project_id" value="Firebase project_id" />
            <x-text-input id="firebase_project_id" name="firebase_project_id" class="mt-1 block w-full" :value="old('firebase_project_id', $editing ? $client->firebase_project_id : '')" />
            <p class="mt-1 text-xs text-slate-500">Tiene que coincidir con el <code>project_id</code> del JSON. Si lo dejás vacío, se toma del archivo.</p>
            <x-input-error :messages="$errors->get('firebase_project_id')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="android_notification_icon" value="Ícono de Android" />
            <x-text-input id="android_notification_icon" name="android_notification_icon" class="mt-1 block w-full" :value="old('android_notification_icon', $editing ? $client->android_notification_icon : '')" placeholder="ic_notification" />
            <p class="mt-1 text-xs text-slate-500">Nombre del drawable dentro de la app, sin extensión. Si lo dejás vacío, Android usa el ícono de la app.</p>
            <x-input-error :messages="$errors->get('android_notification_icon')" class="mt-2" />
        </div>

        <div class="mt-4" x-data="{ fileName: '' }">
            <x-input-label for="service_account" value="Service account JSON" />
            <label class="file-drop mt-1">
                <input id="service_account" name="service_account" type="file" accept="application/json,.json" @change="fileName = $event.target.files[0]?.name || ''">
                <svg class="h-8 w-8 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a1 1 0 01.7.3l5.4 5.4a1 1 0 01.3.7V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm font-medium text-slate-800" x-text="fileName || 'Elegí el JSON o arrastralo acá'"></span>
                <span class="text-xs text-slate-500">Solo archivos .json</span>
            </label>
            @if ($editing)
                <p class="mt-2 text-sm text-slate-500">
                    {{ $client->hasFirebase() ? 'Subí un archivo solo para reemplazarla.' : 'Sin este archivo no se pueden enviar pushes.' }}
                </p>
            @endif
            <x-input-error :messages="$errors->get('service_account')" class="mt-2" />
        </div>

        @if ($editing)
            <div class="mt-4 rounded-lg border border-slate-200 bg-white px-3 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">API key</p>
                <code class="mt-1 block break-all font-mono text-sm text-slate-900">{{ $client->maskedApiKey() }}</code>
                <p class="mt-1 text-xs text-slate-500">El valor completo solo aparece al crear el cliente o al regenerar la key.</p>
            </div>
        @endif
    </section>

    <div class="flex justify-end border-t border-slate-200 pt-4">
        <x-primary-button>{{ __('admin.actions.save') }}</x-primary-button>
    </div>
</div>
