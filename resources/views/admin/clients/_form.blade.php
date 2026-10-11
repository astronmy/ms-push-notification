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
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="firebase_project_id" value="Firebase project_id" />
            <x-text-input id="firebase_project_id" name="firebase_project_id" class="mt-1 block w-full" :value="old('firebase_project_id', $editing ? $client->firebase_project_id : '')" />
            <x-input-error :messages="$errors->get('firebase_project_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="rate_limit_per_minute" value="Rate limit por minuto" />
            <x-text-input id="rate_limit_per_minute" name="rate_limit_per_minute" type="number" min="1" class="mt-1 block w-full" :value="old('rate_limit_per_minute', $editing ? $client->rate_limit_per_minute : '')" />
        </div>
    </div>
    <div>
        <x-input-label for="service_account" value="Service account JSON" />
        <input id="service_account" name="service_account" type="file" accept="application/json,.json" class="mt-1 block w-full text-sm">
        @if ($editing)
            <p class="mt-1 text-sm text-gray-500">
                {{ $client->hasFirebase() ? 'Hay una service account configurada. Subí un archivo solo si querés reemplazarla.' : 'Sin service account.' }}
            </p>
        @endif
        <x-input-error :messages="$errors->get('service_account')" class="mt-2" />
    </div>
    @if ($editing)
        <p class="text-sm text-gray-600">API key: <code>{{ $client->maskedApiKey() }}</code></p>
    @endif
    <div class="flex justify-end">
        <x-primary-button>{{ __('admin.actions.save') }}</x-primary-button>
    </div>
</div>
