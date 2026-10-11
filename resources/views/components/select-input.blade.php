@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-md border border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-[var(--bo-bg-elevated)] focus:ring-[var(--bo-bg-elevated)]']) }}>
    {{ $slot }}
</select>
