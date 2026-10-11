<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Editar usuario de sistema</h1></x-slot>
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user])
    </form>
</x-admin-layout>
