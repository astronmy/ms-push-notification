@php $editing = isset($user); @endphp
<div class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $editing ? $user->name : '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $editing ? $user->email : '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="role_id" value="Rol" />
        <x-select-input id="role_id" name="role_id" class="mt-1" required>
            <option value="">Seleccionar</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role_id', $editing ? $user->role_id : '') == $role->id)>{{ $role->name }}</option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="password" :value="$editing ? 'Contraseña (vacío para no cambiar)' : 'Contraseña'" />
        <x-password-input id="password" name="password" :required="! $editing" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-password-input id="password_confirmation" name="password_confirmation" autocomplete="new-password" />
    </div>
    <div class="flex justify-end"><x-primary-button>{{ __('admin.actions.save') }}</x-primary-button></div>
</div>
