<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Editar envío</h1></x-slot>
    <form method="POST" action="{{ route('admin.notifications.update', $notification) }}">
        @csrf
        @method('PUT')
        @include('admin.notifications._form', ['notification' => $notification])
    </form>
</x-admin-layout>
