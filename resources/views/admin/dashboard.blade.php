<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold">{{ __('admin.menu.dashboard') }}</h1>
    </x-slot>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Clientes', 'value' => $clients],
            ['label' => 'Usuarios', 'value' => $devices],
            ['label' => 'En cola', 'value' => $pending],
            ['label' => 'Fallidos', 'value' => $failed],
        ] as $card)
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>
</x-admin-layout>
