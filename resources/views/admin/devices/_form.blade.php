@php $editing = isset($device); @endphp
<div class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    @unless ($editing)
        <div>
            <x-input-label for="client_id" value="Cliente" />
            <x-select-input id="client_id" name="client_id" class="mt-1" required>
                <option value="">Seleccionar</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="external_user_id" value="ID externo" />
            <x-text-input id="external_user_id" name="external_user_id" class="mt-1 block w-full" :value="old('external_user_id')" required />
            <x-input-error :messages="$errors->get('external_user_id')" class="mt-2" />
        </div>
    @else
        <p class="text-sm text-gray-600">Cliente: <strong>{{ $device->client?->name }}</strong> · ID externo: <strong>{{ $device->external_user_id }}</strong></p>
    @endunless
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $editing ? $device->name : '')" />
    </div>
    <div>
        <x-input-label for="firebase_token" value="Firebase token" />
        <textarea id="firebase_token" name="firebase_token" class="mt-1 block w-full rounded-md border border-gray-300" required>{{ old('firebase_token', $editing ? $device->firebase_token : '') }}</textarea>
        <x-input-error :messages="$errors->get('firebase_token')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="platform" value="Plataforma" />
        <x-select-input id="platform" name="platform" class="mt-1">
            <option value="ios" @selected(old('platform', $editing ? $device->platform->value : '') === 'ios')>iOS</option>
            <option value="android" @selected(old('platform', $editing ? $device->platform->value : 'android') === 'android')>Android</option>
        </x-select-input>
    </div>
    <div>
        <x-input-label for="meta_json" value="Datos extra (JSON)" />
        <textarea id="meta_json" name="meta_json" class="mt-1 block w-full rounded-md border border-gray-300">{{ old('meta_json', $editing && $device->meta ? json_encode($device->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        <x-input-error :messages="$errors->get('meta_json')" class="mt-2" />
    </div>
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $device->is_active : true))>
        Activo
    </label>
    <div class="flex justify-end"><x-primary-button>{{ __('admin.actions.save') }}</x-primary-button></div>
</div>
