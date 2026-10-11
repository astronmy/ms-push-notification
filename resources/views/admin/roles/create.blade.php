<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Nuevo rol</h1></x-slot>
    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        @include('admin.roles._form')
    </form>
</x-admin-layout>
