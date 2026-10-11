<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Nuevo usuario API</h1></x-slot>
    <form method="POST" action="{{ route('admin.devices.store') }}">
        @csrf
        @include('admin.devices._form')
    </form>
</x-admin-layout>
