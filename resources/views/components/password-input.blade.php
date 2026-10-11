@props(['disabled' => false])

<div class="relative mt-1" x-data="{ show: false }">
    <input
        @disabled($disabled)
        type="password"
        x-bind:type="show ? 'text' : 'password'"
        {{ $attributes->merge(['class' => 'block w-full rounded-md border border-gray-300 pe-10 shadow-sm focus:border-[var(--bo-bg-elevated)] focus:ring-[var(--bo-bg-elevated)]']) }}
    >
    <button type="button" class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-500" @click="show = ! show">
        <span class="text-xs" x-text="show ? 'Ocultar' : 'Ver'"></span>
    </button>
</div>
