@php
    $editing = isset($notification);
    $selected = old('external_user_ids', $editing
        ? $notification->recipients->map(fn ($recipient) => $recipient->deviceUser?->external_user_id)->filter()->values()->all()
        : []);
    $clientId = old('client_id', $editing ? $notification->client_id : '');
    $dataJson = old('data_json', $editing && $notification->data ? json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '');
    $scheduled = old('scheduled_at', $editing && $notification->scheduled_at ? $notification->scheduled_at->format('Y-m-d\TH:i') : '');
@endphp
<div class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
     x-data="pushForm({{ \Illuminate\Support\Js::from([
        'clientId' => $clientId ? (int) $clientId : null,
        'selected' => array_values($selected),
        'endpoint' => url('/admin/clients/__ID__/devices'),
     ]) }})"
     @client-selected.window="if (clientId != $event.detail.id) selected = []; clientId = $event.detail.id">
    @unless ($editing)
        <div>
            <x-input-label value="Cliente" />
            <div class="mt-1">
                <x-client-picker :clients="$clients" :selected="$clientId" empty-label="Seleccionar cliente" />
            </div>
            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
        </div>
    @else
        <p class="text-sm text-gray-600">Cliente: <strong>{{ $notification->client?->name }}</strong></p>
    @endunless

    <div>
        <x-input-label value="Destinatarios activos" />
        <p class="mt-1 text-sm text-gray-500" x-show="loading">Cargando usuarios…</p>
        <p class="mt-1 text-sm text-red-600" x-show="loadError" x-text="loadError"></p>
        <div class="mt-2 max-h-64 space-y-2 overflow-y-auto rounded-md border border-gray-200 p-3">
            <template x-for="device in devices" :key="device.external_user_id">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="external_user_ids[]" :value="device.external_user_id" :checked="selected.includes(device.external_user_id)" @change="toggle(device.external_user_id)">
                    <span x-text="device.external_user_id + (device.name ? ' · ' + device.name : '') + ' · ' + device.platform"></span>
                </label>
            </template>
            <p class="text-sm text-gray-500" x-show="!clientId && !loading">Elegí un cliente para ver sus usuarios.</p>
            <p class="text-sm text-gray-500" x-show="clientId && !loading && !loadError && devices.length === 0">No hay usuarios activos para este cliente.</p>
        </div>
        <x-input-error :messages="$errors->get('external_user_ids')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="title" value="Título" />
        <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', $editing ? $notification->title : '')" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="body" value="Mensaje" />
        <textarea id="body" name="body" class="mt-1 block w-full rounded-md border border-gray-300" required>{{ old('body', $editing ? $notification->body : '') }}</textarea>
        <x-input-error :messages="$errors->get('body')" class="mt-2" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="image_url" value="Imagen (URL)" />
            <x-text-input id="image_url" name="image_url" type="url" class="mt-1 block w-full" :value="old('image_url', $editing ? $notification->image_url : '')" />
            <x-input-error :messages="$errors->get('image_url')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="scheduled_at" value="Programar envío" />
            <x-text-input id="scheduled_at" name="scheduled_at" type="datetime-local" class="mt-1 block w-full" :value="$scheduled" />
        </div>
    </div>
    <div>
        <x-input-label for="data_json" value="Data (JSON)" />
        <textarea id="data_json" name="data_json" class="mt-1 block w-full rounded-md border border-gray-300">{{ $dataJson }}</textarea>
        <x-input-error :messages="$errors->get('data_json')" class="mt-2" />
    </div>
    <div class="flex justify-end"><x-primary-button>{{ __('admin.actions.save') }}</x-primary-button></div>
</div>
<script>
    function pushForm(config) {
        return {
            clientId: config.clientId,
            selected: config.selected || [],
            endpoint: config.endpoint,
            devices: [],
            loading: false,
            loadError: '',
            init() {
                this.$watch('clientId', () => this.fetchDevices());
                if (this.clientId) this.fetchDevices();
            },
            toggle(id) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter((item) => item !== id);
                } else {
                    this.selected.push(id);
                }
            },
            async fetchDevices() {
                if (!this.clientId) {
                    this.devices = [];
                    return;
                }
                this.loading = true;
                this.loadError = '';
                try {
                    const response = await fetch(this.endpoint.replace('__ID__', this.clientId), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const json = await response.json();
                    this.devices = json.data || [];
                } catch (error) {
                    this.loadError = 'No se pudieron cargar los usuarios.';
                    this.devices = [];
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
