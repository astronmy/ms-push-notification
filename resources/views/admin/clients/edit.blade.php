<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold">Editar cliente</h1>
            @can('permission', 'clientes.editar')
                <form method="POST" action="{{ route('admin.clients.regenerate-key', $client) }}" onsubmit="return confirm('La API key actual deja de funcionar. ¿Continuar?')">
                    @csrf
                    <button class="rounded-md border border-white/30 px-3 py-2 text-sm text-white">Regenerar API key</button>
                </form>
            @endcan
        </div>
    </x-slot>
    <form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.clients._form', ['client' => $client])
    </form>
</x-admin-layout>
