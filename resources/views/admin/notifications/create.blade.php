<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Nuevo envío</h1></x-slot>
    <form method="POST" action="{{ route('admin.notifications.store') }}">
        @csrf
        @include('admin.notifications._form')
    </form>
</x-admin-layout>
