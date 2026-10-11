<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <x-input-label for="email" :value="__('admin.login.email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="password" :value="__('admin.login.password')" />
            <x-password-input id="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="mt-4">
            <label for="remember" class="inline-flex items-center">
                <input id="remember" type="checkbox" class="rounded border-gray-300" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('admin.login.remember') }}</span>
            </label>
        </div>
        <div class="mt-6 flex justify-end">
            <x-primary-button>{{ __('admin.login.submit') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
