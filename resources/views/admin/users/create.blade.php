<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Nuevo usuario de sistema</h1></x-slot>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        @include('admin.users._form')
    </form>
</x-admin-layout>
