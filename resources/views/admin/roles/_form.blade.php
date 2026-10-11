@php
    $editing = isset($role);
    $selected = $selected ?? [];
@endphp
<div class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $editing ? $role->name : '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $role->is_active : true)) @disabled($editing && $role->isAdmin())>
        Activo
    </label>
    <div>
        <p class="text-sm font-medium text-gray-700">Permisos</p>
        <div class="mt-3 space-y-4">
            @foreach ($permissions as $module => $items)
                <fieldset class="rounded-md border border-gray-200 p-3">
                    <legend class="px-1 text-sm font-semibold text-gray-800">{{ $module }}</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach ($items as $permission)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, old('permissions', $selected)))>
                                {{ $permission->label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>
    </div>
    <div class="flex justify-end"><x-primary-button>{{ __('admin.actions.save') }}</x-primary-button></div>
</div>
