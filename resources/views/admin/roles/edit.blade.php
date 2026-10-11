<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Editar rol</h1></x-slot>
    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')
        @include('admin.roles._form', ['role' => $role, 'selected' => $selected])
    </form>
</x-admin-layout>
