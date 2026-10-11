<x-admin-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold">Nuevo cliente</h1></x-slot>
    <form method="POST" action="{{ route('admin.clients.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.clients._form')
    </form>
</x-admin-layout>
