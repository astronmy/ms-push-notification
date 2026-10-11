<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Editar usuario</h1></x-slot>
    <form method="POST" action="{{ route('admin.devices.update', $device) }}">
        @csrf
        @method('PUT')
        @include('admin.devices._form', ['device' => $device])
    </form>
</x-admin-layout>
