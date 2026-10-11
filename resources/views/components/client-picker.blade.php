@props([
    'clients',
    'name' => 'client_id',
    'selected' => null,
    'allowEmpty' => false,
    'emptyLabel' => 'Todos los clientes',
])

@php
    $items = collect($clients)->map(fn ($client) => [
        'id' => (int) $client->id,
        'name' => $client->name,
        'contact' => $client->contact_email,
        'project' => $client->firebase_project_id,
        'active' => (bool) ($client->is_active ?? true),
    ])->values();
    $selectedId = ($selected !== null && $selected !== '') ? (int) $selected : null;
@endphp

<div
    x-data="clientPicker({{ \Illuminate\Support\Js::from([
        'clients' => $items,
        'selectedId' => $selectedId,
        'allowEmpty' => (bool) $allowEmpty,
        'emptyLabel' => $emptyLabel,
    ]) }})"
>
    <input type="hidden" name="{{ $name }}" :value="selectedId ?? ''">
    <button type="button" class="client-picker-trigger" @click="openModal()">
        <span class="min-w-0 truncate" x-text="label"></span>
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-4" @keydown.escape.window="open = false">
            <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>
            <div class="relative flex max-h-[min(32rem,85vh)] w-full max-w-lg flex-col overflow-hidden rounded-xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="client-picker-title">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <h2 id="client-picker-title" class="text-base font-semibold text-slate-900">Elegir cliente</h2>
                    <button type="button" class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100" @click="open = false" aria-label="Cerrar">&times;</button>
                </div>
                <div class="border-b border-slate-200 px-4 py-3">
                    <input type="search" x-ref="search" x-model="query" placeholder="Buscar por nombre, email o proyecto" autocomplete="off">
                </div>
                <ul class="min-h-0 flex-1 overflow-y-auto p-2">
                    <li x-show="allowEmpty">
                        <button type="button" class="client-picker-option" :class="selectedId === null ? 'client-picker-option-active' : ''" @click="choose(null)">
                            <span class="font-medium" x-text="emptyLabel"></span>
                        </button>
                    </li>
                    <template x-for="client in filtered" :key="client.id">
                        <li>
                            <button type="button" class="client-picker-option" :class="selectedId === client.id ? 'client-picker-option-active' : ''" @click="choose(client.id)">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="font-medium text-slate-900" x-text="client.name"></span>
                                    <span class="text-xs" :class="client.active ? 'text-emerald-700' : 'text-slate-400'" x-text="client.active ? 'Activo' : 'Inactivo'"></span>
                                </span>
                                <span class="mt-0.5 block text-xs text-slate-500" x-show="client.contact || client.project" x-text="[client.contact, client.project].filter(Boolean).join(' · ')"></span>
                            </button>
                        </li>
                    </template>
                    <li x-show="filtered.length === 0" class="px-3 py-8 text-center text-sm text-slate-500">No hay clientes para esa búsqueda.</li>
                </ul>
            </div>
        </div>
    </template>
</div>

@once
    <script>
        function clientPicker(config) {
            return {
                clients: config.clients || [],
                selectedId: config.selectedId,
                allowEmpty: config.allowEmpty,
                emptyLabel: config.emptyLabel,
                open: false,
                query: '',
                get label() {
                    if (!this.selectedId) {
                        return this.emptyLabel;
                    }
                    const client = this.clients.find((item) => item.id === this.selectedId);
                    return client ? client.name : this.emptyLabel;
                },
                get filtered() {
                    const term = this.query.trim().toLowerCase();
                    if (!term) {
                        return this.clients;
                    }
                    return this.clients.filter((client) => {
                        return [client.name, client.contact, client.project]
                            .filter(Boolean)
                            .some((value) => String(value).toLowerCase().includes(term));
                    });
                },
                openModal() {
                    this.query = '';
                    this.open = true;
                    this.$nextTick(() => this.$refs.search?.focus());
                },
                choose(id) {
                    this.selectedId = id;
                    this.open = false;
                    this.$dispatch('client-selected', { id });
                },
            };
        }
    </script>
@endonce
